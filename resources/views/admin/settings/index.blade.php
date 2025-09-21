@php use Illuminate\Support\Facades\Storage; @endphp
@extends('adminlte.layouts.admin')

@section('page_title', 'Pengaturan')

@section('breadcrumbs')
  <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
  <li class="breadcrumb-item active">Pengaturan</li>
@endsection

@section('content')
<div class="row">
  <div class="col-md-9">
    <div class="card">
      <div class="card-header p-2">
        <ul class="nav nav-pills" id="settings-tabs">
          <li class="nav-item"><a class="nav-link active" href="#tab-general" data-toggle="tab"><i class="fas fa-sliders-h mr-1"></i> Umum</a></li>
          <li class="nav-item"><a class="nav-link" href="#tab-branding" data-toggle="tab"><i class="fas fa-image mr-1"></i> Branding</a></li>
          <li class="nav-item"><a class="nav-link" href="#tab-mail" data-toggle="tab"><i class="fas fa-envelope mr-1"></i> Mail</a></li>
        </ul>
      </div>
      <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="active_tab" id="active_tab" value="{{ old('active_tab', session('active_tab', 'general')) }}">
        <div class="card-body">
          @php $activeTab = old('active_tab', session('active_tab', 'general')); @endphp
          @if(session('status') && $activeTab !== 'mail')
            <div class="alert alert-success">{{ session('status') }}</div>
          @endif

          <div class="tab-content">
            <div class="active tab-pane" id="tab-general">
              <div class="form-group">
                <label for="app_name">Nama Aplikasi</label>
                <input type="text" name="app_name" id="app_name" value="{{ old('app_name', $appName) }}" class="form-control @error('app_name') is-invalid @enderror" required>
                @error('app_name')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                <small class="form-text text-muted">Nama ini akan tampil di header dan judul halaman.</small>
              </div>
            </div>

            <div class="tab-pane" id="tab-branding">
              @if(session()->has('logo_saved') && !session('logo_saved'))
                <div class="alert alert-warning">Logo tidak tersimpan. Silakan coba unggah ulang atau gunakan format PNG/JPG/SVG ≤ 2MB.</div>
              @endif
              <div class="form-group">
                <label for="app_logo">Logo Aplikasi</label>
                <div class="custom-file">
                  <input type="file" name="app_logo" id="app_logo" class="custom-file-input @error('app_logo') is-invalid @enderror" accept="image/png,image/jpeg,image/svg+xml">
                  <label class="custom-file-label" for="app_logo">Pilih file...</label>
                  @error('app_logo')
                    <span class="invalid-feedback">{{ $message }}</span>
                  @enderror
                </div>
                <small class="form-text text-muted">PNG/JPG/SVG maks 2MB. Disarankan rasio 1:1.</small>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <label class="d-block">Preview Baru</label>
                  <div class="border rounded p-2 text-center">
                    <img id="logo-preview" src="#" alt="Preview Logo" style="max-height: 96px; display:none;" class="img-fluid">
                    <span id="logo-preview-empty" class="text-muted">Belum ada file dipilih</span>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="d-block">Logo Saat Ini</label>
                  <div class="border rounded p-2 text-center">
                    @php
                      $currentLogoUrl = $appLogo ? Storage::url($appLogo) : null;
                      if ($currentLogoUrl && session('logo_saved')) {
                        $currentLogoUrl .= (str_contains($currentLogoUrl, '?') ? '&' : '?') . 'v=' . time();
                      }
                    @endphp
                    @if($currentLogoUrl)
                      <img src="{{ $currentLogoUrl }}" alt="Logo" style="max-height: 96px;" class="img-fluid">
                    @else
                      <span class="text-muted">Belum disetel</span>
                    @endif
                  </div>
                </div>
              </div>
            </div>

            <div class="tab-pane" id="tab-mail">
              <div class="alert alert-info mb-2">
                Pengiriman email saat ini mengikuti konfigurasi .env (MAIL_*). Gunakan tombol Email Uji untuk memastikan konfigurasi berjalan.
              </div>
              @if(session('mail_status'))
                <div class="alert alert-success mb-2">{{ session('mail_status') }}</div>
              @endif
              @php
                $defaultMailer = config('mail.default');
                $host = config('mail.mailers.'.$defaultMailer.'.host');
                $port = (int) (config('mail.mailers.'.$defaultMailer.'.port') ?? 0);
                $logChannel = config('mail.mailers.log.channel');
              @endphp
              <dl class="row mb-0 small">
                <dt class="col-sm-3">Mailer</dt>
                <dd class="col-sm-9">
                  <select name="mail_mailer" class="form-control form-control-sm" style="max-width:240px; display:inline-block;">
                    @foreach(['smtp'=>'SMTP','log'=>'Log','mailgun'=>'Mailgun','ses'=>'AWS SES','postmark'=>'Postmark'] as $val=>$label)
                      <option value="{{ $val }}" {{ ($mail['mailer'] ?? $defaultMailer) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                </dd>
                <dt class="col-sm-3">Host</dt>
                <dd class="col-sm-9"><input type="text" name="mail_host" class="form-control form-control-sm" style="max-width:260px;" value="{{ $mail['host'] ?? ($host ?? '') }}"></dd>
                <dt class="col-sm-3">Port</dt>
                <dd class="col-sm-9"><input type="number" name="mail_port" class="form-control form-control-sm" style="max-width:160px;" value="{{ $mail['port'] ?? ($port ?: '') }}"></dd>
                <dt class="col-sm-3">Username</dt>
                <dd class="col-sm-9"><input type="text" name="mail_username" class="form-control form-control-sm" style="max-width:260px;" value="{{ $mail['username'] ?? '' }}" placeholder="(biarkan kosong jika tidak perlu)"></dd>
                <dt class="col-sm-3">Password</dt>
                <dd class="col-sm-9"><input type="text" name="mail_password" class="form-control form-control-sm" style="max-width:260px;" value="{{ $mail['password'] ?? '' }}" placeholder="(biarkan kosong jika tidak perlu)"></dd>
                <dt class="col-sm-3">Encryption</dt>
                <dd class="col-sm-9">
                  <select name="mail_encryption" class="form-control form-control-sm" style="max-width:200px; display:inline-block;">
                    @php $enc = $mail['encryption'] ?? config('mail.mailers.'.$defaultMailer.'.encryption'); @endphp
                    <option value="">(none)</option>
                    <option value="tls" {{ $enc==='tls' ? 'selected' : '' }}>tls</option>
                    <option value="ssl" {{ $enc==='ssl' ? 'selected' : '' }}>ssl</option>
                    <option value="null" {{ is_null($enc) ? 'selected' : '' }}>null</option>
                  </select>
                </dd>
                <dt class="col-sm-3">From</dt>
                <dd class="col-sm-9">
                  <div class="form-row">
                    <div class="col-sm-5 mb-1"><input type="email" name="mail_from_address" class="form-control form-control-sm" value="{{ $mail['from_address'] ?? config('mail.from.address') }}" placeholder="address@example.com"></div>
                    <div class="col-sm-5 mb-1"><input type="text" name="mail_from_name" class="form-control form-control-sm" value="{{ $mail['from_name'] ?? config('mail.from.name') }}" placeholder="Nama Pengirim"></div>
                  </div>
                </dd>
                <dt class="col-sm-3">Kirim ke</dt>
                <dd class="col-sm-9"><input type="email" name="mail_test_to" id="test_to" class="form-control form-control-sm" style="max-width:300px;" value="{{ $mail['test_to'] ?? (auth()->user()->email ?? '') }}" placeholder="email tujuan (opsional)"></dd>
              </dl>
              @php
                $rt = [
                  'mailer' => config('mail.default'),
                  'host' => config('mail.mailers.'.config('mail.default').'.host'),
                  'port' => config('mail.mailers.'.config('mail.default').'.port'),
                  'username' => config('mail.mailers.'.config('mail.default').'.username'),
                  'encryption' => config('mail.mailers.'.config('mail.default').'.encryption'),
                  'from_address' => config('mail.from.address'),
                  'from_name' => config('mail.from.name'),
                ];
                $ui = $mail;
                $diff = function($a, $b) { return ($a ?? '') !== ($b ?? ''); };
              @endphp
              <div class="card card-outline card-secondary mt-3">
                <div class="card-header p-2"><strong>Perbandingan Runtime vs UI</strong></div>
                <div class="card-body p-2 small">
                  <div class="row">
                    <div class="col-sm-6">
                      <div><span class="text-muted">Runtime Mailer:</span> <code>{{ $rt['mailer'] }}</code></div>
                      <div><span class="text-muted">Runtime Host:</span> <code>{{ $rt['host'] }}</code></div>
                      <div><span class="text-muted">Runtime Port:</span> <code>{{ $rt['port'] }}</code></div>
                      <div><span class="text-muted">Runtime Encryption:</span> <code>{{ $rt['encryption'] ?? '(none)' }}</code></div>
                      <div><span class="text-muted">Runtime From:</span> <code>{{ $rt['from_address'] }}</code> ({{ $rt['from_name'] }})</div>
                    </div>
                    <div class="col-sm-6">
                      <div>
                        <span class="text-muted">UI Mailer:</span> <code>{{ $ui['mailer'] }}</code>
                        @if($diff($rt['mailer'], $ui['mailer'])) <span class="badge badge-warning ml-1">beda</span> @endif
                      </div>
                      <div>
                        <span class="text-muted">UI Host:</span> <code>{{ $ui['host'] }}</code>
                        @if($diff($rt['host'], $ui['host'])) <span class="badge badge-warning ml-1">beda</span> @endif
                      </div>
                      <div>
                        <span class="text-muted">UI Port:</span> <code>{{ $ui['port'] }}</code>
                        @if($diff($rt['port'], $ui['port'])) <span class="badge badge-warning ml-1">beda</span> @endif
                      </div>
                      <div>
                        <span class="text-muted">UI Encryption:</span> <code>{{ $ui['encryption'] ?? '(none)' }}</code>
                        @if($diff($rt['encryption'], $ui['encryption'])) <span class="badge badge-warning ml-1">beda</span> @endif
                      </div>
                      <div>
                        <span class="text-muted">UI From:</span> <code>{{ $ui['from_address'] }}</code> ({{ $ui['from_name'] }})
                        @if($diff($rt['from_address'], $ui['from_address']) || $diff($rt['from_name'], $ui['from_name'])) <span class="badge badge-warning ml-1">beda</span> @endif
                      </div>
                    </div>
                  </div>
                  <small class="text-muted d-block mt-2">Runtime diambil dari .env/config saat ini. Untuk menyamakan dengan UI, gunakan Preset untuk mengubah .env lalu jalankan config:clear, atau centang “Gunakan pengaturan UI untuk Uji Kirim”.</small>
                </div>
              </div>
              <div class="mt-2 d-flex flex-wrap align-items-start">
                @if($defaultMailer === 'smtp' && in_array($host, ['127.0.0.1','localhost']) && $port === 1025)
                  <a href="http://localhost:8025/" target="_blank" class="btn btn-sm btn-outline-info mr-2 mb-2"><i class="fas fa-external-link-alt mr-1"></i> Buka Mailpit</a>
                @elseif($defaultMailer === 'log')
                  <span class="badge badge-secondary mr-2 mb-2">Mailer: log</span>
                  <small class="text-muted mr-3 mb-2">Email ditulis ke log ({{ $logChannel ?: 'default' }}). Cek file <code>storage/logs/laravel.log</code>.</small>
                @elseif(in_array($defaultMailer, ['mailgun','ses','postmark']))
                  @if($defaultMailer==='mailgun')
                    <a href="https://app.mailgun.com/app/sending/domains" target="_blank" class="btn btn-sm btn-outline-info mr-2 mb-2"><i class="fas fa-external-link-alt mr-1"></i> Buka Mailgun</a>
                  @elseif($defaultMailer==='ses')
                    <a href="https://console.aws.amazon.com/ses/" target="_blank" class="btn btn-sm btn-outline-info mr-2 mb-2"><i class="fas fa-external-link-alt mr-1"></i> Buka AWS SES</a>
                  @elseif($defaultMailer==='postmark')
                    <a href="https://account.postmarkapp.com/" target="_blank" class="btn btn-sm btn-outline-info mr-2 mb-2"><i class="fas fa-external-link-alt mr-1"></i> Buka Postmark</a>
                  @endif
                @else
                  <small class="text-muted mr-3 mb-2">Tidak ada tautan khusus untuk mailer ini. Cek inbox penerima atau dashboard provider Anda.</small>
                @endif
                <div class="form-inline mb-2 align-items-center">
                  <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" class="custom-control-input" id="use_ui_mail">
                    <label class="custom-control-label" for="use_ui_mail">Gunakan pengaturan UI untuk Uji Kirim</label>
                  </div>
                  <button type="button" id="btn-test-mail" class="btn btn-sm btn-primary"><i class="fas fa-paper-plane mr-1"></i> Kirim Email Uji</button>
                </div>
                <div class="w-100"></div>
                <div class="card w-100 mt-2">
                  <div class="card-header p-2"><strong>Preset Konfigurasi MAIL_*</strong></div>
                  <div class="card-body p-2">
                    <div class="form-row align-items-center">
                      <div class="col-sm-4 mb-2">
                        <select id="mail-preset" class="form-control form-control-sm">
                          <option value="">Pilih preset…</option>
                          <option value="mailpit">Mailpit (lokal)</option>
                          <option value="gmail">Gmail (App Password)</option>
                          <option value="mailgun">Mailgun (SMTP)</option>
                          <option value="ses">AWS SES (SMTP)</option>
                          <option value="postmark">Postmark (SMTP)</option>
                          <option value="log">Log (debug)</option>
                        </select>
                      </div>
                      <div class="col-sm-8 text-right mb-2">
                        <button type="button" id="copy-mail-snippet" class="btn btn-sm btn-outline-secondary" disabled><i class="fas fa-copy mr-1"></i> Salin Snippet .env</button>
                      </div>
                    </div>
                    <pre id="mail-snippet" class="mb-0 small" style="white-space:pre-wrap; user-select:text;"></pre>
                    <small class="text-muted d-block mt-1">Salin nilai di atas ke file <code>.env</code>, lalu jalankan ulang konfigurasi (config:clear) bila perlu.</small>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
        <div class="card-footer text-right">
          <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card">
      <div class="card-header"><i class="fas fa-lightbulb mr-1"></i> Tips</div>
      <div class="card-body small">
        <p>Gunakan nama aplikasi yang singkat dan mudah diingat.</p>
        <p>Logo tampil di sidebar dan header—gunakan gambar yang kontras.</p>
        <p>Uji email via Mailpit saat pengembangan.</p>
      </div>
    </div>
  </div>
</div>
@push('scripts')
<script src="{{ asset('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (window.bsCustomFileInput) { bsCustomFileInput.init(); }
  var file = document.getElementById('app_logo');
  var img = document.getElementById('logo-preview');
  var empty = document.getElementById('logo-preview-empty');
  if (file) {
    file.addEventListener('change', function(e) {
      const f = e.target.files && e.target.files[0];
      if (!f) { img.style.display = 'none'; empty.style.display = 'inline'; return; }
      const reader = new FileReader();
      reader.onload = (ev) => {
        img.src = ev.target.result;
        img.style.display = 'inline';
        empty.style.display = 'none';
      };
      reader.readAsDataURL(f);
    });
  }
  // Persist active tab via hash
  $(function(){
    const hash = window.location.hash;
    const sessionTab = "{{ session('active_tab') }}";
    const desired = hash ? hash : (sessionTab ? '#tab-' + sessionTab : null);
    if (desired) {
      const $link = $("a[href='"+desired+"']");
      if ($link.length) { $link.tab('show'); }
    }
    $('#settings-tabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
      history.replaceState(null, null, e.target.getAttribute('href'));
      var href = e.target.getAttribute('href');
      if (href && href.indexOf('#tab-') === 0) {
        var key = href.replace('#tab-', '');
        document.getElementById('active_tab').value = key;
      }
    });
    // Mail presets
    const presetSel = document.getElementById('mail-preset');
    const snippetEl = document.getElementById('mail-snippet');
    const copyBtn = document.getElementById('copy-mail-snippet');
    function setSnippet(key){
      const appName = @json(config('app.name'));
      const presets = {
        mailpit:`MAIL_MAILER=smtp\nMAIL_HOST=127.0.0.1\nMAIL_PORT=1025\nMAIL_USERNAME=null\nMAIL_PASSWORD=null\nMAIL_ENCRYPTION=null\nMAIL_FROM_ADDRESS="no-reply@arkas.test"\nMAIL_FROM_NAME="${appName}"`,
        gmail:`MAIL_MAILER=smtp\nMAIL_HOST=smtp.gmail.com\nMAIL_PORT=587\nMAIL_USERNAME=your@gmail.com\nMAIL_PASSWORD=your_app_password\nMAIL_ENCRYPTION=tls\nMAIL_FROM_ADDRESS="your@gmail.com"\nMAIL_FROM_NAME="${appName}"`,
        mailgun:`MAIL_MAILER=smtp\nMAIL_HOST=smtp.mailgun.org\nMAIL_PORT=587\nMAIL_USERNAME=postmaster@your-domain.com\nMAIL_PASSWORD=your_smtp_password\nMAIL_ENCRYPTION=tls\nMAIL_FROM_ADDRESS="no-reply@your-domain.com"\nMAIL_FROM_NAME="${appName}"`,
        ses:`MAIL_MAILER=smtp\nMAIL_HOST=email-smtp.<region>.amazonaws.com\nMAIL_PORT=587\nMAIL_USERNAME=SES_SMTP_USERNAME\nMAIL_PASSWORD=SES_SMTP_PASSWORD\nMAIL_ENCRYPTION=tls\nMAIL_FROM_ADDRESS="no-reply@your-domain.com"\nMAIL_FROM_NAME="${appName}"`,
        postmark:`MAIL_MAILER=smtp\nMAIL_HOST=smtp.postmarkapp.com\nMAIL_PORT=587\nMAIL_USERNAME=apikey\nMAIL_PASSWORD=POSTMARK_SERVER_TOKEN\nMAIL_ENCRYPTION=tls\nMAIL_FROM_ADDRESS="no-reply@your-domain.com"\nMAIL_FROM_NAME="${appName}"`,
        log:`MAIL_MAILER=log\n# Email akan ditulis ke storage/logs/laravel.log\nMAIL_FROM_ADDRESS="no-reply@arkas.test"\nMAIL_FROM_NAME="${appName}"`
      };
      snippetEl.textContent = presets[key] || '';
      copyBtn.disabled = !snippetEl.textContent;
    }
    presetSel && presetSel.addEventListener('change', function(){ setSnippet(this.value); });
    copyBtn && copyBtn.addEventListener('click', async function(){
      try { await navigator.clipboard.writeText(snippetEl.textContent); copyBtn.classList.add('btn-success'); copyBtn.classList.remove('btn-outline-secondary'); copyBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Disalin'; setTimeout(()=>{ copyBtn.classList.remove('btn-success'); copyBtn.classList.add('btn-outline-secondary'); copyBtn.innerHTML = '<i class="fas fa-copy mr-1"></i> Salin Snippet .env'; }, 1500);} catch(e) { alert('Gagal menyalin.'); }
    });
  });
  // Test mail via AJAX
  const btnTest = document.getElementById('btn-test-mail');
  if (btnTest) {
    btnTest.addEventListener('click', async function() {
      const to = (document.getElementById('test_to') || {}).value || '';
      const token = '{{ csrf_token() }}';
      btnTest.disabled = true;
      const original = btnTest.innerHTML;
      btnTest.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Mengirim...';
      try {
        const useUi = document.getElementById('use_ui_mail')?.checked ? true : false;
        const res = await fetch("{{ route('admin.settings.test-mail') }}", {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
          body: JSON.stringify({ to, use_ui: useUi ? 1 : 0 })
        });
        let data = {};
        try { data = await res.json(); } catch (e) {}
        const msg = (res.ok ? (data.message || 'Email uji terkirim.') : (data.message || 'Gagal mengirim email uji.'));
        const alert = document.createElement('div');
        alert.className = 'alert ' + (res.ok ? 'alert-success' : 'alert-danger');
        alert.textContent = msg;
        const tabMail = document.getElementById('tab-mail');
        tabMail.insertBefore(alert, tabMail.querySelector('dl'));
        setTimeout(() => alert.remove(), 5000);
      } catch (err) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger';
        alert.textContent = 'Gagal mengirim email uji.';
        const tabMail = document.getElementById('tab-mail');
        tabMail.insertBefore(alert, tabMail.querySelector('dl'));
        setTimeout(() => alert.remove(), 5000);
      } finally {
        btnTest.disabled = false;
        btnTest.innerHTML = original;
      }
    });
  }
});
</script>
@endpush
@endsection
