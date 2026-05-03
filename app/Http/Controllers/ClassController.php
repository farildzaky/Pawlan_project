<?php

namespace App\Http\Controllers;

use App\Models\GymClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClassController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = GymClass::query()->with('trainers:id,name,specialization')->withCount('sessions');

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }
        if ($difficulty = $request->query('difficulty')) {
            $query->where('difficulty_level', $difficulty);
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->query('per_page', 10);
        $items = $query->orderByDesc('created_at')->paginate($perPage);
        $items->getCollection()->transform(fn ($c) => $this->withImageUrl($c));

        return response()->json([
            'success' => true,
            'message' => 'Classes retrieved.',
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $class = GymClass::with('trainers:id,name,specialization,bio')->withCount('sessions')->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Class detail retrieved.',
            'data' => $this->withImageUrl($class),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'category' => ['required', 'in:cardio,strength,flexibility,mind-body'],
            'difficulty_level' => ['required', 'in:beginner,intermediate,advanced'],
            'duration_minutes' => ['required', 'integer', 'between:15,180'],
            'max_capacity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:51200'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('classes', 'public');
        }

        $class = GymClass::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Class created.',
            'data' => $this->withImageUrl($class),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $class = GymClass::findOrFail($id);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['sometimes', 'string'],
            'category' => ['sometimes', 'in:cardio,strength,flexibility,mind-body'],
            'difficulty_level' => ['sometimes', 'in:beginner,intermediate,advanced'],
            'duration_minutes' => ['sometimes', 'integer', 'between:15,180'],
            'max_capacity' => ['sometimes', 'integer', 'min:1'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:51200'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            if ($class->image && Storage::disk('public')->exists($class->image)) {
                Storage::disk('public')->delete($class->image);
            }
            $data['image'] = $request->file('image')->store('classes', 'public');
        }

        $class->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Class updated.',
            'data' => $this->withImageUrl($class->fresh()),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $class = GymClass::findOrFail($id);

        if ($class->image && Storage::disk('public')->exists($class->image)) {
            Storage::disk('public')->delete($class->image);
        }

        $class->delete();

        return response()->json([
            'success' => true,
            'message' => 'Class deleted.',
        ]);
    }

    private function withImageUrl(GymClass $class): GymClass
    {
        if ($class->image) {
            $class->image_url = asset('storage/' . $class->image);
        } else {
            $class->image_url = null;
        }
        return $class;
    }
}
