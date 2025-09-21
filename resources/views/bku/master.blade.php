@extends('adminlte.layouts.user')

@section('page_title', 'BKU - Data Master')

@push('styles')
<style>
  .table-preview th, .table-preview td { white-space: nowrap; }
  .table-wrapper { overflow:auto; }
  .custom-file-input ~ .custom-file-label::after { content: 'Pilih'; }
</style>
@endpush

@section('content')
@if (session('success'))
<div class="alert alert-success alert-dismissible">
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
  <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
  {{ session('success') }}
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger alert-dismissible">
  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
  <h5><i class="icon fas fa-ban"></i> Error!</h5>
  <ul class="mb-0">
    @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<div class="row">
  <!-- Card untuk BKU Umum -->
  <div class="col-md-6">
    <div class="card card-outline card-secondary">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">BKU Umum - Data Master</h3>
        @if(!empty($fileName))
          <span class="text-muted small">Terakhir diunggah: {{ $fileName }}</span>
        @endif
      </div>
      <div class="card-body">
        <form action="{{ route('bku.master.upload') }}" method="POST" enctype="multipart/form-data" class="mb-3">
          @csrf
          <input type="hidden" name="bku_type" value="umum">
          <div class="form-group">
            <label for="file">Upload Excel BKU Umum (xlsx)</label>
            <div class="custom-file">
              <input type="file" name="file" id="file" class="custom-file-input @error('file') is-invalid @enderror" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
              <label class="custom-file-label" for="file" data-browse="Pilih">Pilih file...</label>
              @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
          </div>
          <button class="btn btn-primary btn-block"><i class="fas fa-upload mr-1"></i> Unggah &amp; Pratinjau</button>
          <small class="text-muted d-block mt-2">Format yang didukung: .xlsx. Kapasitas maksimal 5 MB. Baris pertama dianggap sebagai header kolom.</small>
        </form>

        @if(!empty($headers))
          <form method="POST" action="{{ route('bku.master.import') }}" class="mb-2">
            @csrf
            @if(!empty($importToken))
              <input type="hidden" name="token" value="{{ $importToken }}">
            @endif
            <input type="hidden" name="bku_type" value="umum">
            <div class="form-check form-check-inline mr-2">
              <input class="form-check-input" type="checkbox" name="clear_period" id="clear_period" value="1">
              <label class="form-check-label" for="clear_period">Hapus data bulan yang sama sebelum impor</label>
            </div>
            <button type="submit" class="btn btn-success"><i class="fas fa-database mr-1"></i> Simpan ke Database</button>
          </form>
          <div class="table-wrapper">
            <table class="table table-sm table-bordered table-hover table-preview">
              <thead class="thead-light">
                <tr>
                  @foreach($headers as $h)
                    <th>{{ $h }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @forelse($preview as $row)
                  <tr>
                    @foreach($row as $cell)
                      <td>{{ is_scalar($cell) ? $cell : '' }}</td>
                    @endforeach
                  </tr>
                @empty
                  <tr><td colspan="{{ count($headers) }}" class="text-center text-muted">Tidak ada data untuk pratinjau.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <div class="text-muted small">Menampilkan maksimal 100 baris pertama untuk pratinjau.</div>
        @else
          <p class="text-muted mb-0">Unggah file Excel (.xlsx) untuk BKU Umum dengan kolom seperti TANGGAL, KODE KEGIATAN, KODE REKENING, NO. BUKTI, URAIAN, PENERIMAAN, PENGELUARAN, SALDO.</p>
        @endif
      </div>
    </div>
  </div>

  <!-- Card untuk BKU Tunai -->
  <div class="col-md-6">
    <div class="card card-outline card-info">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">BKU Tunai - Data Master</h3>
        @if(!empty($tunaiFileName))
          <span class="text-muted small">Terakhir diunggah: {{ $tunaiFileName }}</span>
        @endif
      </div>
      <div class="card-body">
        <form action="{{ route('bku.tunai.upload') }}" method="POST" enctype="multipart/form-data" class="mb-3">
          @csrf
          <input type="hidden" name="bku_type" value="tunai">
          <div class="form-group">
            <label for="tunai_file">Upload Excel BKU Tunai (xlsx)</label>
            <div class="custom-file">
              <input type="file" name="file" id="tunai_file" class="custom-file-input @error('file') is-invalid @enderror" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
              <label class="custom-file-label" for="tunai_file" data-browse="Pilih">Pilih file...</label>
              @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
          </div>
          <button class="btn btn-info btn-block"><i class="fas fa-upload mr-1"></i> Unggah &amp; Pratinjau</button>
          <small class="text-muted d-block mt-2">Format yang didukung: .xlsx. Kapasitas maksimal 5 MB. Baris pertama dianggap sebagai header kolom.</small>
        </form>

        @php
          $tunaiHeadersData = $tunaiHeaders ?? session('tunaiHeaders', []);
          $tunaiPreviewData = $tunaiPreview ?? session('tunaiPreview', []);
          $tunaiTokenData = $tunaiImportToken ?? session('tunaiImportToken', '');
          $tunaiFileNameData = $tunaiFileName ?? session('tunaiFileName', '');
        @endphp

        @if(!empty($tunaiHeadersData))
          <form method="POST" action="{{ route('bku.tunai.import') }}" class="mb-2">
            @csrf
            @if(!empty($tunaiTokenData))
              <input type="hidden" name="token" value="{{ $tunaiTokenData }}">
            @endif
            <input type="hidden" name="bku_type" value="tunai">
            <div class="form-check form-check-inline mr-2">
              <input class="form-check-input" type="checkbox" name="clear_period" id="clear_period_tunai" value="1">
              <label class="form-check-label" for="clear_period_tunai">Hapus data bulan yang sama sebelum impor</label>
            </div>
            <button type="submit" class="btn btn-success"><i class="fas fa-database mr-1"></i> Simpan ke Database</button>
          </form>
          <div class="table-wrapper">
            <table class="table table-sm table-bordered table-hover table-preview">
              <thead class="thead-light">
                <tr>
                  @foreach($tunaiHeadersData as $h)
                    <th>{{ $h }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @forelse($tunaiPreviewData as $row)
                  <tr>
                    @foreach($row as $cell)
                      <td>{{ is_scalar($cell) ? $cell : '' }}</td>
                    @endforeach
                  </tr>
                @empty
                  <tr><td colspan="{{ count($tunaiHeadersData) }}" class="text-center text-muted">Tidak ada data untuk pratinjau.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <div class="text-muted small">Menampilkan maksimal 100 baris pertama untuk pratinjau.</div>
        @else
          <p class="text-muted mb-0">Unggah file Excel (.xlsx) untuk BKU Tunai dengan kolom seperti TANGGAL, KODE KEGIATAN, KODE REKENING, NO. BUKTI, URAIAN, PENERIMAAN, PENGELUARAN, SALDO.</p>
        @endif
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function(){
    // File input untuk BKU Umum
    var input = document.getElementById('file');
    if(input){
      input.addEventListener('change', function(e){
        var label = this.nextElementSibling;
        if(label && this.files && this.files.length){
          label.innerText = this.files[0].name;
        }
      });
    }

    // File input untuk BKU Tunai
    var tunaiInput = document.getElementById('tunai_file');
    if(tunaiInput){
      tunaiInput.addEventListener('change', function(e){
        var label = this.nextElementSibling;
        if(label && this.files && this.files.length){
          label.innerText = this.files[0].name;
        }
      });
    }
  });
</script>
@endsection
