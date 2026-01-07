<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $query = User::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(15);

        $stats = [
            'total' => User::count(),
            'superadmins' => User::where('role', 'superadmin')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'incharges' => User::where('role', 'incharge')->count(),
            'users' => User::where('role', 'user')->count(),
        ];

        return view('users.index', compact('users', 'stats'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', Rule::in(['superadmin', 'admin', 'incharge', 'user'])],
            'profile_picture' => ['nullable', 'image', 'max:10240'],
        ]);

        if ($request->hasFile('profile_picture')) {
            $validated['profile_picture'] = $request->file('profile_picture')
                ->store('profile_pictures', 'public');
        }

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return redirect()->route('users.show', $user)->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): View
    {
        $user->load(['projects', 'assignedTasks.project']);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['sometimes', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', Password::defaults()],
            'role' => ['sometimes', Rule::in(['superadmin', 'admin', 'incharge', 'user'])],
            'profile_picture' => ['nullable', 'image', 'max:10240'],
        ]);

        if ($request->hasFile('profile_picture')) {
            // Delete old picture
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $validated['profile_picture'] = $request->file('profile_picture')
                ->store('profile_pictures', 'public');
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.show', $user)->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['message' => 'You cannot delete your own account.']);
        }

        // Only superadmin can delete admin or superadmin users
        if ($user->isAdmin() && !auth()->user()->isSuperAdmin()) {
            return back()->withErrors(['message' => 'Only superadmin can delete admin users.']);
        }

        // Superadmin cannot be deleted by anyone except themselves (which is already blocked above)
        if ($user->isSuperAdmin()) {
            return back()->withErrors(['message' => 'Superadmin accounts cannot be deleted.']);
        }

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}

