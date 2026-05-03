<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\GymClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ClassSession::query()->with([
            'gymClass:id,name,category,image',
            'trainer:id,name,specialization',
        ]);

        if ($date = $request->query('date')) {
            $query->whereDate('session_date', $date);
        }
        if ($from = $request->query('date_from')) {
            $query->whereDate('session_date', '>=', $from);
        }
        if ($to = $request->query('date_to')) {
            $query->whereDate('session_date', '<=', $to);
        }
        if ($cid = $request->query('class_id')) {
            $query->where('class_id', $cid);
        }
        if ($tid = $request->query('trainer_id')) {
            $query->where('trainer_id', $tid);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($request->boolean('available')) {
            $query->whereColumn('booked_count', '<', 'capacity');
        }

        $perPage = (int) $request->query('per_page', 10);
        $items = $query->orderBy('session_date')->orderBy('start_time')->paginate($perPage);

        $items->getCollection()->transform(function ($s) {
            if ($s->gymClass && $s->gymClass->image) {
                $s->gymClass->image_url = asset('storage/' . $s->gymClass->image);
            }
            return $s;
        });

        return response()->json([
            'success' => true,
            'message' => 'Sessions retrieved.',
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
        $session = ClassSession::with([
            'gymClass:id,name,category,description,image',
            'trainer:id,name,specialization,bio',
        ])->findOrFail($id);

        if ($session->gymClass && $session->gymClass->image) {
            $session->gymClass->image_url = asset('storage/' . $session->gymClass->image);
        }

        return response()->json([
            'success' => true,
            'message' => 'Session detail retrieved.',
            'data' => $session,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'trainer_id' => ['required', 'integer', 'exists:trainers,id'],
            'session_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'location' => ['required', 'string', 'max:100'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $class = GymClass::findOrFail($data['class_id']);

        // Validasi: trainer terdaftar mengajar kelas ini
        $isTrainerForClass = $class->trainers()->where('trainers.id', $data['trainer_id'])->exists();
        if (! $isTrainerForClass) {
            return response()->json([
                'success' => false,
                'message' => 'Trainer tidak terdaftar mengajar kelas ini.',
                'errors' => ['trainer_id' => ['Trainer tidak terdaftar mengajar kelas ini.']],
            ], 422);
        }

        // Validasi: bentrok jadwal trainer
        if ($this->trainerHasConflict($data['trainer_id'], $data['session_date'], $data['start_time'], $data['end_time'])) {
            return response()->json([
                'success' => false,
                'message' => 'Trainer memiliki bentrok jadwal pada waktu tersebut.',
                'errors' => ['start_time' => ['Bentrok jadwal trainer.']],
            ], 422);
        }

        $data['capacity'] = $data['capacity'] ?? $class->max_capacity;
        $data['price'] = $data['price'] ?? $class->price;

        $session = ClassSession::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Session created.',
            'data' => $session->load(['gymClass:id,name', 'trainer:id,name']),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $session = ClassSession::findOrFail($id);

        // Jika sudah ada booking, hanya notes & location yang boleh diubah
        if ($session->booked_count > 0) {
            $data = $request->validate([
                'notes' => ['nullable', 'string'],
                'location' => ['sometimes', 'string', 'max:100'],
            ]);
            $session->update($data);
            return response()->json([
                'success' => true,
                'message' => 'Session updated (limited fields karena ada booking aktif).',
                'data' => $session->fresh(),
            ]);
        }

        $data = $request->validate([
            'class_id' => ['sometimes', 'integer', 'exists:classes,id'],
            'trainer_id' => ['sometimes', 'integer', 'exists:trainers,id'],
            'session_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i'],
            'location' => ['sometimes', 'string', 'max:100'],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:scheduled,ongoing,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        $trainerId = $data['trainer_id'] ?? $session->trainer_id;
        $sessionDate = $data['session_date'] ?? $session->session_date->toDateString();
        $startTime = $data['start_time'] ?? $session->start_time;
        $endTime = $data['end_time'] ?? $session->end_time;

        if (isset($data['start_time']) || isset($data['end_time'])) {
            if ($startTime >= $endTime) {
                return response()->json([
                    'success' => false,
                    'message' => 'start_time harus kurang dari end_time.',
                    'errors' => ['end_time' => ['end_time harus setelah start_time.']],
                ], 422);
            }
        }

        if ($this->trainerHasConflict($trainerId, $sessionDate, $startTime, $endTime, $session->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Trainer memiliki bentrok jadwal pada waktu tersebut.',
                'errors' => ['start_time' => ['Bentrok jadwal trainer.']],
            ], 422);
        }

        $session->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Session updated.',
            'data' => $session->fresh()->load(['gymClass:id,name', 'trainer:id,name']),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $session = ClassSession::findOrFail($id);

        DB::transaction(function () use ($session) {
            // Cancel all bookings & refund yang sudah dibayar
            foreach ($session->bookings()->whereIn('status', ['pending', 'confirmed'])->get() as $booking) {
                $booking->status = 'cancelled';
                $booking->cancelled_at = now();
                $booking->cancelled_reason = 'Sesi dibatalkan oleh admin.';
                if ($booking->payment_status === 'paid') {
                    $booking->payment_status = 'refunded';
                }
                $booking->save();
                $booking->delete();
            }
            $session->status = 'cancelled';
            $session->save();
            $session->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Session deleted (semua booking terkait dibatalkan).',
        ]);
    }

    private function trainerHasConflict(int $trainerId, string $date, string $start, string $end, ?int $excludeId = null): bool
    {
        $q = ClassSession::where('trainer_id', $trainerId)
            ->whereDate('session_date', $date)
            ->whereIn('status', ['scheduled', 'ongoing'])
            ->where(function ($q) use ($start, $end) {
                $q->where(function ($q) use ($start, $end) {
                    $q->where('start_time', '<', $end)
                      ->where('end_time', '>', $start);
                });
            });

        if ($excludeId) {
            $q->where('id', '!=', $excludeId);
        }

        return $q->exists();
    }
}
