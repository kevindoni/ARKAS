@extends('adminlte.layouts.user')

@section('page_title', 'Profil')

@section('breadcrumbs')
  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
  <li class="breadcrumb-item active">Profil</li>
@endsection

@section('content')
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
                {{ auth()->user()->is_admin ? 'Admin' : 'Bendahara' }}
              </span>
              @php $enabled = ! is_null(auth()->user()->two_factor_secret); @endphp
              @php $confirmed = ! is_null(auth()->user()->two_factor_confirmed_at); @endphp
              @if($enabled && $confirmed)
                <span class="badge badge-success ml-1"><i class="fas fa-lock mr-1"></i> 2FA Aktif</span>
              @elseif($enabled && ! $confirmed)
                <span class="badge badge-info ml-1"><i class="fas fa-hourglass-half mr-1"></i> 2FA Menunggu Konfirmasi</span>
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
    <div class="card card-outline card-secondary">
      <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" id="akun-tabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="tab-info" data-toggle="pill" href="#pane-info" role="tab" aria-controls="pane-info" aria-selected="true"><i class="fas fa-id-card mr-1"></i> Info</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="tab-password" data-toggle="pill" href="#pane-password" role="tab" aria-controls="pane-password" aria-selected="false"><i class="fas fa-key mr-1"></i> Password</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="tab-2fa" data-toggle="pill" href="#pane-2fa" role="tab" aria-controls="pane-2fa" aria-selected="false"><i class="fas fa-shield-alt mr-1"></i> 2FA</a>
          </li>
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content" id="akun-tabs-content">
          <div class="tab-pane fade show active" id="pane-info" role="tabpanel" aria-labelledby="tab-info">
            <form action="{{ route('profile.updateInfo') }}" method="POST">
              @csrf
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="name">Nama</label>
                  <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                  @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group col-md-6">
                  <label for="email">Email</label>
                  <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                  @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                  @if(!auth()->user()->email_verified_at)
                    <small class="form-text text-warning">
                      Email belum diverifikasi.
                      <form id="resend-info-form" method="POST" action="{{ url('/email/verification-notification') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-link btn-sm p-0 align-baseline">Kirim ulang verifikasi</button>
                        <span id="last-sent-info" class="text-muted ml-1"></span>
                      </form>
                    </small>
                  @endif
                </div>
              </div>
              <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
            </form>
          </div>

          <div class="tab-pane fade" id="pane-password" role="tabpanel" aria-labelledby="tab-password">
            <form action="{{ route('profile.updatePassword') }}" method="POST">
              @csrf
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="current_password">Password Saat Ini</label>
                  <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                  @error('current_password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group col-md-4">
                  <label for="password">Password Baru</label>
                  <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                  @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group col-md-4">
                  <label for="password_confirmation">Konfirmasi Password</label>
                  <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                </div>
              </div>
              <button type="submit" class="btn btn-primary"><i class="fas fa-key mr-1"></i> Ubah Password</button>
            </form>
          </div>

          <div class="tab-pane fade" id="pane-2fa" role="tabpanel" aria-labelledby="tab-2fa">
            @if($enabled)
              @php $confirmed = ! is_null(auth()->user()->two_factor_confirmed_at); @endphp
              <div class="alert {{ $confirmed ? 'alert-success' : 'alert-info' }}">2FA saat ini <strong>{{ $confirmed ? 'Aktif' : 'Menunggu konfirmasi' }}</strong>.</div>
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
              @unless($confirmed)
                <div class="mb-3">
                  <form method="POST" action="{{ url('/user/confirmed-two-factor-authentication') }}" class="form-inline">
                    @csrf
                    <label for="code" class="sr-only">Kode OTP</label>
                    <input type="text" pattern="[0-9]*" inputmode="numeric" class="form-control mr-2" id="code" name="code" placeholder="Masukkan kode 6 digit" required maxlength="6">
                    <button class="btn btn-success"><i class="fas fa-check mr-1"></i> Konfirmasi Kode</button>
                  </form>
                  <small class="text-muted d-block mt-1">Masukkan kode OTP dari aplikasi autentikator untuk menyelesaikan aktivasi 2FA.</small>
                </div>
              @endunless
              <form id="disable-2fa-form" method="POST" action="{{ url('/user/two-factor-authentication') }}" class="d-inline-block">
                @csrf
                @method('DELETE')
          <button type="button" id="btn-disable-2fa" class="btn btn-danger"><i class="fas fa-shield-alt mr-1"></i> Nonaktifkan 2FA</button>
              </form>
            @else
              <div class="alert alert-warning">2FA saat ini <strong>Nonaktif</strong>.</div>
              <p class="text-muted">Aktifkan 2FA untuk meningkatkan keamanan akun Anda. Setelah aktif, Anda akan mendapatkan QR Code untuk dipindai di aplikasi autentikator.</p>
              <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                @csrf
          <button class="btn btn-primary"><i class="fas fa-shield-alt mr-1"></i> Aktifkan 2FA</button>
              </form>
            @endif

            <hr>
            <h6 class="mb-2">Recovery Codes</h6>
            @if($enabled && auth()->user()->two_factor_confirmed_at)
              <div class="mb-2 d-flex align-items-center flex-wrap">
                <button id="copy-codes" class="btn btn-xs btn-outline-secondary mr-2 mb-2" data-toggle="tooltip" title="Simpan codes ini di tempat yang aman (password manager atau catatan terenkripsi)."><i class="fas fa-copy mr-1"></i> Salin semua</button>
                <form method="POST" action="{{ url('/user/two-factor-recovery-codes') }}" class="d-inline-block mb-2">
                  @csrf
          <button class="btn btn-xs btn-outline-secondary"><i class="fas fa-sync mr-1"></i> Regenerasi</button>
                </form>
              </div>
              <div id="codes-container" class="p-3 border rounded bg-white"></div>
            @elseif($enabled)
              <p class="text-muted mb-0">Recovery codes akan tersedia setelah 2FA dikonfirmasi dengan kode OTP.</p>
            @else
              <p class="text-muted mb-0">Aktifkan 2FA untuk mendapatkan recovery codes.</p>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Last sent timestamp (shared with banner) using localStorage
    (function() {
      const KEY = 'verification_last_sent_at';
      const label = document.getElementById('last-sent-info');
      function render() {
        if (!label) return;
        const v = localStorage.getItem(KEY);
        if (!v) { label.textContent = ''; return; }
        const date = new Date(parseInt(v, 10));
        const diff = Math.max(0, Math.floor((Date.now() - date.getTime())/1000));
        const mm = Math.floor(diff / 60);
        const ss = diff % 60;
        label.textContent = `(terakhir dikirim ${mm>0?mm+'m ':''}${ss}s lalu)`;
      }
      render();
      setInterval(render, 1000);
      const form = document.getElementById('resend-info-form');
      if (form) {
        form.addEventListener('submit', function() {
          localStorage.setItem(KEY, String(Date.now()));
          setTimeout(render, 1200);
        });
      }
    })();
    let codesCache = [];
    const csrfToken = '{{ csrf_token() }}';

  // Shared callback to run after password confirmation
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

    // Tabs: activate based on hash
    function activateTabFromHash() {
      const hash = window.location.hash.replace('#','').toLowerCase();
      if (hash === 'password') {
        $('#tab-password').tab('show');
      } else if (hash === '2fa') {
        $('#tab-2fa').tab('show');
      } else if (hash === 'info' || hash === '') {
        $('#tab-info').tab('show');
      }
      // Smooth scroll to tab card
      const card = document.querySelector('#akun-tabs');
      if (card) {
        window.requestAnimationFrame(() => {
          window.scrollTo({ top: card.getBoundingClientRect().top + window.pageYOffset - 80, behavior: 'smooth' });
        });
      }
    }
    activateTabFromHash();
    window.addEventListener('hashchange', activateTabFromHash);
    // Smooth scroll when clicking internal anchors pointing to #info/#password/#2fa
    document.querySelectorAll('a[href*="#info"], a[href*="#password"], a[href*="#2fa"]').forEach(a => {
      a.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href && href.includes('#')) {
          // let hashchange handle tab activation; just scroll smoothly
          setTimeout(() => activateTabFromHash(), 0);
        }
      });
    });

    // Only load QR/codes/otpauth when 2FA tab becomes active
    $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
      if (e.target.id === 'tab-2fa') {
        @if(! is_null(auth()->user()->two_factor_secret))
          loadQR();
          loadCodes();
          loadOtpauth();
          $(function () { $('[data-toggle="tooltip"]').tooltip() });
        @endif
      // Optional toast on tab change
      if (window.toastr) {
        const label = e.target.textContent.trim();
        toastr.info('Berpindah ke tab: ' + label);
      }
      }
    });

    // Password confirm modal before disabling 2FA
    const btnDisable = document.getElementById('btn-disable-2fa');
    if (btnDisable) {
      btnDisable.addEventListener('click', function() {
        document.getElementById('confirm-password-input').value = '';
        document.getElementById('confirm-password-error').style.display = 'none';
        window.afterPasswordConfirmed2FA = () => { document.getElementById('disable-2fa-form').submit(); };
        $('#confirmPasswordModal').modal('show');
      });
    }

    const submitConfirm = document.getElementById('confirm-password-submit');
    if (submitConfirm) {
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
              // small delay to ensure modal hides smoothly
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
    }

    // Auto-load when enabled
    // Note: Now deferred to when 2FA tab is shown
  });
</script>
<!-- Password Confirm Modal -->
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
          <small class="form-text text-muted">Diperlukan untuk menonaktifkan 2FA.</small>
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
@endpush
