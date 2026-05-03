<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ClassSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Booking::query()->with([
            'user:id,name,email',
            'session.gymClass:id,name,category',
            'session.trainer:id,name',
        ]);

        if ($user->isMember()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isTrainer()) {
            $tid = $user->trainer_id;
            $query->whereHas('session', fn ($q) => $q->where('trainer_id', $tid));
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($pay = $request->query('payment_status')) {
            $query->where('payment_status', $pay);
        }
        if ($from = $request->query('date_from')) {
            $query->whereDate('booking_date', '>=', $from);
        }
        if ($to = $request->query('date_to')) {
            $query->whereDate('booking_date', '<=', $to);
        }
        if ($sid = $request->query('session_id')) {
            $query->where('session_id', $sid);
        }

        $perPage = (int) $request->query('per_page', 10);
        $items = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Bookings retrieved.',
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $booking = Booking::with([
            'user:id,name,email,phone',
            'session.gymClass:id,name,category,description',
            'session.trainer:id,name,specialization',
        ])->findOrFail($id);

        $this->authorizeAccess($booking);

        return response()->json([
            'success' => true,
            'message' => 'Booking detail retrieved.',
            'data' => $booking,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user->isMember()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya member yang dapat melakukan booking.',
            ], 403);
        }

        $data = $request->validate([
            'session_id' => ['required', 'integer', 'exists:class_sessions,id'],
            'payment_method' => ['required', 'in:cash,transfer,ewallet'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $booking = DB::transaction(function () use ($data, $user) {
                $session = ClassSession::lockForUpdate()->findOrFail($data['session_id']);

                if ($session->status !== 'scheduled') {
                    abort(422, 'Sesi tidak tersedia untuk booking.');
                }
                if ($session->session_date->lt(now()->startOfDay())) {
                    abort(422, 'Sesi sudah lewat.');
                }
                if ($session->booked_count >= $session->capacity) {
                    abort(409, 'Sesi sudah penuh.');
                }

                $existing = Booking::where('user_id', $user->id)
                    ->where('session_id', $session->id)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->exists();
                if ($existing) {
                    abort(409, 'Anda sudah memiliki booking aktif untuk sesi ini.');
                }

                $code = 'BK-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));

                $booking = Booking::create([
                    'user_id' => $user->id,
                    'session_id' => $session->id,
                    'booking_code' => $code,
                    'booking_date' => now(),
                    'status' => 'pending',
                    'payment_method' => $data['payment_method'],
                    'payment_status' => 'unpaid',
                    'total_price' => $session->price,
                    'notes' => $data['notes'] ?? null,
                ]);

                $session->increment('booked_count');

                return $booking;
            });
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dibuat.',
            'data' => $booking->load('session.gymClass:id,name', 'session.trainer:id,name'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $booking = Booking::findOrFail($id);
        $user = Auth::user();
        $this->authorizeAccess($booking);

        if ($user->isMember()) {
            if ($booking->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya booking pending yang bisa diubah.',
                ], 403);
            }
            $data = $request->validate([
                'notes' => ['nullable', 'string'],
            ]);
            $booking->update($data);
        } elseif ($user->isAdmin()) {
            $data = $request->validate([
                'status' => ['sometimes', 'in:pending,confirmed,cancelled,completed'],
                'payment_method' => ['sometimes', 'in:cash,transfer,ewallet'],
                'payment_status' => ['sometimes', 'in:unpaid,paid,refunded'],
                'notes' => ['nullable', 'string'],
            ]);
            $booking->update($data);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Trainer tidak boleh mengubah booking.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Booking updated.',
            'data' => $booking->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $booking = Booking::findOrFail($id);
        $user = Auth::user();
        $this->authorizeAccess($booking);

        if ($user->isTrainer()) {
            return response()->json([
                'success' => false,
                'message' => 'Trainer tidak boleh membatalkan booking.',
            ], 403);
        }

        if ($user->isMember()) {
            $session = $booking->session;
            $sessionStart = $session->session_date->setTimeFromTimeString($session->start_time);
            if ($sessionStart->diffInHours(now(), false) > -24) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembatalan hanya bisa dilakukan minimal 24 jam sebelum sesi.',
                ], 403);
            }
        }

        $reason = request('cancelled_reason', 'Dibatalkan oleh ' . ($user->isAdmin() ? 'admin' : 'member'));

        DB::transaction(function () use ($booking, $reason) {
            if ($booking->status !== 'cancelled') {
                $booking->session()->decrement('booked_count');
            }
            $booking->status = 'cancelled';
            $booking->cancelled_at = now();
            $booking->cancelled_reason = $reason;
            if ($booking->payment_status === 'paid') {
                $booking->payment_status = 'refunded';
            }
            $booking->save();
            $booking->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Booking dibatalkan.',
        ]);
    }

    private function authorizeAccess(Booking $booking): void
    {
        $user = Auth::user();
        if ($user->isAdmin()) return;
        if ($user->isMember() && $booking->user_id === $user->id) return;
        if ($user->isTrainer() && $booking->session && $booking->session->trainer_id === $user->trainer_id) return;
        abort(403, 'Anda tidak memiliki akses ke booking ini.');
    }
}
