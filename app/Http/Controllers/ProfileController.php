<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        $activityLog = \Spatie\Activitylog\Models\Activity::causedBy(auth()->user())
            ->latest()
            ->take(10)
            ->get();

        return view('profile', compact('activityLog'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone'   => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update($validated);

        return redirect()->route('profile.index')
            ->with('profile_success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Password lama tidak sesuai.'])
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.index')
            ->with('password_success', 'Password berhasil diperbarui.');
    }
}