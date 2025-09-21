@extends('adminlte.layouts.user')

@section('page_title', 'BKU - Tunai')

@section('content')
<div class="card card-outline card-success">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">BKU - Tunai</h3>
    <form method="GET" class="form-inline">
      <div class="form-group mr-2">
        <label for="bulan" class="mr-1">Bulan</label>
        <select name="bulan" id="bulan" class="form-control form-control-sm">
          <option value="">Semua</option>
          @for($m=1;$m<=12;$m++)
            <option value="{{ $m }}" {{ (int)request('bulan', 0) === $m ? 'selected' : '' }}>{{ str_pad($m,2,'0',STR_PAD_LEFT) }}</option>
          @endfor
        </select>
      </div>
      <div class="form-group mr-2">
        <label for="tahun" class="mr-1">Tahun</label>
        <select name="tahun" id="tahun" class="form-control form-control-sm">
          <option value="">Semua</option>
          @php
            // Get available years from tunai_master_entries + extend range
            $dbYears = \App\Models\TunaiMasterEntry::where('user_id', auth()->id())
                ->selectRaw('DISTINCT YEAR(tanggal) as year')
                ->whereNotNull('tanggal')
                ->pluck('year')->toArray();
            
            // Add current year and next year for future planning
            $currentYear = now()->year;
            $allYears = array_unique(array_merge($dbYears, [$currentYear, $currentYear + 1]));
            sort($allYears);
            $allYears = array_reverse($allYears); // Newest first
          @endphp
          @foreach($allYears as $y)
            <option value="{{ $y }}" {{ (int)request('tahun', 0) === $y ? 'selected' : '' }}>{{ $y }}</option>
          @endforeach
        </select>
      </div>
      <button class="btn btn-sm btn-success mr-2"><i class="fas fa-filter mr-1"></i> Filter</button>
    </form>
  </div>
  <div class="card-body">
    @if(isset($entries) && $entries->count() > 0)
      <div class="row mb-3">
        <div class="col-md-3">
          <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Saldo Awal</span>
              <span class="info-box-number">Rp {{ number_format($totals['saldo_awal'] ?? 0, 0, ',', '.') }}</span>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Penerimaan</span>
              <span class="info-box-number">Rp {{ number_format($totals['penerimaan'] ?? 0, 0, ',', '.') }}</span>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Pengeluaran</span>
              <span class="info-box-number">Rp {{ number_format($totals['pengeluaran'] ?? 0, 0, ',', '.') }}</span>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Saldo Akhir</span>
              <span class="info-box-number">Rp {{ number_format($totals['saldo_akhir'] ?? 0, 0, ',', '.') }}</span>
            </div>
          </div>
        </div>
      </div>
    @endif

    @if(isset($entries) && $entries->count())
      <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm">
          <thead class="thead-dark">
            <tr>
              <th style="width: 120px;">Tanggal</th>
              <th>Kode Kegiatan</th>
              <th>Kode Rekening</th>
              <th>No. Bukti</th>
              <th>Uraian</th>
              <th class="text-right">Penerimaan</th>
              <th class="text-right">Pengeluaran</th>
              <th class="text-right">Saldo</th>
            </tr>
          </thead>
          @php
            // For tunai, we don't need running calculation since saldo is stored in DB
            // Just use the saldo field directly from each entry
          @endphp
          <tbody>
            @foreach($entries as $e)
              @php
                $in = (float) $e->penerimaan;
                $out = (float) $e->pengeluaran;
                // Use actual saldo from database instead of calculating running total
                $saldo = (float) $e->saldo;
              @endphp
              <tr>
                <td>{{ optional($e->tanggal)->format('d-m-Y') }}</td>
                <td>{{ $e->kode_kegiatan }}</td>
                <td>{{ $e->kode_rekening }}</td>
                <td>{{ $e->no_bukti }}</td>
                <td>{{ $e->uraian }}</td>
                <td class="text-right">{{ $in > 0 ? number_format($in, 0, ',', '.') : '0' }}</td>
                <td class="text-right">{{ $out > 0 ? number_format($out, 0, ',', '.') : '0' }}</td>
                <td class="text-right">{{ number_format($saldo, 0, ',', '.') }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            @if(request('bulan') && request('tahun'))
              <tr>
                <th colspan="5" class="text-right">Jumlah</th>
                <th class="text-right">{{ number_format($totals['penerimaan'] ?? 0, 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format($totals['pengeluaran'] ?? 0, 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format($totals['saldo_akhir'] ?? 0, 0, ',', '.') }}</th>
              </tr>
              @php
                $rawPenerimaan = ($totals['penerimaan'] ?? 0);
                $rawPengeluaran = ($totals['pengeluaran'] ?? 0);
                $openingBalance = ($totals['saldo_awal'] ?? 0);
                $previousMonthName = request('bulan') > 1 ? \Carbon\Carbon::createFromDate(request('tahun'), request('bulan') - 1, 1)->format('M') : 'Des ' . (request('tahun') - 1);
              @endphp
              <tr>
                <td colspan="8" class="text-muted small">
                  <i class="fas fa-info-circle mr-1"></i>
                  PENERIMAAN: {{ number_format($rawPenerimaan, 0, ',', '.') }} | 
                  PENGELUARAN: {{ number_format($rawPengeluaran, 0, ',', '.') }} 
                  (Opening {{ $previousMonthName }}: {{ number_format($openingBalance, 0, ',', '.') }})
                </td>
              </tr>
            @else
              @if($entries->hasMorePages() == false)
                {{-- Only show grand total on the last page --}}
                @php
                  $grandTotalPenerimaan = ($totals['penerimaan'] ?? 0);
                  $grandTotalPengeluaran = ($totals['pengeluaran'] ?? 0);
                  $grandTotalSaldo = ($totals['saldo'] ?? 0);
                  
                  // Get actual final saldo from database (not calculated net change)
                  $finalEntry = \App\Models\TunaiMasterEntry::where('user_id', auth()->id())
                    ->orderBy('tanggal', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
                  $actualFinalSaldo = $finalEntry ? $finalEntry->saldo : 0;
                @endphp
                <tr>
                  <th colspan="5" class="text-right">Grand Total</th>
                  <th class="text-right">{{ number_format($grandTotalPenerimaan, 0, ',', '.') }}</th>
                  <th class="text-right">{{ number_format($grandTotalPengeluaran, 0, ',', '.') }}</th>
                  <th class="text-right">{{ number_format($actualFinalSaldo, 0, ',', '.') }}</th>
                </tr>
                <tr>
                  <td colspan="8" class="text-muted small">
                    <i class="fas fa-info-circle mr-1"></i>
                    Total {{ $entries->total() }} entries | 
                    Net Change: {{ number_format($grandTotalPenerimaan - $grandTotalPengeluaran, 0, ',', '.') }} | 
                    Final Saldo: {{ number_format($actualFinalSaldo, 0, ',', '.') }}
                  </td>
                </tr>
              @else
                {{-- On pages with more pages, show pagination info only --}}
                <tr>
                  <td colspan="8" class="text-muted small text-center">
                    <i class="fas fa-arrow-right mr-1"></i>
                    Menampilkan {{ $entries->count() }} dari {{ $entries->total() }} entries. 
                    Total akan ditampilkan di halaman terakhir.
                  </td>
                </tr>
              @endif
            @endif
          </tfoot>
        </table>
      </div>
      <div>
        {{ $entries->links() }}
      </div>
    @else
      <div class="alert alert-info">
        <i class="fas fa-info-circle mr-2"></i>
        Belum ada data tunai. Silakan impor dari menu <a href="{{ route('bku.master') }}">Data Master</a>.
      </div>
    @endif
  </div>
</div>

<div class="card card-outline card-secondary">
  <div class="card-header">
    <h3 class="card-title">Informasi BKU Tunai</h3>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-6">
        <h5><i class="fas fa-info-circle text-primary mr-2"></i>Penjelasan BKU Tunai</h5>
        <p class="text-muted">
          BKU Tunai menampilkan <strong>transaksi tunai</strong> sekolah 
          yang dilakukan secara langsung dengan uang fisik.
        </p>
        <ul class="text-muted">
          <li>Data diambil dari file Excel yang diupload</li>
          <li>Menampilkan transaksi tunai saja</li>
          <li>Saldo awal diambil dari saldo akhir bulan sebelumnya</li>
          <li>Tidak termasuk transaksi transfer/bank</li>
        </ul>
      </div>
      <div class="col-md-6">
        <h5><i class="fas fa-question-circle text-warning mr-2"></i>Kapan Menggunakan BKU Tunai?</h5>
        <p class="text-muted">
          Gunakan BKU Tunai untuk melacak transaksi yang dilakukan dengan:
        </p>
        <ul class="text-muted">
          <li>Uang tunai langsung</li>
          <li>Pembayaran fisik</li>
          <li>Transaksi harian sekolah</li>
          <li>Pengeluaran operasional tunai</li>
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection
