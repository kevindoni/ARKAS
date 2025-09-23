<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function index(Request $request)
    {
        return view('security.index');
    }

    public function otpauth(Request $request)
    {
        $user = $request->user();
        if (! $user || ! $user->two_factor_secret) {
            return response()->json([
                'uri' => null, 
                'error' => 'Two-factor authentication not configured'
            ], 404);
        }

        try {
            $secret = decrypt($user->two_factor_secret);
        } catch (\Throwable $e) {
            \Log::error('Failed to decrypt two-factor secret', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'uri' => null, 
                'error' => 'Failed to decrypt two-factor secret'
            ], 500);
        }

        $issuer = rawurlencode(config('app.name', 'Laravel'));
        $account = rawurlencode($user->email);
        $uri = "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
        return response()->json(['uri' => $uri]);
    }
}
