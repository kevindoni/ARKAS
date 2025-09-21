@extends('adminlte.layouts.user')

@section('page_title', 'BKU - Umum')

@section('content')
<div class="card card-outline card-primary">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">BKU - Umum 
      <small class="text-muted">
        ({{ $entries->total() ?? 0 }} entries total, page {{ $entries->currentPage() ?? '?' }} of {{ $entries->lastPage() ?? '?' }})
      </small>
    </h3>
    <form method="GET" class="form-inline">
      <div class="form-group mr-2">
        <label for="month" class="mr-1">Bulan</label>
        <select name="month" id="month" class="form-control form-control-sm">
          <option value="">Semua</option>
          @for($m=1;$m<=12;$m++)
            <option value="{{ $m }}" {{ (int)($month ?? 0) === $m ? 'selected' : '' }}>{{ str_pad($m,2,'0',STR_PAD_LEFT) }}</option>
          @endfor
        </select>
      </div>
      <div class="form-group mr-2">
        <label for="year" class="mr-1">Tahun</label>
        <select name="year" id="year" class="form-control form-control-sm">
          <option value="">Semua</option>
          @php
            // Get available years from database + extend range for future planning
            $dbYears = \App\Models\BkuMasterEntry::where('user_id', auth()->id())
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
            <option value="{{ $y }}" {{ (int)($year ?? 0) === $y ? 'selected' : '' }}>{{ $y }}</option>
          @endforeach
        </select>
      </div>
      <button class="btn btn-sm btn-primary mr-2"><i class="fas fa-filter mr-1"></i> Filter</button>
      <a class="btn btn-sm btn-outline-secondary" target="_blank" href="{{ route('bku.umum.print', ['month'=>$month, 'year'=>$year]) }}"><i class="fas fa-print mr-1"></i> Cetak</a>
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
            // Display values: use actual DB saldo for accuracy instead of calculated running
            // This ensures displayed saldo matches database saldo
            $running = 0; // Not used - we'll use actual DB saldo
          @endphp
          <tbody>
            @if(($month ?? null) && ($year ?? null) && ($showOpeningRow ?? false))
              @php
                $prev = \Carbon\Carbon::createFromDate($year, $month, 1);
                $prevLabel = 'Bulan '.$prev->subMonth()->locale('id')->translatedFormat('F Y');
                $openingBalance = (float)($totals['saldo_awal'] ?? 0) * $displayScale;
              @endphp
              <tr>
                <td>{{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('d-m-Y') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td>Saldo Bank {{ $prevLabel }}</td>
                <td class="text-right">{{ $openingBalance > 0 ? number_format($openingBalance, 0, ',', '.') : '0' }}</td>
                <td class="text-right">0</td>
                @php $running = $openingBalance; @endphp
                <td class="text-right">{{ number_format($running, 0, ',', '.') }}</td>
              </tr>
              <tr>
                <td>{{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('d-m-Y') }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td>Saldo Tunai {{ $prevLabel }}</td>
                <td class="text-right">0</td>
                <td class="text-right">0</td>
                <td class="text-right">{{ number_format($running, 0, ',', '.') }}</td>
              </tr>
            @endif
            @foreach($entries as $e)
              @php
                $in = (float) $e->penerimaan * $displayScale;
                $out = (float) $e->pengeluaran * $displayScale;
                // Use actual DB saldo instead of calculated running
                $displaySaldo = (float) $e->saldo * $displayScale;
              @endphp
              <tr>
                <td>{{ optional($e->tanggal)->format('d-m-Y') }}</td>
                <td>{{ $e->kode_kegiatan }}</td>
                <td>{{ $e->kode_rekening }}</td>
                <td>{{ $e->no_bukti }}</td>
                <td>{{ $e->uraian }}</td>
                <td class="text-right">{{ $in > 0 ? number_format($in, 0, ',', '.') : '0' }}</td>
                <td class="text-right">{{ $out > 0 ? number_format($out, 0, ',', '.') : '0' }}</td>
                <td class="text-right">{{ number_format($displaySaldo, 0, ',', '.') }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            @if(($month ?? null) && ($year ?? null))
              <tr>
                <th colspan="5" class="text-right">Jumlah</th>
                <th class="text-right">{{ number_format(($totals['display_penerimaan'] ?? 0) * $displayScale, 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format(($totals['display_pengeluaran'] ?? 0) * $displayScale, 0, ',', '.') }}</th>
                <th class="text-right">{{ number_format(($totals['saldo_akhir'] ?? 0) * $displayScale, 0, ',', '.') }}</th>
              </tr>
              @php
                $rawPenerimaan = ($totals['raw_penerimaan'] ?? 0) * $displayScale;
                $displayPenerimaan = ($totals['display_penerimaan'] ?? 0) * $displayScale;
                $openingBalance = ($totals['saldo_awal'] ?? 0) * $displayScale;
                $previousMonthName = $month > 1 ? \Carbon\Carbon::createFromDate($year, $month - 1, 1)->format('M') : 'Des ' . ($year - 1);
              @endphp
              <tr>
                <td colspan="8" class="text-muted small">
                  <i class="fas fa-info-circle mr-1"></i>
                  PENERIMAAN (raw): {{ number_format($rawPenerimaan, 0, ',', '.') }} | 
                  PENERIMAAN (display): {{ number_format($displayPenerimaan, 0, ',', '.') }} 
                  (+ {{ $previousMonthName }} opening: {{ number_format($openingBalance, 0, ',', '.') }})
                </td>
              </tr>
            @else
              @if($entries->hasMorePages() == false)
                {{-- Only show grand total on the last page --}}
                @php
                  // Use totals from controller (which already applies proper filters)
                  $grandTotalPenerimaan = ($totals['penerimaan'] ?? 0) * $displayScale;
                  $grandTotalPengeluaran = ($totals['pengeluaran'] ?? 0) * $displayScale;
                  
                  // Get actual final saldo from database 
                  $finalEntry = \App\Models\BkuMasterEntry::where('user_id', auth()->id())
                    ->orderBy('tanggal', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
                  $actualFinalSaldo = $finalEntry ? $finalEntry->saldo * $displayScale : 0;
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
        Belum ada data. Silakan impor dari menu Data Master.
      </div>
    @endif
  </div>
</div>

<div class="card card-outline card-secondary">
  <div class="card-header">
    <h3 class="card-title">Informasi BKU Umum</h3>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-6">
        <h5><i class="fas fa-info-circle text-primary mr-2"></i>Penjelasan BKU Umum</h5>
        <p class="text-muted">
          BKU Umum menampilkan <strong>semua transaksi keuangan</strong> sekolah 
          termasuk transaksi tunai dan non-tunai.
        </p>
        <ul class="text-muted">
          <li>Data diambil dari file Excel yang diupload</li>
          <li>Menampilkan semua jenis transaksi</li>
          <li>Saldo awal diambil dari saldo akhir bulan sebelumnya</li>
          <li>Termasuk transaksi tunai dan transfer</li>
        </ul>
      </div>
      <div class="col-md-6">
        <h5><i class="fas fa-question-circle text-warning mr-2"></i>Kapan Menggunakan BKU Umum?</h5>
        <p class="text-muted">
          Gunakan BKU Umum untuk melihat:
        </p>
        <ul class="text-muted">
          <li>Semua transaksi keuangan sekolah</li>
          <li>Laporan keuangan lengkap</li>
          <li>Saldo berjalan setiap transaksi</li>
          <li>Rekapitulasi penerimaan dan pengeluaran</li>
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection
