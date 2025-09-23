<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Auth\MustVerifyEmail;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Simple search by name or email
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Optional role filter: admin or user
        if ($role = $request->input('role')) {
            if ($role === 'admin') {
                $query->where('is_admin', true);
            } elseif ($role === 'user') {
                $query->where(function ($q) {
                    $q->whereNull('is_admin')->orWhere('is_admin', false);
                });
            }
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        // Use database transaction to prevent race conditions
        return \DB::transaction(function () use ($validated, $user) {
            // Protect against removing the last admin - check with FOR UPDATE lock
            $isAdmin = (bool)($validated['is_admin'] ?? false);
            if (!$isAdmin && ($user->is_admin ?? false)) {
                $otherAdmins = User::where('id', '!=', $user->id)
                    ->where('is_admin', true)
                    ->lockForUpdate() // Prevent race conditions
                    ->count();
                if ($otherAdmins === 0) {
                    return back()->withErrors(['is_admin' => 'Tidak bisa mencabut peran admin dari admin terakhir.'])->withInput();
                }
            }

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->is_admin = $isAdmin;
            $user->save();

            return redirect()->route('admin.users.edit', $user)->with('status', 'Pengguna berhasil diperbarui.');
        });
    }

    public function destroy(Request $request, User $user)
    {
        // Prevent self-delete from admin panel
        if ($request->user()->id === $user->id) {
            return back()->withErrors(['delete' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        // Prevent deleting the last admin account
        if ($user->is_admin) {
            $otherAdmins = User::where('id', '!=', $user->id)->where('is_admin', true)->count();
            if ($otherAdmins === 0) {
                return back()->withErrors(['delete' => 'Tidak bisa menghapus admin terakhir.']);
            }
        }

        $user->delete();
        return redirect()->route('admin.users')->with('status', 'Pengguna telah dihapus.');
    }

    public function resendVerification(Request $request, User $user)
    {
        // Only send if the User implements MustVerifyEmail and is unverified
        if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            return back()->with('status', 'Email verifikasi telah dikirim ulang ke ' . $user->email . '.');
        }
        return back()->with('status', 'Email pengguna sudah terverifikasi.');
    }

    public function resetTwoFactor(User $user, DisableTwoFactorAuthentication $disabler)
    {
        // Disable 2FA for the given user
        $disabler($user);
        return back()->with('status', "2FA untuk {$user->email} telah dinonaktifkan.");
    }

    public function regenRecoveryCodes(User $user, GenerateNewRecoveryCodes $generator)
    {
        // Ensure user has 2FA secret before generating codes; if not, return with error
        if (is_null($user->two_factor_secret)) {
            return back()->withErrors(['codes' => 'Pengguna belum mengaktifkan 2FA.']);
        }
        $generator($user);
        return back()->with('status', "Recovery codes baru untuk {$user->email} telah dibuat.");
    }
}
