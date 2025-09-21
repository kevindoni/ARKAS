@extends('adminlte.layouts.user')

@section('page_title', 'BKU - Bank')

@section('content')
<div class="card card-outline card-primary">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">BKU - Bank 
      <small class="text-muted">
        ({{ $bankEntries->count() }} entries total)
      </small>
    </h3>
    <form method="GET" class="form-inline">
      <div class="form-group mr-2">
        <label for="bulan" class="mr-1">Bulan</label>
        <select name="bulan" id="bulan" class="form-control form-control-sm">
          <option value="">Semua</option>
          @foreach($availableMonths as $monthData)
            <option value="{{ $monthData['value'] }}" {{ (int)$month === $monthData['value'] ? 'selected' : '' }}>
              {{ str_pad($monthData['value'], 2, '0', STR_PAD_LEFT) }} - {{ $monthData['label'] }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="form-group mr-2">
        <label for="tahun" class="mr-1">Tahun</label>
        <select name="tahun" id="tahun" class="form-control form-control-sm">
          <option value="">Semua</option>
          @foreach($availableYears as $y)
            <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>{{ $y }}</option>
          @endforeach
        </select>
      </div>
      <button class="btn btn-sm btn-primary mr-2"><i class="fas fa-filter mr-1"></i> Filter</button>
    </form>
  </div>
  <div class="card-body">
    @if($bankEntries->count() > 0)
      <div class="row mb-3">
        <div class="col-md-3">
          <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Saldo Awal</span>
              <span class="info-box-number">Rp {{ number_format($totals['saldo_awal'], 0, ',', '.') }}</span>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Penerimaan</span>
              <span class="info-box-number">Rp {{ number_format($totals['penerimaan'], 0, ',', '.') }}</span>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Pengeluaran</span>
              <span class="info-box-number">Rp {{ number_format($totals['pengeluaran'], 0, ',', '.') }}</span>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Saldo Akhir</span>
              <span class="info-box-number">Rp {{ number_format($totals['saldo_akhir'], 0, ',', '.') }}</span>
            </div>
          </div>
        </div>
      </div>

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
          <tbody>
            @forelse($bankEntries as $entry)
              <tr>
                <td>{{ \Carbon\Carbon::parse($entry->tanggal)->format('d-m-Y') }}</td>
                <td>{{ $entry->kode_kegiatan }}</td>
                <td>{{ $entry->kode_rekening }}</td>
                <td>{{ $entry->no_bukti }}</td>
                <td>{{ $entry->uraian }}</td>
                <td class="text-right">
                  @if($entry->penerimaan > 0)
                    Rp {{ number_format($entry->penerimaan, 0, ',', '.') }}
                  @elseif(strpos($entry->uraian, 'Saldo Bank Bulan') !== false && $entry->saldo > 0)
                    Rp {{ number_format($entry->saldo, 0, ',', '.') }}
                  @else
                    -
                  @endif
                </td>
                <td class="text-right">
                  @if($entry->pengeluaran > 0)
                    Rp {{ number_format($entry->pengeluaran, 0, ',', '.') }}
                  @else
                    -
                  @endif
                </td>
                <td class="text-right">Rp {{ number_format($entry->saldo, 0, ',', '.') }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center text-muted">
                  <i class="fas fa-info-circle mr-2"></i>
                  Tidak ada transaksi BNU (non-tunai/transfer) untuk bulan {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }}
                </td>
              </tr>
            @endforelse
          </tbody>
          @if($bankEntries->count() > 0)
            <tfoot class="bg-light">
              <tr>
                <th colspan="5" class="text-right">Jumlah:</th>
                <th class="text-right">Rp {{ number_format($totals['penerimaan'], 0, ',', '.') }}</th>
                <th class="text-right">Rp {{ number_format($totals['pengeluaran'], 0, ',', '.') }}</th>
                <th class="text-right">Rp {{ number_format($totals['saldo_akhir'], 0, ',', '.') }}</th>
              </tr>
            </tfoot>
          @endif
        </table>
      </div>
    @else
      <div class="alert alert-info">
        <i class="fas fa-info-circle mr-2"></i>
        @if($month && $year)
          Tidak ada transaksi BNU (non-tunai/transfer) untuk bulan {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }}.
        @else
          Pilih bulan dan tahun untuk melihat data BKU Bank (transaksi non-tunai/transfer dengan kode BNU).
        @endif
      </div>
    @endif
  </div>
</div>

<div class="card card-outline card-secondary">
  <div class="card-header">
    <h3 class="card-title">Informasi BKU Bank</h3>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-6">
        <h5><i class="fas fa-info-circle text-primary mr-2"></i>Penjelasan BKU Bank</h5>
        <p class="text-muted">
          BKU Bank menampilkan semua transaksi <strong>non-tunai</strong> atau <strong>transfer</strong> 
          yang memiliki kode bukti dimulai dengan <strong>"BNU"</strong>.
        </p>
        <ul class="text-muted">
          <li>Data diambil dari BKU Umum</li>
          <li>Hanya menampilkan transaksi dengan kode BNU</li>
          <li>Saldo awal diambil dari saldo akhir bulan sebelumnya</li>
          <li>Transaksi tunai tidak ditampilkan di sini</li>
        </ul>
      </div>
      <div class="col-md-6">
        <h5><i class="fas fa-question-circle text-warning mr-2"></i>Kapan Menggunakan BKU Bank?</h5>
        <p class="text-muted">
          Gunakan BKU Bank untuk melacak transaksi yang dilakukan melalui:
        </p>
        <ul class="text-muted">
          <li>Transfer bank</li>
          <li>Pembayaran non-tunai</li>
          <li>Transaksi digital</li>
          <li>Pembayaran melalui rekening bank</li>
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection