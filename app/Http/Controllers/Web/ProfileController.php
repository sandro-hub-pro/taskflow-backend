<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show profile edit form.
     */
    public function edit(): View
    {
        return view('profile.edit');
    }

    /**
     * Update user profile.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        // Check if email changed
        if ($validated['email'] !== $user->email) {
            $validated['email_verified_at'] = null;
        }

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update profile picture.
     */
    public function updatePicture(Request $request): RedirectResponse
    {
        $request->validate([
            'profile_picture' => ['required', 'image', 'max:10240'],
        ]);

        $user = $request->user();

        // Delete old picture
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $path = $request->file('profile_picture')->store('profile_pictures', 'public');
        $user->update(['profile_picture' => $path]);

        return back()->with('success', 'Profile picture updated successfully.');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    /**
     * Show settings page.
     */
    public function settings(): View
    {
        return view('profile.settings');
    }
}

