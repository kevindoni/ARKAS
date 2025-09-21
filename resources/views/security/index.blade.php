@extends('adminlte.layouts.user')

@section('page_title', 'Keamanan')

@section('breadcrumbs')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
  <li class="breadcrumb-item active">Keamanan</li>
@endsection

@section('content')
@php $enabled = ! is_null(auth()->user()->two_factor_secret); @endphp
<div class="row">
  <div class="col-md-4">
    <div class="card card-outline card-primary">
      <div class="card-header"><h3 class="card-title">Akun</h3></div>
      <div class="card-body">
        <div class="d-flex align-items-center">
          <img class="img-circle elevation-2 mr-3" src="{{ asset('assets/dist/img/user2-160x160.jpg') }}" alt="Avatar" width="64" height="64">
          <div>
            <div class="font-weight-bold">{{ auth()->user()->name }}</div>
            <div class="text-muted small">{{ auth()->user()->email }}</div>
            <div class="mt-2">
              <span class="badge {{ auth()->user()->is_admin ? 'badge-primary' : 'badge-secondary' }}">
                <i class="fas {{ auth()->user()->is_admin ? 'fa-user-shield' : 'fa-user' }} mr-1"></i>
                {{ auth()->user()->is_admin ? 'Admin' : 'User' }}
              </span>
              @if($enabled)
                <span class="badge badge-success ml-1"><i class="fas fa-lock mr-1"></i> 2FA Aktif</span>
              @else
                <span class="badge badge-warning ml-1"><i class="fas fa-unlock mr-1"></i> 2FA Nonaktif</span>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    @if(session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <div class="card card-outline card-secondary mb-3">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title mb-0">Autentikasi Dua-Faktor (2FA)</h3>
      </div>
      <div class="card-body">
        @if($enabled)
          <div class="alert alert-success">2FA saat ini <strong>Aktif</strong>.</div>
          <p class="text-muted mb-3">Pindai QR dengan aplikasi autentikator atau input manual menggunakan otpauth URI di bawah.</p>
          <div class="mb-3">
            <div id="qr-container" class="p-3 border rounded bg-white"></div>
          </div>
          <div class="mb-3">
            <label class="d-block font-weight-bold small">otpauth URI</label>
            <div class="input-group input-group-sm">
              <input type="password" class="form-control" id="otpauth-input" value="" readonly>
              <div class="input-group-append">
                <button class="btn btn-outline-secondary" id="toggle-otpauth" title="Tampilkan/Sembunyikan"><i class="fas fa-eye"></i></button>
                <button class="btn btn-outline-secondary" id="copy-otpauth" title="Salin"><i class="fas fa-copy"></i></button>
              </div>
            </div>
          </div>
          <form method="POST" action="{{ url('/user/two-factor-authentication') }}" class="d-inline-block" onsubmit="return confirm('Nonaktifkan 2FA?');">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger"><i class="fas fa-shield-alt mr-1"></i> Nonaktifkan 2FA</button>
          </form>
        @else
          <div class="alert alert-warning">2FA saat ini <strong>Nonaktif</strong>.</div>
          <p class="text-muted">Aktifkan 2FA untuk meningkatkan keamanan akun Anda. Setelah aktif, Anda akan mendapatkan QR Code untuk dipindai di aplikasi autentikator.</p>
          <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
            @csrf
            <button class="btn btn-primary"><i class="fas fa-shield-alt mr-1"></i> Aktifkan 2FA</button>
          </form>
        @endif
      </div>
    </div>

    <div class="card card-outline card-secondary">
      <div class="card-header"><h3 class="card-title">Recovery Codes</h3></div>
      <div class="card-body">
        @if($enabled)
          <div class="mb-2 d-flex align-items-center flex-wrap">
            <button id="copy-codes" class="btn btn-xs btn-outline-secondary mr-2 mb-2" data-toggle="tooltip" title="Simpan codes ini di tempat yang aman (password manager atau catatan terenkripsi)."><i class="fas fa-copy mr-1"></i> Salin semua</button>
            <form method="POST" action="{{ url('/user/two-factor-recovery-codes') }}" class="d-inline-block mb-2">
              @csrf
              <button class="btn btn-xs btn-outline-secondary"><i class="fas fa-sync mr-1"></i> Regenerasi</button>
            </form>
          </div>
          <div id="codes-container" class="p-3 border rounded bg-white"></div>
        @else
          <p class="text-muted mb-0">Aktifkan 2FA untuk mendapatkan recovery codes.</p>
        @endif
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    let codesCache = [];
    window.afterPasswordConfirmed2FA = null;

    async function loadQR() {
      const box = document.getElementById('qr-container');
      if (!box) return;
      box.innerHTML = 'Memuat QR...';
      try {
        const res = await fetch("{{ url('/user/two-factor-qr-code') }}", { headers: { 'Accept': 'application/json' } });
        if (res.status === 423) {
          box.innerHTML = '<span class="text-warning">QR terkunci. Konfirmasi password diperlukan.</span>';
          window.afterPasswordConfirmed2FA = () => { loadQR(); };
          $('#confirmPasswordModal').modal('show');
          return;
        }
        const data = await res.json();
        box.innerHTML = data.svg ?? 'QR tidak tersedia.';
      } catch (e) {
        box.innerHTML = 'Gagal memuat QR.';
      }
    }

    async function loadCodes() {
      const box = document.getElementById('codes-container');
      if (!box) return;
      box.innerHTML = 'Memuat codes...';
      try {
        const res = await fetch("{{ url('/user/two-factor-recovery-codes') }}", { headers: { 'Accept': 'application/json' } });
        if (res.status === 423) {
          box.innerHTML = '<span class="text-warning">Recovery codes terkunci. Konfirmasi password diperlukan.</span>';
          window.afterPasswordConfirmed2FA = () => { loadCodes(); };
          $('#confirmPasswordModal').modal('show');
          return;
        }
        const data = await res.json();
        codesCache = Array.isArray(data) ? data : [];
        let html = '<ol class="mb-0">';
        codesCache.forEach(c => html += `<li><code>${c}</code></li>`);
        html += '</ol>';
        box.innerHTML = html;
      } catch (e) {
        box.innerHTML = 'Gagal memuat recovery codes.';
      }
    }

    async function loadOtpauth() {
      const input = document.getElementById('otpauth-input');
      if (!input) return;
      input.value = 'Memuat...';
      try {
        const res = await fetch("{{ route('security.otpauth') }}", { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        input.value = data.uri || '';
      } catch (e) {
        input.value = '';
      }
    }

    const toggleBtn = document.getElementById('toggle-otpauth');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const input = document.getElementById('otpauth-input');
        const isPassword = input.getAttribute('type') === 'password';
        input.setAttribute('type', isPassword ? 'text' : 'password');
        toggleBtn.innerHTML = isPassword ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
      });
    }

    const copyOtp = document.getElementById('copy-otpauth');
    if (copyOtp) {
      copyOtp.addEventListener('click', async function(e) {
        e.preventDefault();
        const input = document.getElementById('otpauth-input');
        try {
          await navigator.clipboard.writeText(input.value);
          copyOtp.classList.remove('btn-outline-secondary');
          copyOtp.classList.add('btn-success');
          setTimeout(() => { copyOtp.classList.add('btn-outline-secondary'); copyOtp.classList.remove('btn-success'); }, 1500);
        } catch (err) {
          alert('Gagal menyalin.');
        }
      });
    }

    const copyBtn = document.getElementById('copy-codes');
    if (copyBtn) {
      copyBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        if (!codesCache.length) { await loadCodes(); }
        try {
          await navigator.clipboard.writeText(codesCache.join('\n'));
          copyBtn.classList.remove('btn-outline-secondary');
          copyBtn.classList.add('btn-success');
          copyBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Disalin';
          setTimeout(() => {
            copyBtn.classList.add('btn-outline-secondary');
            copyBtn.classList.remove('btn-success');
            copyBtn.innerHTML = '<i class="fas fa-copy mr-1"></i> Salin semua';
          }, 1500);
        } catch (err) {
          alert('Gagal menyalin.');
        }
      });
    }

    // Auto-load when enabled
    @if($enabled)
      loadQR();
      loadCodes();
      loadOtpauth();
      $(function () { $('[data-toggle="tooltip"]').tooltip() });
    @endif
  });
