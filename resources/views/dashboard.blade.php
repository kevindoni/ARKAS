@extends('adminlte.layouts.user')

@section('page_title', 'Dashboard Bendahara')

@push('styles')
<style>
    .small-box { min-height: 140px; }
    .small-box .inner { min-height: 100px; display: flex; flex-direction: column; justify-content: center; }
    .small-box h3 { font-size: 1.1rem; font-weight: 700; line-height: 1.2; }
    .small-box h3.currency { font-size: 1.3rem; font-weight: 700; line-height: 0.9; }
    /* Ensure content doesn't collide with the absolute-positioned icon on the right */
    .small-box .inner { min-height: 100px; display: flex; flex-direction: column; justify-content: center; padding-right: 72px; }
    .small-box h3 { font-size: 1.1rem; font-weight: 700; line-height: 1.2; margin: 0; }
    /* Prevent long currency text from overflowing the card */
        .small-box h3.currency {
            display: flex;
            align-items: baseline;
            gap: .25rem;
            font-size: 1.36rem; /* base size, will auto-shrink via JS if needed */
            font-weight: 700;
            line-height: 1;     /* tighter for single-line */
            margin: 0;
            max-width: 100%;
            white-space: nowrap;      /* keep currency on a single line */
            overflow: hidden;         /* prevent spillover */
            text-overflow: clip;      /* no ellipsis; we auto-fit instead */
            letter-spacing: 0.006em;  /* subtle tracking for readability */
            font-variant-numeric: tabular-nums; /* stable width digits */
        }
        .small-box h3.currency .prefix { font-size: 0.88em; font-weight: 600; opacity: .9; letter-spacing: 0; }
        .small-box h3.currency .amount { letter-spacing: 0.01em; }

        /* Larger headline on desktop */
        @media (min-width: 1200px) {
            .small-box h3.currency { font-size: 1.44rem; }
        }
        @media (min-width: 1400px) {
            .small-box h3.currency { font-size: 1.5rem; }
        }
    .small-box p { font-size: .8rem; margin-bottom: 0; }
    .small-box .icon { top: 8px; }
    .card .card-title { font-weight: 600; }
    .text-white .btn.btn-link { color: #fff; }
    .text-white .btn.btn-link:hover { color: #f8f9fa; }
    /* Custom 7-column layout for XL screens */
    @media (min-width: 1200px) {
        .col-xl-1-7 {
            flex: 0 0 14.285714%;
            max-width: 14.285714%;
        }
    }
    .bg-teal { background-color: #20c997 !important; color: #fff; }
    .bg-teal .small-box-footer { color: #fff; }
    .bg-orange { background-color: #fd7e14 !important; color: #fff; }
    .bg-orange .small-box-footer { color: #fff; }
    .bg-gradient-purple { background: linear-gradient(45deg, #6f42c1, #8a63d2) !important; color: #fff; }
    .bg-gradient-purple .small-box-footer { color: #fff; }
    .bg-gradient-maroon { background: linear-gradient(45deg, #800020, #a0002a) !important; color: #fff; }
    .bg-gradient-maroon .small-box-footer { color: #fff; }
    .bg-gradient-navy { background: linear-gradient(45deg, #001f3f, #003366) !important; color: #fff; }
    .bg-gradient-navy .small-box-footer { color: #fff; }
    .bg-gradient-lime { background: linear-gradient(45deg, #32cd32, #7fff00) !important; color: #fff; }
    .bg-gradient-lime .small-box-footer { color: #fff; }
    .bg-gradient-indigo { background: linear-gradient(45deg, #6610f2, #8540f5) !important; color: #fff; }
    .bg-gradient-indigo .small-box-footer { color: #fff; }
    .bg-gradient-pink { background: linear-gradient(45deg, #e83e8c, #f16ba2) !important; color: #fff; }
    .bg-gradient-pink .small-box-footer { color: #fff; }
    .bg-gradient-cyan { background: linear-gradient(45deg, #17a2b8, #3dd5f3) !important; color: #fff; }
    .bg-gradient-cyan .small-box-footer { color: #fff; }
    .currency { font-family: 'Courier New', monospace; }
    .transaction-item { border-left: 4px solid #007bff; padding-left: 10px; margin-bottom: 10px; }
    .transaction-date { font-size: 0.8rem; color: #6c757d; }
    .transaction-amount { font-weight: bold; }
    .amount-in { color: #28a745; }
    .amount-out { color: #dc3545; }

    /* Responsive tweaks for small widths to keep currency inside box */
    @media (max-width: 575.98px) {
        .small-box h3.currency { font-size: 1.2rem; line-height: 1.15; }
        .small-box .inner { padding-right: 64px; }
    }
</style>
@endpush

@section('content')
@php
    $user = auth()->user();
    $isAdmin = (bool) ($user->is_admin ?? false);
    $twoFactorActive = !empty($user->two_factor_secret);
    $school = $user->school;
    $isSchoolComplete = $school && !empty($school->nama_sekolah) && !empty($school->status_sekolah)
                                     && !empty($school->kepala_nama) && !empty($school->bendahara_nama);
@endphp

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-coins mr-2"></i>
            Selamat datang, <strong>{{ $user->name }}</strong> - Sistem Manajemen Keuangan Sekolah
        </div>
    </div>
</div>

<div class="row">
    @if($isAdmin)
        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ \App\Models\User::count() }}</h3>
                    <p>Total Pengguna</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ route('admin.users') }}" class="small-box-footer">Lihat Pengguna <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    @endif

    <!-- 1. Total Terima Dana BOS -->
    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-3">
        <div class="small-box bg-success">
            <div class="inner">
                @php
                    $danaBos = \App\Models\BkuMasterEntry::where('user_id', auth()->id())
                        ->where('uraian', 'like', '%Dana BOS%')
                        ->sum('penerimaan');
                @endphp
                <h3 class="currency"><span class="prefix">Rp</span><span class="amount">{{ number_format((int)$danaBos, 0, ',', '.') }}</span></h3>
                <p>Total Terima Dana BOS</p>
            </div>
            <div class="icon"><i class="fas fa-school"></i></div>
            <a href="{{ route('bku.umum') }}" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <!-- 2. Saldo Buku Kas Umum Bulan Ini -->
    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 class="currency"><span class="prefix">Rp</span><span class="amount">{{ $latestBku ? number_format($latestBku->saldo, 0, ',', '.') : '0' }}</span></h3>
                <p>Saldo Buku Kas Umum Bulan Ini</p>
            </div>
            <div class="icon"><i class="fas fa-wallet"></i></div>
            <a href="{{ route('bku.umum') }}" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <!-- 3. Saldo Bank (sama dengan BKU Umum) -->
    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-3">
        <div class="small-box bg-primary">
            <div class="inner">
                @php
                    // Saldo bank seharusnya sama dengan BKU Umum (bukan hanya BNU)
                    // Karena BNU = pengeluaran non-tunai, BPU = pengeluaran tunai
                    $saldoBankBenar = \App\Models\BkuMasterEntry::where('user_id', auth()->id())
                        ->latest('tanggal')
                        ->first();
                @endphp
                <h3 class="currency"><span class="prefix">Rp</span><span class="amount">{{ $saldoBankBenar ? number_format($saldoBankBenar->saldo, 0, ',', '.') : '0' }}</span></h3>
                <p>Saldo Bank Terkini</p>
            </div>
            <div class="icon"><i class="fas fa-university"></i></div>
            <a href="{{ route('bku.master') }}" class="small-box-footer">
                @if($saldoBankBenar)
                    Per {{ $saldoBankBenar->tanggal->format('d M Y') }}
                @else
                    Belum ada data
                @endif
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- 4. Total Saldo Kas Tunai -->
    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-3">
        <div class="small-box bg-teal">
            <div class="inner">
                @php
                    // Use running balance (correct method), not total calculation
                    $latestTunaiEntry = \App\Models\TunaiMasterEntry::where('user_id', auth()->id())
                        ->latest('tanggal')
                        ->first();
                    $saldoTunaiActual = $latestTunaiEntry ? $latestTunaiEntry->saldo : 0;
                @endphp
                <h3 class="currency"><span class="prefix">Rp</span><span class="amount">{{ number_format((int)$saldoTunaiActual, 0, ',', '.') }}</span></h3>
                <p>Saldo Kas Tunai Terkini</p>
            </div>
            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            <a href="{{ route('bku.tunai') }}" class="small-box-footer">
                @if($latestTunaiEntry)
                    Per {{ $latestTunaiEntry->tanggal->format('d M Y') }}
                @else
                    Belum ada data
                @endif
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- 5. Transaksi Pajak Bulan Ini -->
    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-3">
        <div class="small-box bg-orange">
            <div class="inner">
                @php
                    // Get tax transactions for current month
                    $taxPenerimaanBulanIni = \App\Models\BkuMasterEntry::where('user_id', auth()->id())
                        ->where(function($query) {
                            $query->where('uraian', 'like', '%PPh%')
                                  ->orWhere('uraian', 'like', '%PPN%')
                                  ->orWhere('uraian', 'like', '%Pajak%');
                        })
                        ->whereMonth('tanggal', now()->month)
                        ->whereYear('tanggal', now()->year)
                        ->sum('penerimaan');
                    $taxPengeluaranBulanIni = \App\Models\BkuMasterEntry::where('user_id', auth()->id())
                        ->where(function($query) {
                            $query->where('uraian', 'like', '%PPh%')
                                  ->orWhere('uraian', 'like', '%PPN%')
                                  ->orWhere('uraian', 'like', '%Pajak%');
                        })
                        ->whereMonth('tanggal', now()->month)
                        ->whereYear('tanggal', now()->year)
                        ->sum('pengeluaran');
                    $taxSaldoBulanIni = $taxPenerimaanBulanIni - $taxPengeluaranBulanIni;
                    
                    // Display the most meaningful value: pajak yang disetor (pengeluaran)
                    // Karena ini menunjukkan volume pajak yang benar-benar dibayar
                    $taxDisplayValue = $taxPengeluaranBulanIni;
                @endphp
                <h3 class="currency"><span class="prefix">Rp</span><span class="amount">{{ number_format((int)$taxDisplayValue, 0, ',', '.') }}</span></h3>
                <p>Pajak Disetor Bulan Ini</p>
            </div>
            <div class="icon"><i class="fas fa-receipt"></i></div>
            <a href="{{ route('bku.pajak') }}" class="small-box-footer"> 
                @if($taxSaldoBulanIni == 0 && ($taxPenerimaanBulanIni > 0 || $taxPengeluaranBulanIni > 0))
                    Saldo Pajak: Rp {{ number_format((int)$taxSaldoBulanIni, 0, ',', '.') }} (Lunas)
                @elseif($taxSaldoBulanIni > 0)
                    Kelebihan: Rp {{ number_format((int)$taxSaldoBulanIni, 0, ',', '.') }}
                @elseif($taxSaldoBulanIni < 0)
                    Hutang: Rp {{ number_format((int)abs($taxSaldoBulanIni), 0, ',', '.') }}
                @else
                    Tidak ada transaksi pajak
                @endif
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- 6. Total Saldo Semua Kas (Tunai + Bank) -->
    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-3">
        <div class="small-box bg-gradient-maroon">
            <div class="inner">
                @php
                    $totalBalance = (int)(($latestBku ? $latestBku->saldo : 0) + ($latestTunai ? $latestTunai->saldo : 0));
                @endphp
                <h3 class="currency"><span class="prefix">Rp</span><span class="amount">{{ number_format($totalBalance, 0, ',', '.') }}</span></h3>
                <p>Total Saldo Semua Kas</p>
            </div>
            <div class="icon"><i class="fas fa-coins"></i></div>
            <span class="small-box-footer">Kas + Tunai</span>
        </div>
    </div>
</div>

<div class="row">
    <!-- 7. Total Transaksi BNU+BPU -->
    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-3">
        <div class="small-box bg-secondary">
            <div class="inner">
                @php
                    $bnuBpuTransactions = \App\Models\BkuMasterEntry::where('user_id', auth()->id())
                        ->where(function($query) {
                            $query->where('no_bukti', 'like', 'BNU%')
                                  ->orWhere('no_bukti', 'like', 'BPU%');
                        })
                        ->count();
                @endphp
                <h3>{{ $bnuBpuTransactions }}</h3>
                <p>Total Transaksi BNU+BPU</p>
            </div>
            <div class="icon"><i class="fas fa-exchange-alt"></i></div>
            <a href="{{ route('bku.master') }}" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <!-- 8. Transaksi Perlu Review -->
    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-3">
        <div class="small-box bg-gradient-cyan">
            <div class="inner">
                @php
                    $currentMonth = \Carbon\Carbon::now()->format('Y-m');
                    $pendingTransactions = \App\Models\BkuMasterEntry::where('user_id', auth()->id())
                        ->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$currentMonth])
                        ->where(function($query) {
                            $query->whereNull('uraian')->orWhere('uraian', '')->orWhere('uraian', 'like', '%pending%');
                        })
                        ->count();
                @endphp
                <h3>{{ $pendingTransactions }}</h3>
                <p>Transaksi Perlu Review</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            <span class="small-box-footer">{{ $pendingTransactions > 0 ? 'Perlu perhatian' : 'Semua lengkap' }}</span>
        </div>
    </div>

    <!-- 9. Total Penerimaan BKU Umum -->
    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 class="currency"><span class="prefix">Rp</span><span class="amount">{{ number_format((int)($bkuStats['monthly_penerimaan'] ?? 0), 0, ',', '.') }}</span></h3>
                <p>Total Penerimaan BKU Umum</p>
            </div>
            <div class="icon"><i class="fas fa-arrow-down"></i></div>
            <span class="small-box-footer">{{ ($bkuStats['monthly_transactions'] ?? 0) }} transaksi bulan ini</span>
        </div>
    </div>

    <!-- 10. Total Pengeluaran BKU Umum -->
    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-3">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3 class="currency"><span class="prefix">Rp</span><span class="amount">{{ number_format((int)($bkuStats['monthly_pengeluaran'] ?? 0), 0, ',', '.') }}</span></h3>
                <p>Total Pengeluaran BKU Umum</p>
            </div>
            <div class="icon"><i class="fas fa-arrow-up"></i></div>
            <span class="small-box-footer">Net: Rp {{ number_format((int)($bkuStats['monthly_net'] ?? 0), 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- 11. Total Penerimaan BKU Tunai -->
    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-3">
        <div class="small-box bg-gradient-lime">
            <div class="inner">
                @php
                    // Get total tunai penerimaan (all time) since monthly might be 0
                    $totalTunaiPenerimaan = \App\Models\TunaiMasterEntry::where('user_id', auth()->id())
                        ->sum('penerimaan');
                @endphp
                <h3 class="currency"><span class="prefix">Rp</span><span class="amount">{{ number_format((int)$totalTunaiPenerimaan, 0, ',', '.') }}</span></h3>
                <p>Total Penerimaan BKU Tunai</p>
            </div>
            <div class="icon"><i class="fas fa-coins"></i></div>
            <a href="{{ route('bku.tunai') }}" class="small-box-footer">{{ ($tunaiStats['yearly_transactions'] ?? 0) }} transaksi total <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <!-- 12. Total Pengeluaran BKU Tunai -->
    <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 col-12 mb-3">
        <div class="small-box bg-gradient-pink">
            <div class="inner">
                @php
                    // Get total tunai pengeluaran (all time) since monthly might be 0
                    $totalTunaiPengeluaran = \App\Models\TunaiMasterEntry::where('user_id', auth()->id())
                        ->sum('pengeluaran');
                    $totalTunaiNet = $totalTunaiPenerimaan - $totalTunaiPengeluaran;
                @endphp
                <h3 class="currency"><span class="prefix">Rp</span><span class="amount">{{ number_format((int)$totalTunaiPengeluaran, 0, ',', '.') }}</span></h3>
                <p>Total Pengeluaran BKU Tunai</p>
            </div>
            <div class="icon"><i class="fas fa-money-bill-alt"></i></div>
            <a href="{{ route('bku.tunai') }}" class="small-box-footer">
                @php
                    $latestTunaiForNet = \App\Models\TunaiMasterEntry::where('user_id', auth()->id())
                        ->latest('tanggal')
                        ->first();
                    $actualNetTunai = $latestTunaiForNet ? $latestTunaiForNet->saldo : 0;
                @endphp
                Saldo Terkini: Rp {{ number_format((int)$actualNetTunai, 0, ',', '.') }} <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Statistik Bulanan</h3>
            </div>
            <div class="card-body">
                @php
                    $currentMonth = now()->format('Y-m');
                    $totalBkuPenerimaan = \App\Models\BkuMasterEntry::where('user_id', auth()->id())
                        ->whereYear('tanggal', now()->year)
                        ->whereMonth('tanggal', now()->month)
                        ->sum('penerimaan');
                    $totalBkuPengeluaran = \App\Models\BkuMasterEntry::where('user_id', auth()->id())
                        ->whereYear('tanggal', now()->year)
                        ->whereMonth('tanggal', now()->month)
                        ->sum('pengeluaran');
                    $netBku = $totalBkuPenerimaan - $totalBkuPengeluaran;
                    
                    $totalTunaiPenerimaan = \App\Models\TunaiMasterEntry::where('user_id', auth()->id())
                        ->whereYear('tanggal', now()->year)
                        ->whereMonth('tanggal', now()->month)
                        ->sum('penerimaan');
                    $totalTunaiPengeluaran = \App\Models\TunaiMasterEntry::where('user_id', auth()->id())
                        ->whereYear('tanggal', now()->year)
                        ->whereMonth('tanggal', now()->month)  
                        ->sum('pengeluaran');
                    $netTunai = $totalTunaiPenerimaan - $totalTunaiPengeluaran;
                    
                    // Get latest available tunai month if current month has no data
                    $latestTunaiMonth = null;
                    $latestTunaiData = ['penerimaan' => 0, 'pengeluaran' => 0, 'net' => 0];
                    if ($totalTunaiPenerimaan == 0 && $totalTunaiPengeluaran == 0) {
                        $latestTunai = \App\Models\TunaiMasterEntry::where('user_id', auth()->id())
                            ->selectRaw('DATE_FORMAT(tanggal, "%Y-%m") as month, SUM(penerimaan) as total_penerimaan, SUM(pengeluaran) as total_pengeluaran')
                            ->groupBy('month')
                            ->orderBy('month', 'desc')
                            ->first();
                        if ($latestTunai) {
                            $latestTunaiMonth = $latestTunai->month;
                            $latestTunaiData = [
                                'penerimaan' => $latestTunai->total_penerimaan,
                                'pengeluaran' => $latestTunai->total_pengeluaran,
                                'net' => $latestTunai->total_penerimaan - $latestTunai->total_pengeluaran
                            ];
                        }
                    }
                @endphp
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>{{ now()->format('F Y') }}</strong>
                        <span class="badge badge-info">{{ $user->name }}</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6 mb-2">
                        <div class="border rounded p-2">
                            <small class="text-muted d-block">BKU Umum</small>
                            <span class="text-success">+{{ number_format($totalBkuPenerimaan, 0, ',', '.') }}</span><br>
                            <span class="text-danger">-{{ number_format($totalBkuPengeluaran, 0, ',', '.') }}</span><br>
                            <strong class="{{ $netBku >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($netBku, 0, ',', '.') }}
                            </strong>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="border rounded p-2">
                            @if($latestTunaiMonth)
                                <small class="text-muted d-block">Kas Tunai ({{ \Carbon\Carbon::createFromFormat('Y-m', $latestTunaiMonth)->format('M Y') }})</small>
                                <span class="text-success">+{{ number_format($latestTunaiData['penerimaan'], 0, ',', '.') }}</span><br>
                                <span class="text-danger">-{{ number_format($latestTunaiData['pengeluaran'], 0, ',', '.') }}</span><br>
                                <strong class="{{ $latestTunaiData['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($latestTunaiData['net'], 0, ',', '.') }}
                                </strong>
                            @else
                                <small class="text-muted d-block">Kas Tunai</small>
                                <span class="text-success">+{{ number_format($totalTunaiPenerimaan, 0, ',', '.') }}</span><br>
                                <span class="text-danger">-{{ number_format($totalTunaiPengeluaran, 0, ',', '.') }}</span><br>
                                <strong class="{{ $netTunai >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($netTunai, 0, ',', '.') }}
                                </strong>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if($school)
                <div class="mt-2 text-center">
                    <small class="text-muted">{{ $school->nama_sekolah ?? 'Nama sekolah belum diisi' }}</small>
                </div>
                @endif
            </div>
        </div>

        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">Menu Utama</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-2">
                        <a href="{{ route('bku.master') }}" class="btn btn-block btn-outline-primary btn-sm">
                            <i class="fas fa-book"></i><br>
                            <small>Buku Kas Umum</small>
                        </a>
                    </div>
                    <div class="col-6 mb-2">
                        <a href="{{ route('bku.tunai') }}" class="btn btn-block btn-outline-info btn-sm">
                            <i class="fas fa-coins"></i><br>
                            <small>Kas Tunai</small>
                        </a>
                    </div>
                    <div class="col-6 mb-2">
                        <a href="{{ route('bku.pajak') }}" class="btn btn-block btn-outline-warning btn-sm">
                            <i class="fas fa-receipt"></i><br>
                            <small>Laporan Pajak</small>
                        </a>
                    </div>
                    <div class="col-6 mb-2">
                        <a href="{{ route('school.index') }}" class="btn btn-block btn-outline-secondary btn-sm">
                            <i class="fas fa-school"></i><br>
                            <small>Data Sekolah</small>
                        </a>
                    </div>
                </div>
                
                <hr class="my-2">
                
                <div class="row">
                    <div class="col-12">
                        <a href="{{ route('profile.index') }}" class="btn btn-outline-dark btn-sm mr-1">
                            <i class="fas fa-user mr-1"></i> Profil
                        </a>
                        <a href="{{ route('logout') }}" class="btn btn-outline-danger btn-sm" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Transaksi Terbaru</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                @if($recentBku && $recentBku->count() > 0)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted mb-0">💰 Transaksi BKU Umum Terbaru</h6>
                        <small class="text-muted">{{ $recentBku->count() }} transaksi</small>
                    </div>
                    @foreach($recentBku as $transaction)
                        <div class="transaction-item" style="border-left: 3px solid #007bff; padding-left: 10px;">
                            <div class="d-flex justify-content-between">
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">{{ Str::limit($transaction->uraian, 60) }}</div>
                                    <div class="transaction-date">
                                        <i class="far fa-calendar-alt mr-1"></i>
                                        {{ $transaction->tanggal ? $transaction->tanggal->format('d M Y') : 'Tanggal tidak tersedia' }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($transaction->penerimaan > 0)
                                        <div class="transaction-amount amount-in">
                                            <i class="fas fa-arrow-up mr-1"></i>+Rp {{ number_format($transaction->penerimaan, 0, ',', '.') }}
                                        </div>
                                    @endif
                                    @if($transaction->pengeluaran > 0)
                                        <div class="transaction-amount amount-out">
                                            <i class="fas fa-arrow-down mr-1"></i>-Rp {{ number_format($transaction->pengeluaran, 0, ',', '.') }}
                                        </div>
                                    @endif
                                    <div class="small text-muted">Saldo: Rp {{ number_format($transaction->saldo, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                @if($recentTunai && $recentTunai->count() > 0)
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted mb-0">💵 Transaksi Kas Tunai Terbaru</h6>
                        <small class="text-muted">{{ $recentTunai->count() }} transaksi</small>
                    </div>
                    @foreach($recentTunai as $transaction)
                        <div class="transaction-item" style="border-left: 3px solid #20c997; padding-left: 10px;">
                            <div class="d-flex justify-content-between">
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">{{ Str::limit($transaction->uraian, 60) }}</div>
                                    <div class="transaction-date">
                                        <i class="far fa-calendar-alt mr-1"></i>
                                        {{ $transaction->tanggal ? $transaction->tanggal->format('d M Y') : 'Tanggal tidak tersedia' }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($transaction->penerimaan > 0)
                                        <div class="transaction-amount amount-in">
                                            <i class="fas fa-arrow-up mr-1"></i>+Rp {{ number_format($transaction->penerimaan, 0, ',', '.') }}
                                        </div>
                                    @endif
                                    @if($transaction->pengeluaran > 0)
                                        <div class="transaction-amount amount-out">
                                            <i class="fas fa-arrow-down mr-1"></i>-Rp {{ number_format($transaction->pengeluaran, 0, ',', '.') }}
                                        </div>
                                    @endif
                                    <div class="small text-muted">Saldo: Rp {{ number_format($transaction->saldo, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                @if((!$recentBku || $recentBku->count() == 0) && (!$recentTunai || $recentTunai->count() == 0))
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                        Belum ada transaksi yang tercatat.<br>
                        <a href="{{ route('bku.master') }}" class="btn btn-sm btn-primary mt-2">Mulai Input Data</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-shrink currency values so they always fit on one line inside their box
    function autoshrinkCurrency() {
        const items = document.querySelectorAll('.small-box h3.currency');
        items.forEach((el) => {
            // Skip if already processed for this layout cycle
            el.style.fontSize = '';
            const amount = el.querySelector('.amount') || el;
            amount.style.fontSize = '';
            const parent = el.parentElement; // .inner
            if (!parent) return;
            const cs = window.getComputedStyle(parent);
            const pr = parseFloat(cs.paddingRight) || 0;
            const pl = parseFloat(cs.paddingLeft) || 0;
            // Reserve extra for icon area (AdminLTE icon size ~60-70px)
            const iconReserve = Math.max(56, pr);
            const maxWidth = Math.max(0, parent.clientWidth - pl - iconReserve);
            // Available width for amount is total minus prefix width and gap
            const prefix = el.querySelector('.prefix');
            let prefixWidth = 0;
            if (prefix) {
                const rect = prefix.getBoundingClientRect();
                prefixWidth = rect.width + 4; // include small gap
            }
            const usable = Math.max(0, maxWidth - prefixWidth);
            let size = parseFloat(window.getComputedStyle(amount).fontSize);
            // Min font-size by breakpoint so desktop stays readable
            const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
            let minSize = 12;
            if (vw >= 1400) minSize = 15;
            else if (vw >= 1200) minSize = 14;
            else if (vw >= 992) minSize = 13;
            let guard = 0;
            // Use fractional decrements for smoother visual result
            while (amount.scrollWidth > usable && size > minSize && guard < 30) {
                size -= 0.6;
                amount.style.fontSize = size + 'px';
                guard++;
            }
        });
    }
    // Run on load and when window resizes
    window.addEventListener('DOMContentLoaded', autoshrinkCurrency);
    window.addEventListener('load', autoshrinkCurrency);
    window.addEventListener('resize', autoshrinkCurrency);
    // Update time every minute using browser time for snappy UX (server timezone already set)
    function updateClock() {
        const el = document.getElementById('current-time');
        if (!el) return;
        const now = new Date();
        const hh = String(now.getHours()).padStart(2, '0');
        const mm = String(now.getMinutes()).padStart(2, '0');
        el.textContent = hh + ':' + mm;
    }
    updateClock();
    setInterval(updateClock, 60 * 1000);

    // Auto-refresh dashboard every 5 minutes for live data
    setTimeout(function() {
        if(document.visibilityState === 'visible') {
            location.reload();
        }
    }, 5 * 60 * 1000);
    // Also re-run autoshrink after dynamic reloads of content
    setTimeout(autoshrinkCurrency, 0);
</script>
@endpush
