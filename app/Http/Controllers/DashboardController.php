<?php

namespace App\Http\Controllers;

use App\Models\BkuMasterEntry;
use App\Models\TunaiMasterEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Redirect admin to admin dashboard
        if ($user && ($user->is_admin ?? false)) {
            return redirect()->route('admin.dashboard');
        }
        
        $currentMonth = Carbon::now()->format('Y-m');
        $currentYear = Carbon::now()->year;

        // Get BKU statistics
        $bkuStats = $this->getBkuStatistics($user->id, $currentMonth, $currentYear);
        
        // Get Tunai statistics
        $tunaiStats = $this->getTunaiStatistics($user->id, $currentMonth, $currentYear);

        // Get recent transactions (from both tables)
        $recentBku = BkuMasterEntry::where('user_id', $user->id)
            ->whereNotNull('tanggal')
            ->latest('tanggal')
            ->latest('id')
            ->take(3)
            ->get();

        $recentTunai = TunaiMasterEntry::where('user_id', $user->id)
            ->whereNotNull('tanggal')
            ->latest('tanggal')
            ->latest('id')
            ->take(2)
            ->get();

        // Get current saldo from latest entry
        $latestBku = BkuMasterEntry::where('user_id', $user->id)
            ->whereNotNull('tanggal')
            ->latest('tanggal')
            ->latest('id')
            ->first();

        $latestTunai = TunaiMasterEntry::where('user_id', $user->id)
            ->whereNotNull('tanggal')
            ->latest('tanggal')
            ->latest('id')
            ->first();

        return view('dashboard', compact(
            'bkuStats',
            'tunaiStats', 
            'recentBku',
            'recentTunai',
            'latestBku',
            'latestTunai'
        ));
    }

    private function getBkuStatistics($userId, $currentMonth, $currentYear)
    {
        $baseQuery = BkuMasterEntry::where('user_id', $userId)->whereNotNull('tanggal');

        // Current month statistics
        $monthlyQuery = clone $baseQuery;
        $monthlyQuery->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$currentMonth]);

        $monthlyPenerimaan = $monthlyQuery->sum('penerimaan');
        $monthlyPengeluaran = $monthlyQuery->sum('pengeluaran');
        $monthlyTransactions = $monthlyQuery->count();

        // Tax-related transactions this month
        $taxQuery = clone $monthlyQuery;
        $taxTransactions = $taxQuery->where(function($query) {
            $query->where('uraian', 'like', '%PPh%')
                  ->orWhere('uraian', 'like', '%PPN%')
                  ->orWhere('uraian', 'like', '%Pajak%');
        })
        ->where(function($query) {
            $query->where('penerimaan', '>', 0)
                  ->orWhere('pengeluaran', '>', 0);
        })
        ->count();

        // Yearly statistics
        $yearlyQuery = clone $baseQuery;
        $yearlyQuery->whereYear('tanggal', $currentYear);

        return [
            'monthly_penerimaan' => $monthlyPenerimaan,
            'monthly_pengeluaran' => $monthlyPengeluaran,
            'monthly_transactions' => $monthlyTransactions,
            'monthly_tax_transactions' => $taxTransactions,
            'yearly_transactions' => $yearlyQuery->count(),
            'monthly_net' => $monthlyPenerimaan - $monthlyPengeluaran
        ];
    }

    private function getTunaiStatistics($userId, $currentMonth, $currentYear)
    {
        $baseQuery = TunaiMasterEntry::where('user_id', $userId)->whereNotNull('tanggal');

        // Current month statistics
        $monthlyQuery = clone $baseQuery;
        $monthlyQuery->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$currentMonth]);

        $monthlyPenerimaan = $monthlyQuery->sum('penerimaan');
        $monthlyPengeluaran = $monthlyQuery->sum('pengeluaran');
        $monthlyTransactions = $monthlyQuery->count();

        // Yearly statistics
        $yearlyQuery = clone $baseQuery;
        $yearlyQuery->whereYear('tanggal', $currentYear);

        return [
            'monthly_penerimaan' => $monthlyPenerimaan,
            'monthly_pengeluaran' => $monthlyPengeluaran,
            'monthly_transactions' => $monthlyTransactions,
            'yearly_transactions' => $yearlyQuery->count(),
            'monthly_net' => $monthlyPenerimaan - $monthlyPengeluaran
        ];
    }
}