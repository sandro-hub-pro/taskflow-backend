<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate($request->per_page ?? 15);

        return response()->json($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique(User::class)],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', Password::defaults()],
            'role' => ['required', Rule::in(['admin', 'incharge', 'user'])],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('profile_picture')) {
            $validated['profile_picture'] = $request->file('profile_picture')
                ->store('profile_pictures', 'public');
        }

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'message' => 'User created successfully.',
            'user' => $user,
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['projects', 'assignedTasks']);

        return response()->json($user);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['sometimes', 'string', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'password' => ['sometimes', Password::defaults()],
            'role' => ['sometimes', Rule::in(['admin', 'incharge', 'user'])],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('profile_picture')) {
            // Delete old picture
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $validated['profile_picture'] = $request->file('profile_picture')
                ->store('profile_pictures', 'public');
        }

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    /**
     * Update profile picture.
     */
    public function updateProfilePicture(Request $request): JsonResponse
    {
        $request->validate([
            'profile_picture' => ['required', 'image', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $path = $request->file('profile_picture')->store('profile_pictures', 'public');
        $user->update(['profile_picture' => $path]);

        return response()->json([
            'message' => 'Profile picture updated successfully.',
            'profile_picture' => $path,
        ]);
    }

    /**
     * Update own profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'username' => ['sometimes', 'string', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }
}

