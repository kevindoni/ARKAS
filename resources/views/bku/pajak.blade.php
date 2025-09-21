@extends('adminlte.layouts.user')

@section('page_title', 'BKU - Pajak')

@section('content')
<div class="card card-outline card-warning">
  <div class="card-header">
    <h3 class="card-title">BKU - Pajak</h3>
    <div class="card-tools">
      <span class="badge badge-info">{{ $pajakEntries->count() }} entries total</span>
    </div>
  </div>
  <div class="card-body">
    <!-- Filter Section -->
    <div class="row mb-3">
      <div class="col-md-3">
        <div class="form-group">
          <label for="bulan">Bulan</label>
          <select name="bulan" id="bulan" class="form-control">
            <option value="">Semua</option>
            @foreach($availableMonths as $monthOption)
              <option value="{{ $monthOption['value'] }}" {{ $month == $monthOption['value'] ? 'selected' : '' }}>
                {{ $monthOption['label'] }}
              </option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label for="tahun">Tahun</label>
          <select name="tahun" id="tahun" class="form-control">
            <option value="">Semua</option>
            @foreach($availableYears as $yearOption)
              <option value="{{ $yearOption }}" {{ $year == $yearOption ? 'selected' : '' }}>
                {{ $yearOption }}
              </option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          <label>&nbsp;</label>
          <div>
            <button type="button" class="btn btn-primary" onclick="applyFilter()">
              <i class="fas fa-filter"></i> Filter
            </button>
            <a href="{{ route('bku.pajak') }}" class="btn btn-secondary">
              <i class="fas fa-times"></i> Reset
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Summary Info Boxes -->
    @if(isset($pajakEntries) && $pajakEntries->count() > 0)
    <div class="row mb-4">
      <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
          <div class="inner">
            <h3>Rp {{ number_format($totals['saldo_awal'], 0, ',', '.') }}</h3>
            <p>Saldo Awal</p>
          </div>
          <div class="icon">
            <i class="fas fa-wallet"></i>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
          <div class="inner">
            <h3>Rp {{ number_format($totals['penerimaan'], 0, ',', '.') }}</h3>
            <p>Penerimaan Pajak</p>
          </div>
          <div class="icon">
            <i class="fas fa-arrow-up"></i>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
          <div class="inner">
            <h3>Rp {{ number_format($totals['pengeluaran'], 0, ',', '.') }}</h3>
            <p>Pengeluaran Pajak</p>
          </div>
          <div class="icon">
            <i class="fas fa-arrow-down"></i>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3>Rp {{ number_format($totals['saldo_akhir'], 0, ',', '.') }}</h3>
            <p>Saldo Akhir</p>
          </div>
          <div class="icon">
            <i class="fas fa-calculator"></i>
          </div>
        </div>
      </div>
    </div>
    @endif

    <!-- Data Table -->
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover">
        <thead class="thead-dark">
          <tr>
            <th style="width: 100px;">Tanggal</th>
            <th style="width: 120px;">Kode Kegiatan</th>
            <th>Uraian</th>
            <th style="width: 140px;" class="text-right">Penerimaan PPN</th>
            <th style="width: 140px;" class="text-right">Pengeluaran PPN</th>
            <th style="width: 140px;" class="text-right">Saldo</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pajakEntries as $entry)
            <tr>
              <td class="text-center">{{ \Carbon\Carbon::parse($entry->tanggal)->format('d-m-Y') }}</td>
              <td class="text-center">{{ $entry->kode_kegiatan }}</td>
              <td>
                <div style="max-width: 400px; word-wrap: break-word;">
                  {{ $entry->uraian }}
                </div>
              </td>
              <td class="text-right">
                @if($entry->penerimaan > 0)
                  <span class="text-success font-weight-bold">
                    Rp {{ number_format($entry->penerimaan, 0, ',', '.') }}
                  </span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td class="text-right">
                @if($entry->pengeluaran > 0)
                  <span class="text-danger font-weight-bold">
                    Rp {{ number_format($entry->pengeluaran, 0, ',', '.') }}
                  </span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td class="text-right">
                <span class="font-weight-bold">
                  Rp {{ number_format($entry->pajak_saldo ?? 0, 0, ',', '.') }}
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">
                <i class="fas fa-info-circle mr-2"></i>
                @if($month && $year)
                  Tidak ada data pajak untuk {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}
                @else
                  Tidak ada data pajak
                @endif
              </td>
            </tr>
          @endforelse
        </tbody>
        @if(isset($pajakEntries) && $pajakEntries->count() > 0)
        <tfoot class="bg-light">
          <tr class="font-weight-bold">
            <td colspan="3" class="text-center">
              <strong>Jumlah:</strong>
            </td>
            <td class="text-right">
              <span class="text-success">
                Rp {{ number_format($totals['penerimaan'], 0, ',', '.') }}
              </span>
            </td>
            <td class="text-right">
              <span class="text-danger">
                Rp {{ number_format($totals['pengeluaran'], 0, ',', '.') }}
              </span>
            </td>
            <td class="text-right">
              <span class="text-primary">
                Rp {{ number_format($totals['saldo_akhir'], 0, ',', '.') }}
              </span>
            </td>
          </tr>
        </tfoot>
        @endif
      </table>
    </div>

    <!-- Information Card -->
    @if(isset($pajakEntries) && $pajakEntries->count() > 0)
    <div class="card mt-4">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="fas fa-info-circle mr-2"></i>
          Informasi BKU Pajak
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h6>Penjelasan BKU Pajak</h6>
            <ul class="mb-0">
              <li>Menampilkan transaksi pajak (PPN, PPh, Pajak Bunga)</li>
              <li>Data diambil dari BKU Umum</li>
              <li>Transaksi "Terima" = Penerimaan PPN</li>
              <li>Transaksi "Setor" = Pengeluaran PPN</li>
              <li>Saldo menunjukkan akumulasi pajak</li>
            </ul>
          </div>
          <div class="col-md-6">
            <h6>Kapan Menggunakan BKU Pajak?</h6>
            <ul class="mb-0">
              <li>Pembayaran PPN dari transaksi SIPLah</li>
              <li>Setor PPN ke kas negara</li>
              <li>Pembayaran PPh 21, PPh 23, PPh 4</li>
              <li>Pajak bunga bank</li>
              <li>Monitoring kewajiban pajak sekolah</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    @endif
  </div>
</div>

<script>
function applyFilter() {
  const bulan = document.getElementById('bulan').value;
  const tahun = document.getElementById('tahun').value;
  
  let url = '{{ route("bku.pajak") }}';
  const params = new URLSearchParams();
  
  if (bulan) params.append('bulan', bulan);
  if (tahun) params.append('tahun', tahun);
  
  if (params.toString()) {
    url += '?' + params.toString();
  }
  
  window.location.href = url;
}
</script>
@endsection
