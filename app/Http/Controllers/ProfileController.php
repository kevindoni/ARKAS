<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index');
    }

    public function updateInfo(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email,'.$user->id],
        ]);

        $emailChanged = $data['email'] !== $user->email;
        $user->fill($data)->save();

        // If email changed and email verification is enabled, mark unverified and send new verification notification
        if ($emailChanged) {
            $user->email_verified_at = null;
            $user->save();
            if (method_exists($user, 'sendEmailVerificationNotification')) {
                $user->sendEmailVerificationNotification();
            }
            return back()->with('status', 'Profil diperbarui. Tautan verifikasi telah dikirim ke email baru Anda.');
        }

        return back()->with('status', 'Profil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required','confirmed','min:8'],
        ]);

        $user = $request->user();
        if (! Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return back()->with('status', 'Password diperbarui.');
    }
}
