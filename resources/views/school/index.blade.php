@extends('adminlte.layouts.user')

@section('page_title', 'Data Sekolah')

@push('styles')
<style>
  .sticky-actions { position: sticky; bottom: 0; z-index: 5; background: #fff; border-top: 1px solid #dee2e6; }
  .sticky-actions .btn { min-width: 120px; }
  @media (max-width: 575.98px) { .sticky-actions .btn { width: 100%; margin-bottom: .5rem; } }
  .form-section-title { font-size: 1.05rem; font-weight: 600; margin-bottom: .75rem; }
  .help-list { padding-left: 1.1rem; }
  .help-list li { margin-bottom: .25rem; }
  .card h5 { font-size: 1.05rem; font-weight: 600; }
  .card .card-body { padding-top: .75rem; }
  .card .form-group:last-child { margin-bottom: 0; }
  .grid-gap { row-gap: 1rem; }
</style>
@endpush

@section('content')
<div class="row">
  <div class="col-12">
  <form id="school-form" method="POST" action="{{ $school ? route('school.update', $school) : route('school.store') }}">
        @csrf
        @if($school)
          @method('PUT')
        @endif
        <div class="row grid-gap">
          <!-- Card: Data Sekolah -->
          <div class="col-md-12 col-lg-4">
            <div class="card card-outline card-success h-100">
              <div class="card-header"><h5 class="mb-0">Data Sekolah</h5></div>
              <div class="card-body">
                <div class="form-group">
                  <label>Nama Sekolah</label>
                  <input type="text" name="nama_sekolah" class="form-control" value="{{ old('nama_sekolah', $school->nama_sekolah ?? '') }}" required>
                </div>
                <div class="form-group">
                  <label>Status Sekolah</label>
                  <select name="status_sekolah" class="form-control" required>
                    <option value="">- Pilih -</option>
                    <option value="negeri" {{ old('status_sekolah', $school->status_sekolah ?? '')==='negeri' ? 'selected' : '' }}>Negeri</option>
                    <option value="swasta" {{ old('status_sekolah', $school->status_sekolah ?? '')==='swasta' ? 'selected' : '' }}>Swasta</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Alamat</label>
                  <textarea name="alamat_sekolah" class="form-control" rows="2">{{ old('alamat_sekolah', $school->alamat_sekolah ?? '') }}</textarea>
                </div>
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label>NPSN</label>
                    <input type="text" name="npsn" class="form-control" value="{{ old('npsn', $school->npsn ?? '') }}">
                  </div>
                  <div class="form-group col-md-6">
                    <label>Kecamatan</label>
                    <input type="text" name="kecamatan" class="form-control" value="{{ old('kecamatan', $school->kecamatan ?? '') }}">
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label>Kabupaten</label>
                    <input type="text" name="kabupaten" class="form-control" value="{{ old('kabupaten', $school->kabupaten ?? '') }}">
                  </div>
                  <div class="form-group col-md-6">
                    <label>Provinsi</label>
                    <input type="text" name="provinsi" class="form-control" value="{{ old('provinsi', $school->provinsi ?? '') }}">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Card: Data Kepala Sekolah -->
          <div class="col-md-12 col-lg-4">
            <div class="card card-outline card-primary h-100">
              <div class="card-header"><h5 class="mb-0">Data Kepala Sekolah</h5></div>
              <div class="card-body">
                <div class="form-row">
                  <div class="form-group col-sm-6">
                    <label>Nama Kepala Sekolah</label>
                    <input type="text" name="kepala_nama" class="form-control" value="{{ old('kepala_nama', $school->kepala_nama ?? '') }}" required>
                  </div>
                  <div class="form-group col-sm-6">
                    <label>NIP</label>
                    <input type="text" name="kepala_nip" class="form-control" value="{{ old('kepala_nip', $school->kepala_nip ?? '') }}">
                  </div>
                </div>
                <div class="form-group">
                  <label>SK Kepala Sekolah</label>
                  <input type="text" name="kepala_sk" class="form-control" value="{{ old('kepala_sk', $school->kepala_sk ?? '') }}">
                </div>
              </div>
            </div>
          </div>

          <!-- Card: Data Bendahara -->
          <div class="col-md-12 col-lg-4">
            <div class="card card-outline card-warning h-100">
              <div class="card-header"><h5 class="mb-0">Data Bendahara</h5></div>
              <div class="card-body">
                <div class="form-row">
                  <div class="form-group col-sm-6">
                    <label>Nama Bendahara</label>
                    <input type="text" name="bendahara_nama" class="form-control" value="{{ old('bendahara_nama', $school->bendahara_nama ?? '') }}" required>
                  </div>
                  <div class="form-group col-sm-6">
                    <label>NIP</label>
                    <input type="text" name="bendahara_nip" class="form-control" value="{{ old('bendahara_nip', $school->bendahara_nip ?? '') }}">
                  </div>
                </div>
                <div class="form-group">
                  <label>SK Bendahara</label>
                  <input type="text" name="bendahara_sk" class="form-control" value="{{ old('bendahara_sk', $school->bendahara_sk ?? '') }}">
                </div>
              </div>
            </div>
          </div>
        </div>

    </form>

    <div class="sticky-actions d-flex flex-wrap justify-content-between align-items-center p-3 mt-2">
      <div class="mb-2 mb-sm-0">
        <button type="submit" class="btn btn-primary" form="school-form"><i class="fas fa-save mr-1"></i> Simpan</button>
      </div>
      @if($school)
        <form method="POST" action="{{ route('school.destroy', $school) }}" onsubmit="return confirm('Yakin hapus data sekolah?');" class="mb-0">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger"><i class="fas fa-trash mr-1"></i> Hapus</button>
        </form>
      @endif
    </div>
  </div>
</div>

<div class="row mt-3">
  <div class="col-12">
    <div class="p-3 border rounded bg-white">
      <h5 class="mb-2">Petunjuk</h5>
      <ul class="help-list mb-0">
        <li>Setiap user hanya memiliki 1 data sekolah.</li>
        <li>Status sekolah hanya “negeri” atau “swasta”.</li>
        <li>Isian NIP dan SK opsional.</li>
        <li>Gunakan ketiga kartu di atas untuk mengisi data sesuai bagiannya.</li>
      </ul>
    </div>
  </div>
</div>

@push('scripts')
@endpush
@endsection