</script>
<!-- Password Confirm Modal (reused) -->
<div class="modal fade" id="confirmPasswordModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-lock mr-1"></i> Konfirmasi Password</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group mb-1">
          <label for="confirm-password-input">Masukkan password Anda</label>
          <input type="password" class="form-control" id="confirm-password-input" autocomplete="current-password">
          <small class="form-text text-muted">Diperlukan untuk melihat QR / recovery codes.</small>
          <div id="confirm-password-error" class="invalid-feedback d-block" style="display:none;"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="confirm-password-submit"><i class="fas fa-check mr-1"></i> Konfirmasi</button>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '{{ csrf_token() }}';
    const submitConfirm = document.getElementById('confirm-password-submit');
    if (!submitConfirm) return;
    submitConfirm.addEventListener('click', async function() {
      const pwd = document.getElementById('confirm-password-input').value;
      const errEl = document.getElementById('confirm-password-error');
      errEl.style.display = 'none';
      try {
        const res = await fetch("{{ url('/user/confirm-password') }}", {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
          body: JSON.stringify({ password: pwd })
        });
        if (res.ok) {
          $('#confirmPasswordModal').modal('hide');
          if (typeof window.afterPasswordConfirmed2FA === 'function') {
            const cb = window.afterPasswordConfirmed2FA;
            window.afterPasswordConfirmed2FA = null;
            setTimeout(cb, 150);
          }
        } else {
          let data = {};
          try { data = await res.json(); } catch (e) {}
          errEl.style.display = 'block';
          errEl.textContent = (data && data.errors && data.errors.password && data.errors.password[0]) || 'Konfirmasi password gagal.';
        }
      } catch (e) {
        errEl.style.display = 'block';
        errEl.textContent = 'Terjadi kesalahan. Coba lagi.';
      }
    });
  });
</script>
@endpush
@endsection
