<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'Dashboard' }} | {{ $appName ?? config('app.name', 'Laravel') }}</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/dist/css/adminlte.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/plugins/toastr/toastr.min.css') }}">
  <style>
    .brand-image { width: 32px; height: 32px; }
  </style>
  @stack('styles')
  </head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-light navbar-white">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="{{ route('dashboard') }}" class="nav-link">Dashboard Bendahara</a>
      </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button"><i class="fas fa-expand-arrows-alt"></i></a>
      </li>
      <li class="nav-item">
        <a href="{{ route('logout') }}" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
      </li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('dashboard') }}" class="brand-link">
      <img src="{{ $appLogo ? asset('storage/'.$appLogo) : asset('assets/dist/img/AdminLTELogo.png') }}" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">{{ $appName ?? config('app.name', 'Laravel') }}</span>
    </a>

    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="{{ asset('assets/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block">{{ auth()->user()->name }}</a>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('school.index') }}" class="nav-link {{ request()->routeIs('school.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-school"></i>
              <p>Data Sekolah</p>
            </a>
          </li>
          @php $bkuActive = request()->routeIs('bku.*'); @endphp
          <li class="nav-item has-treeview {{ $bkuActive ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ $bkuActive ? 'active' : '' }}">
              <i class="nav-icon fas fa-book"></i>
              <p>
                BKU
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                @php $isMaster = request()->routeIs('bku.master*'); @endphp
                <a href="{{ route('bku.master') }}" class="nav-link {{ $isMaster ? 'active' : '' }}">
                  <i class="{{ $isMaster ? 'fas' : 'far' }} fa-circle nav-icon"></i>
                  <p>Data Master</p>
                </a>
              </li>
              <li class="nav-item">
                @php $isUmum = request()->routeIs('bku.umum'); @endphp
                <a href="{{ route('bku.umum') }}" class="nav-link {{ $isUmum ? 'active' : '' }}">
                  <i class="{{ $isUmum ? 'fas' : 'far' }} fa-circle nav-icon"></i>
                  <p>Umum</p>
                </a>
              </li>
              <li class="nav-item">
                @php $isTunai = request()->routeIs('bku.tunai'); @endphp
                <a href="{{ route('bku.tunai') }}" class="nav-link {{ $isTunai ? 'active' : '' }}">
                  <i class="{{ $isTunai ? 'fas' : 'far' }} fa-circle nav-icon"></i>
                  <p>Tunai</p>
                </a>
              </li>
              <li class="nav-item">
                @php $isBank = request()->routeIs('bku.bank'); @endphp
                <a href="{{ route('bku.bank') }}" class="nav-link {{ $isBank ? 'active' : '' }}">
                  <i class="{{ $isBank ? 'fas' : 'far' }} fa-circle nav-icon"></i>
                  <p>Bank</p>
                </a>
              </li>
              <li class="nav-item">
                @php $isPajak = request()->routeIs('bku.pajak'); @endphp
                <a href="{{ route('bku.pajak') }}" class="nav-link {{ $isPajak ? 'active' : '' }}">
                  <i class="{{ $isPajak ? 'fas' : 'far' }} fa-circle nav-icon"></i>
                  <p>Pajak</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="{{ route('profile.index') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-user-cog"></i>
              <p>Akun</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        @if(auth()->check() && !auth()->user()->email_verified_at)
          <div class="alert alert-warning mb-3 d-flex align-items-center justify-content-between">
            <div>
              <i class="fas fa-exclamation-triangle mr-1"></i> Email Anda belum diverifikasi.
              <small id="last-sent-label" class="text-muted ml-2"></small>
            </div>
            <form id="resend-banner-form" method="POST" action="{{ url('/email/verification-notification') }}" class="mb-0">
              @csrf
              <button type="submit" class="btn btn-sm btn-outline-dark"><i class="fas fa-paper-plane mr-1"></i> Kirim ulang verifikasi</button>
            </form>
          </div>
        @endif
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>@yield('page_title', $title ?? 'Dashboard')</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              @hasSection('breadcrumbs')
                @yield('breadcrumbs')
              @else
                @php
                  $routeName = \Illuminate\Support\Facades\Route::currentRouteName();
                  $map = [
                    'dashboard' => ['Dashboard'],
                    'school.index' => ['Data Sekolah'],
                    'bku.master' => ['BKU', 'Data Master'],
                    'bku.umum' => ['BKU', 'Umum'],
                    'bku.tunai' => ['BKU', 'Tunai'],
                    'bku.bank' => ['BKU', 'Bank'],
                    'bku.pajak' => ['BKU', 'Pajak'],
                  ];
                  $trail = $map[$routeName] ?? null;
                @endphp
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Beranda</a></li>
                @if($trail)
                  @foreach($trail as $i => $label)
                    @if($i === count($trail)-1)
                      <li class="breadcrumb-item active">{{ $label }}</li>
                    @else
                      <li class="breadcrumb-item">{{ $label }}</li>
                    @endif
                  @endforeach
                @else
                  <li class="breadcrumb-item active">@yield('page_title', $title ?? 'Dashboard')</li>
                @endif
              @endif
            </ol>
          </div>
        </div>
      </div>
    </section>
    <section class="content">
      <div class="container-fluid">
        @yield('content')
      </div>
    </section>
  </div>

  <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/dist/js/adminlte.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>
  <script>
    // Toastr defaults
    toastr.options = {
      closeButton: true,
      progressBar: true,
      newestOnTop: true,
      timeOut: 3500,
      positionClass: 'toast-bottom-right'
    };
    // Session flashes
    @if(session('status'))
      toastr.success(@json(session('status')));
    @endif
    @if($errors && $errors->any())
      @foreach($errors->all() as $err)
        toastr.error(@json($err));
      @endforeach
    @endif
    // Last sent timestamp for email verification
    (function() {
      const KEY = 'verification_last_sent_at';
      const label = document.getElementById('last-sent-label');
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
      const form = document.getElementById('resend-banner-form');
      if (form) {
        form.addEventListener('submit', function() {
          localStorage.setItem(KEY, String(Date.now()));
          setTimeout(render, 1200);
        });
      }
    })();
  </script>
  @stack('scripts')
</div>
</body>
</html>
