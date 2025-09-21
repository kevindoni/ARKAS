@extends('adminlte.layouts.admin')

@section('page_title', 'Edit Pengguna')

@section('breadcrumbs')
  <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
  <li class="breadcrumb-item"><a href="{{ route('admin.users') }}">Pengguna</a></li>
  <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Edit: {{ $user->name }}</h3>
      </div>
      <div class="card-body">
        @if(session('status'))
          <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="form-group">
            <label for="name">Nama</label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="is_admin" name="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
              <label class="custom-control-label" for="is_admin">Jadikan Admin</label>
            </div>
            @error('is_admin')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
          </div>
          <div class="d-flex justify-content-between">
            <div>
              <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
              <a href="{{ route('admin.users.show', $user) }}" class="btn btn-default">Lihat</a>
              <a href="{{ route('admin.users') }}" class="btn btn-default">Kembali</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Info Pengguna</h3></div>
      <div class="card-body">
        <div class="d-flex align-items-center mb-3">
          <span class="btn btn-default btn-lg rounded-circle mr-3" style="width:48px;height:48px;display:inline-flex;align-items:center;justify-content:center;font-weight:600;">{{ strtoupper(substr($user->name,0,1)) }}</span>
          <div>
            <div class="font-weight-bold">{{ $user->name }}</div>
            <div class="text-muted small"><i class="fas fa-envelope mr-1"></i> <span class="text-monospace">{{ $user->email }}</span></div>
          </div>
        </div>
        <div class="mb-2">
          <span class="text-muted mr-1">Peran:</span>
          @if($user->is_admin)
            <span class="badge badge-primary">Admin</span>
          @else
            <span class="badge badge-secondary">User</span>
          @endif
        </div>
        <div>
          <span class="text-muted mr-1">2FA:</span>
          @if($user->two_factor_confirmed_at)
            <span class="badge badge-success">Aktif</span>
          @elseif($user->two_factor_secret)
            <span class="badge badge-info">Menunggu</span>
          @else
            <span class="badge badge-secondary">Nonaktif</span>
          @endif
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3 class="card-title text-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Zona Berbahaya</h3></div>
      <div class="card-body">
        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini? Tindakan tidak dapat dibatalkan.');">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger btn-block"><i class="fas fa-trash mr-1"></i> Hapus Pengguna</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
