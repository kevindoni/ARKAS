<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BkuMasterEntry;
use App\Models\TunaiMasterEntry;
use App\Models\School;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // User statistics
        $totalUsers = User::where(function($q) {
            $q->whereNull('is_admin')->orWhere('is_admin', false);
        })->count();
        $admins = User::where('is_admin', true)->count();
        $twoFaEnabled = User::whereNotNull('two_factor_secret')->count();
        $twoFaConfirmed = User::whereNotNull('two_factor_confirmed_at')->count();
        $unverified = User::whereNull('email_verified_at')->count();
        $recentUsers = User::latest()->take(5)->get(['id','name','email','created_at','email_verified_at']);

        // School statistics (subscription-ready)
        $totalSchools = School::count();
        $completeSchools = School::whereNotNull('nama_sekolah')
            ->whereNotNull('kepala_nama')
            ->whereNotNull('bendahara_nama')
            ->count();

        // Future: subscription statistics
        $subscriptionStats = [
            'active_subscriptions' => $totalSchools, // For now, all schools are considered "active"
            'trial_schools' => 0, // Future implementation
            'expired_subscriptions' => 0, // Future implementation
        ];

        // System usage statistics (count only, no financial data)
        $systemStats = [
            'total_bku_entries' => BkuMasterEntry::count(),
            'total_tunai_entries' => TunaiMasterEntry::count(),
            'active_users' => User::whereHas('bkuEntries')->distinct()->count('id'),
            'users_with_data' => User::where(function($query) {
                $query->whereHas('bkuEntries')->orWhereHas('tunaiEntries');
            })->count(),
        ];

        $stats = [
            'totalUsers' => $totalUsers,
            'admins' => $admins,
            'totalSchools' => $totalSchools,
            'completeSchools' => $completeSchools,
            'twoFaEnabled' => $twoFaEnabled,
            'twoFaConfirmed' => $twoFaConfirmed,
            'unverified' => $unverified,
        ];

        $system = [
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'env' => config('app.env'),
            'debug' => config('app.debug') ? 'on' : 'off',
            'timezone' => config('app.timezone'),
            'app_url' => config('app.url'),
        ];

        return view('admin.dashboard', compact('stats','recentUsers','system', 'systemStats', 'subscriptionStats'));
    }
}
