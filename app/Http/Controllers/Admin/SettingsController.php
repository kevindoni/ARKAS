<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index()
    {
        $appName = Setting::get('app_name', config('app.name', 'Laravel'));
        $appLogo = Setting::get('app_logo');
        $mail = [
            'mailer' => Setting::get('mail_mailer', config('mail.default')),
            'host' => Setting::get('mail_host', config('mail.mailers.'.config('mail.default').'.host')),
            'port' => Setting::get('mail_port', config('mail.mailers.'.config('mail.default').'.port')),
            'username' => Setting::get('mail_username', config('mail.mailers.'.config('mail.default').'.username')),
            'password' => Setting::get('mail_password', config('mail.mailers.'.config('mail.default').'.password')),
            'encryption' => Setting::get('mail_encryption', config('mail.mailers.'.config('mail.default').'.encryption')),
            'from_address' => Setting::get('mail_from_address', config('mail.from.address')),
            'from_name' => Setting::get('mail_from_name', config('mail.from.name')),
            'test_to' => Setting::get('mail_test_to', auth()->user()->email ?? ''),
        ];
        return view('admin.settings.index', compact('appName', 'appLogo', 'mail'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'app_name' => ['required','string','max:100'],
            // Use mimes instead of image to allow SVG gracefully
            'app_logo' => ['nullable','mimes:png,jpg,jpeg,svg','max:2048'],
            'active_tab' => ['nullable','string'],
            // Optional mail settings saved for convenience and test mail overrides
            'mail_mailer' => ['nullable','in:smtp,log,mailgun,ses,postmark'],
            'mail_host' => ['nullable','string','max:255'],
            'mail_port' => ['nullable','integer','min:1','max:65535'],
            'mail_encryption' => ['nullable','in:,tls,ssl,null'],
            'mail_username' => ['nullable','string','max:255'],
            'mail_password' => ['nullable','string','max:255'],
            'mail_from_address' => ['nullable','email'],
            'mail_from_name' => ['nullable','string','max:100'],
            'mail_test_to' => ['nullable','email'],
        ]);

        Setting::set('app_name', $data['app_name']);

        $logoTried = false;
        $logoSaved = false;
        if ($request->hasFile('app_logo')) {
            $file = $request->file('app_logo');
            $logoTried = $file && (string) $file->getClientOriginalName() !== '';
            try {
                if ($file) {
                    $error = $file->getError();
                    $realPath = $file->getRealPath();
                    $tmpPath = $file->getPathname();
                    $isReadable = $realPath && @is_readable($realPath);
                    $sizeOk = ($file->getSize() ?? 0) > 0;

                    if ($logoTried && $error === UPLOAD_ERR_OK && $file->isValid() && $tmpPath && $sizeOk) {
                        // Ensure destination directory exists
                        Storage::disk('public')->makeDirectory('logos');

                        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
                        $filename = 'logo_' . Str::uuid()->toString() . '.' . $ext;
                        // Prefer putFileAs; fallback to storeAs if needed
                        $stored = false;
                        try {
                            $stored = Storage::disk('public')->putFileAs('logos', $file, $filename);
                        } catch (\Throwable $inner) {
                            Log::debug('putFileAs failed, will try storeAs', ['error' => $inner->getMessage()]);
                            try {
                                $file->storeAs('logos', $filename, 'public');
                                $stored = true;
                            } catch (\Throwable $inner2) {
                                Log::debug('storeAs also failed, will try streaming fallback', ['error' => $inner2->getMessage()]);
                                // Final fallback: stream copy into storage
                                try {
                                    $sourcePath = $realPath ?: $tmpPath;
                                    if ($sourcePath) {
                                        $stream = @fopen($sourcePath, 'rb');
                                        if ($stream) {
                                            $stored = Storage::disk('public')->put('logos/'.$filename, $stream);
                                            @fclose($stream);
                                            if (! $stored) {
                                                // As a last resort attempt copy to absolute storage path
                                                $target = storage_path('app/public/logos/'.$filename);
                                                @is_dir(dirname($target)) || @mkdir(dirname($target), 0775, true);
                                                if (@copy($sourcePath, $target)) {
                                                    $stored = true;
                                                }
                                            }
                                            if ($stored) {
                                                Log::info('Stored app_logo via streaming/copy fallback');
                                            }
                                        } else {
                                            Log::debug('Unable to open upload stream for app_logo', ['path' => $sourcePath]);
                                        }
                                    } else {
                                        Log::debug('No source path available for app_logo during fallback');
                                    }
                                } catch (\Throwable $inner3) {
                                    Log::debug('Streaming fallback failed for app_logo', ['error' => $inner3->getMessage()]);
                                }
                            }
                        }

                        if ($stored) {
                            Setting::set('app_logo', 'logos/' . $filename);
                            $logoSaved = true;
                        } else {
                            Log::error('Failed to store app_logo after all methods');
                        }
                    } else if ($logoTried) {
                        Log::warning('Invalid or unreadable app_logo upload', [
                            'error' => $error,
                            'realPath' => $realPath,
                            'tmpPath' => $tmpPath,
                            'isReadable' => $isReadable,
                            'sizeOk' => $sizeOk,
                            'clientSize' => $file->getSize(),
                            'clientName' => $file->getClientOriginalName(),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('File upload error (app_logo): ' . $e->getMessage(), [
                    'exception' => $e,
                ]);
                // Optionally surface to UI:
                // return back()->withErrors(['app_logo' => 'Gagal mengunggah logo, silakan coba lagi.']);
            }
        }

        // Persist optional mail settings
    $mailKeys = ['mail_mailer','mail_host','mail_port','mail_encryption','mail_username','mail_password','mail_from_address','mail_from_name','mail_test_to'];
        foreach ($mailKeys as $k) {
            if ($request->has($k)) {
                $val = $request->input($k);
                // normalize empty string to null for encryption
                if ($k === 'mail_encryption' && ($val === 'null' || $val === '')) { $val = null; }
                Setting::set($k, $val);
            }
        }

        $activeTab = $data['active_tab'] ?? 'branding';
        $message = 'Pengaturan tersimpan.';
        if ($logoTried) {
            $message .= $logoSaved ? ' Logo diperbarui.' : ' Logo tidak tersimpan.';
        }
        return redirect()->route('admin.settings')
            ->with('status', $message)
            ->with('logo_saved', $logoSaved)
            ->with('active_tab', $activeTab);
    }

    public function testMail(Request $request)
    {
        $request->validate([
            'to' => ['nullable','email'],
            'use_ui' => ['nullable','boolean'],
        ]);
        $user = $request->user();
        $to = $request->input('to') ?: Setting::get('mail_test_to') ?: ($user ? $user->email : null);
        if (! $to) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Tidak ada alamat email tujuan.'], 422);
            }
            return redirect()->route('admin.settings')
                ->with('active_tab', 'mail')
                ->with('mail_status', 'Tidak ada alamat email tujuan.');
        }
        try {
            $used = 'runtime';
            if ($request->boolean('use_ui')) {
                $used = 'ui';
                // Build a temporary mailer from saved settings
                $selMailer = Setting::get('mail_mailer', config('mail.default'));
                $host = Setting::get('mail_host', config('mail.mailers.'.config('mail.default').'.host'));
                $port = (int) Setting::get('mail_port', config('mail.mailers.'.config('mail.default').'.port'));
                $encryption = Setting::get('mail_encryption', config('mail.mailers.'.config('mail.default').'.encryption'));
                if ($encryption === 'null' || $encryption === '') { $encryption = null; }
                $username = Setting::get('mail_username', config('mail.mailers.'.config('mail.default').'.username'));
                $password = Setting::get('mail_password', config('mail.mailers.'.config('mail.default').'.password'));
                $fromAddr = Setting::get('mail_from_address', config('mail.from.address'));
                $fromName = Setting::get('mail_from_name', config('mail.from.name'));

                $transport = [];
                if ($selMailer === 'log' || empty($host)) {
                    $transport = [ 'transport' => 'log', 'channel' => config('mail.log_channel') ];
                } else {
                    $transport = [
                        'transport' => 'smtp',
                        'host' => $host,
                        'port' => $port ?: 587,
                        'encryption' => $encryption,
                        'username' => $username ?: null,
                        'password' => $password ?: null,
                        'timeout' => null,
                        'auth_mode' => null,
                    ];
                }
                // Temporarily set a dedicated mailer config
                $prevFrom = [ 'address' => config('mail.from.address'), 'name' => config('mail.from.name') ];
                config([
                    'mail.mailers.ui' => $transport,
                    'mail.from.address' => $fromAddr,
                    'mail.from.name' => $fromName,
                ]);
                try {
                    Mail::mailer('ui')->raw('Ini adalah email uji dari ARKAS. Jika Anda melihat ini di tujuan, konfigurasi berhasil.', function ($message) use ($to) {
                        $message->to($to)->subject('Tes Email ARKAS');
                    });
                } finally {
                    // Restore from name/address for safety within this request cycle
                    config(['mail.from.address' => $prevFrom['address'], 'mail.from.name' => $prevFrom['name']]);
                }
            } else {
                // Use runtime (.env) mailer
                Mail::raw('Ini adalah email uji dari ARKAS. Jika Anda melihat ini di tujuan, konfigurasi berhasil.', function ($message) use ($to) {
                    $message->to($to)->subject('Tes Email ARKAS');
                });
            }
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Email uji terkirim (via '.$used.') ke '.$to.' (cek inbox / Mailpit).']);
            }
            return redirect()->route('admin.settings')
                ->with('active_tab', 'mail')
                ->with('mail_status', 'Email uji terkirim (via '.$used.') ke '.$to.' (cek inbox / Mailpit).');
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim email uji', ['error' => $e->getMessage()]);
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Gagal mengirim email uji: '.$e->getMessage()], 500);
            }
            return redirect()->route('admin.settings')
                ->with('active_tab', 'mail')
                ->with('mail_status', 'Gagal mengirim email uji: '.$e->getMessage());
        }
    }
}
