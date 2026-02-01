<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show profile page
     */
    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    /**
     * Update profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'regex:/^(\+62|62|0)[0-9]{9,13}$/'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'current_password' => ['nullable', 'required_with:password'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ], [
            'avatar.image' => 'File yang diupload harus berupa gambar.',
            'avatar.mimes' => 'Format gambar harus JPG, PNG, GIF, atau WEBP.',
            'avatar.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        // Update basic info
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (isset($validated['phone'])) {
            $user->phone = $validated['phone'];
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            try {
                // Delete old avatar if exists
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                // Store new avatar
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $avatarPath;
            } catch (\Exception $e) {
                return back()->withErrors(['avatar' => 'Gagal mengupload foto: ' . $e->getMessage()]);
            }
        }

        // Update password if provided
        if ($request->filled('password')) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
            }
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
