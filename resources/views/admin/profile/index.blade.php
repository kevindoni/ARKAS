@extends('adminlte.layouts.admin')

@section('page_title', 'Profil Admin')

@section('breadcrumbs')
  <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
  <li class="breadcrumb-item active">Profil Admin</li>
@endsection

@section('content')
  <div class="row">
    <div class="col-md-8">
      @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
      @endif
      <div class="card">
        <div class="card-header p-2 d-flex align-items-center justify-content-between">
          <ul class="nav nav-pills" id="admin-profile-tabs">
            <li class="nav-item"><a class="nav-link active" href="#tab-info" data-toggle="tab"><i class="fas fa-id-badge mr-1"></i> Info</a></li>
            <li class="nav-item"><a class="nav-link" href="#tab-password" data-toggle="tab"><i class="fas fa-key mr-1"></i> Ubah Password</a></li>
          </ul>
          <div class="ml-auto pr-2">
            <span class="badge badge-primary">Admin</span>
            @if(!auth()->user()->email_verified_at)
              <span class="badge badge-warning">Email belum verifikasi</span>
            @else
              <span class="badge badge-success">Email terverifikasi</span>
            @endif
            @if(auth()->user()->two_factor_confirmed_at)
              <span class="badge badge-success">2FA aktif</span>
            @elseif(auth()->user()->two_factor_secret)
              <span class="badge badge-info">2FA menunggu konfirmasi</span>
            @else
              <span class="badge badge-secondary">2FA nonaktif</span>
            @endif
          </div>
        </div>
        <div class="card-body">
          <div class="tab-content">
            <div class="active tab-pane" id="tab-info">
              <form action="{{ route('admin.profile.updateInfo') }}" method="POST">
                @csrf
                <div class="form-group">
                  <label for="name">Nama</label>
                  <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                  @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                  @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                  <small class="form-text text-muted">Mengubah email akan meminta verifikasi ulang.</small>
                </div>
                <div class="text-right">
                  <button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
                </div>
              </form>
              <hr>
              <dl class="row mb-0">
                <dt class="col-sm-4">Dibuat</dt>
                <dd class="col-sm-8">{{ auth()->user()->created_at->format('d M Y H:i') }}</dd>
                <dt class="col-sm-4">Terakhir Update</dt>
                <dd class="col-sm-8">{{ auth()->user()->updated_at->format('d M Y H:i') }}</dd>
              </dl>
            </div>
            <div class="tab-pane" id="tab-password">
              <form action="{{ route('admin.profile.updatePassword') }}" method="POST">
                @csrf
                <div class="form-group">
                  <label for="current_password">Password Saat Ini</label>
                  <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                  @error('current_password')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="password">Password Baru</label>
                  <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                  @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="password_confirmation">Konfirmasi Password Baru</label>
                  <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                </div>
                <div class="text-right">
                  <button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Password</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="card-header"><i class="fas fa-bolt mr-1"></i> Tindakan Cepat</div>
        <div class="card-body">
          <a href="{{ route('admin.settings') }}" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-cogs mr-1"></i> Pengaturan</a>
          <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-primary mb-2"><i class="fas fa-home mr-1"></i> Kembali ke Situs</a>
          <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-info mb-2"><i class="fas fa-users mr-1"></i> Pengguna</a>
        </div>
      </div>
    </div>
  </div>

@push('scripts')
<script>
  // Activate tab from hash or on error fields (keep UX consistent)
  $(function(){
    const hash = window.location.hash;
    if (hash) {
      const $link = $("a[href='"+hash+"']");
      if ($link.length) { $link.tab('show'); }
    }
    // If there are password-related errors, switch to password tab
    if ($('.is-invalid#current_password, .is-invalid#password').length) {
      $("a[href='#tab-password']").tab('show');
    }
  });
</script>
@endpush
@endsection
