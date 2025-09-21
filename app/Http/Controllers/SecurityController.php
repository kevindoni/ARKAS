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
            return response()->json(['uri' => null], 404);
        }

        try {
            $secret = decrypt($user->two_factor_secret);
        } catch (\Throwable $e) {
            return response()->json(['uri' => null], 500);
        }

        $issuer = rawurlencode(config('app.name', 'Laravel'));
        $account = rawurlencode($user->email);
        $uri = "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
        return response()->json(['uri' => $uri]);
    }
}
