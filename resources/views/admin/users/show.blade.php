@extends('adminlte.layouts.admin')

@section('page_title', 'Detail Pengguna')

@section('breadcrumbs')
  <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
  <li class="breadcrumb-item"><a href="{{ route('admin.users') }}">Pengguna</a></li>
  <li class="breadcrumb-item active">Detail</li>
@endsection

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-body d-flex align-items-center flex-wrap">
        <div class="mr-3">
          <span class="btn btn-default btn-lg rounded-circle" style="width:56px;height:56px;display:inline-flex;align-items:center;justify-content:center;font-weight:600;">
            {{ strtoupper(substr($user->name,0,1)) }}
          </span>
        </div>
        <div class="flex-fill">
          <h4 class="mb-1">
            {{ $user->name }}
            @if($user->is_admin)
              <span class="badge badge-primary ml-2"><i class="fas fa-user-shield mr-1"></i> Admin</span>
            @else
              <span class="badge badge-secondary ml-2"><i class="fas fa-user mr-1"></i> User</span>
            @endif
          </h4>
          <div class="text-muted d-flex align-items-center">
            <i class="fas fa-envelope mr-1"></i>
            <span class="text-monospace mr-2">{{ $user->email }}</span>
            @if(method_exists($user, 'hasVerifiedEmail') && ! $user->hasVerifiedEmail())
              <span class="badge badge-warning">Email belum terverifikasi</span>
            @endif
          </div>
        </div>
        
      </div>
      <div class="card-footer">
        <div class="d-flex align-items-center">
          <div class="mr-3">
            <span class="text-muted mr-1">2FA:</span>
            @if($user->two_factor_confirmed_at)
              <span class="badge badge-success">Aktif</span>
            @elseif($user->two_factor_secret)
              <span class="badge badge-info">Menunggu</span>
            @else
              <span class="badge badge-secondary">Nonaktif</span>
            @endif
          </div>
          <div class="text-muted small">
            Dibuat: {{ $user->created_at?->format('Y-m-d H:i') }} · Diperbarui: {{ $user->updated_at?->format('Y-m-d H:i') }}
            @php
              $lastLoginAt = $user->last_login_at ?? null;
              $lastLoginIp = $user->last_login_ip ?? null;
            @endphp
            @if($lastLoginAt || $lastLoginIp)
              · Terakhir login: {{ $lastLoginAt?->format('Y-m-d H:i') ?? '-' }} @if($lastLoginIp) ({{ $lastLoginIp }}) @endif
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3 class="card-title">Ringkasan</h3></div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-3">Nama</dt>
          <dd class="col-sm-9">{{ $user->name }}</dd>

          <dt class="col-sm-3">Email</dt>
          <dd class="col-sm-9"><span class="text-monospace">{{ $user->email }}</span></dd>

          <dt class="col-sm-3">Peran</dt>
          <dd class="col-sm-9">{{ $user->is_admin ? 'Admin' : 'User' }}</dd>

          <dt class="col-sm-3">2FA</dt>
          <dd class="col-sm-9">
            @if($user->two_factor_confirmed_at)
              Aktif sejak {{ $user->two_factor_confirmed_at->format('Y-m-d H:i') }}
            @elseif($user->two_factor_secret)
              Menunggu konfirmasi
            @else
              Nonaktif
            @endif
          </dd>
        </dl>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Aksi Cepat</h3></div>
      <div class="card-body">
        <div class="d-flex flex-wrap">
          @if(method_exists($user, 'hasVerifiedEmail') && ! $user->hasVerifiedEmail())
            <form action="{{ route('admin.users.resend-verification', $user) }}" method="POST" class="d-inline-block mr-2 mb-2" onsubmit="return confirm('Kirim ulang email verifikasi ke {{ $user->email }}?');">
              @csrf
              <button class="btn btn-warning btn-sm" type="submit"><i class="fas fa-paper-plane mr-1"></i> Kirim Ulang Email Verifikasi</button>
            </form>
          @endif
          @if($user->two_factor_secret)
            <form action="{{ route('admin.users.2fa.codes', $user) }}" method="POST" class="d-inline-block mr-2 mb-2" onsubmit="return confirm('Regenerasi recovery codes untuk {{ $user->email }}?');">
              @csrf
              <button class="btn btn-secondary btn-sm" type="submit"><i class="fas fa-sync mr-1"></i> Regenerasi Recovery Codes</button>
            </form>
            <form action="{{ route('admin.users.2fa.reset', $user) }}" method="POST" class="d-inline-block mr-2 mb-2" onsubmit="return confirm('Nonaktifkan 2FA untuk {{ $user->email }}?');">
              @csrf
              <button class="btn btn-danger btn-sm" type="submit"><i class="fas fa-shield-alt mr-1"></i> Nonaktifkan 2FA</button>
            </form>
          @endif
        </div>
      </div>
    </div>
    <div class="mt-2 d-flex flex-wrap">
      <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm mr-2 mb-2"><i class="fas fa-edit mr-1"></i> Edit</a>
      <a href="{{ route('admin.users') }}" class="btn btn-default btn-sm mb-2">Kembali</a>
    </div>
  </div>
</div>
@endsection
