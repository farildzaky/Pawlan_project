<?php

namespace App\Http\Controllers;

use App\Models\Trainer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TrainerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Trainer::query()->withCount('classes');

        if ($spec = $request->query('specialization')) {
            $query->where('specialization', 'like', "%{$spec}%");
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($classId = $request->query('class_id')) {
            $query->whereHas('classes', fn ($q) => $q->where('classes.id', $classId));
        }

        $perPage = (int) $request->query('per_page', 10);
        $items = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Trainers retrieved.',
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
        $trainer = Trainer::with('classes:id,name,category')->withCount('sessions')->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Trainer detail retrieved.',
            'data' => $trainer,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:trainers,email'],
            'phone' => ['required', 'string', 'max:20'],
            'specialization' => ['required', 'string', 'max:150'],
            'years_experience' => ['required', 'integer', 'min:0'],
            'certification' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['integer', 'exists:classes,id'],
        ]);

        $classIds = $data['class_ids'] ?? [];
        unset($data['class_ids']);

        $trainer = Trainer::create($data);
        if (! empty($classIds)) {
            $trainer->classes()->sync($classIds);
        }

        return response()->json([
            'success' => true,
            'message' => 'Trainer created.',
            'data' => $trainer->load('classes:id,name'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $trainer = Trainer::findOrFail($id);
        $user = Auth::user();

        // Trainer hanya bisa update profil sendiri (jika user terhubung ke trainer ini)
        if ($user->isTrainer() && $user->trainer_id !== $trainer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya bisa mengubah profil trainer milik Anda sendiri.',
            ], 403);
        }

        $rules = [
            'name' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'email', 'max:150', Rule::unique('trainers', 'email')->ignore($trainer->id)],
            'phone' => ['sometimes', 'string', 'max:20'],
            'specialization' => ['sometimes', 'string', 'max:150'],
            'years_experience' => ['sometimes', 'integer', 'min:0'],
            'certification' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
            'hourly_rate' => ['sometimes', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'class_ids' => ['sometimes', 'array'],
            'class_ids.*' => ['integer', 'exists:classes,id'],
        ];

        // Trainer tidak boleh ubah hourly_rate, is_active, class_ids
        if ($user->isTrainer()) {
            $rules = array_intersect_key($rules, array_flip(['phone', 'bio', 'certification']));
        }

        $data = $request->validate($rules);

        if (isset($data['class_ids'])) {
            $trainer->classes()->sync($data['class_ids']);
            unset($data['class_ids']);
        }

        $trainer->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Trainer updated.',
            'data' => $trainer->fresh()->load('classes:id,name'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $trainer = Trainer::findOrFail($id);

        $upcoming = $trainer->sessions()
            ->where('session_date', '>=', now()->toDateString())
            ->whereIn('status', ['scheduled', 'ongoing'])
            ->exists();

        if ($upcoming) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus trainer yang masih memiliki sesi terjadwal.',
            ], 409);
        }

        $trainer->update(['is_active' => false]);
        $trainer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Trainer deleted.',
        ]);
    }
}
