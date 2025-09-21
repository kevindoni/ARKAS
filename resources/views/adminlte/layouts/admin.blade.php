<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ $title ?? 'Admin' }} | {{ $appName ?? config('app.name', 'Laravel') }}</title>

	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
	<link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/dist/css/adminlte.min.css') }}">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

		<nav class="main-header navbar navbar-expand navbar-dark navbar-dark">
		<ul class="navbar-nav">
			<li class="nav-item">
				<a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
			</li>
			<li class="nav-item d-none d-sm-inline-block">
				<a href="{{ route('admin.dashboard') }}" class="nav-link">Dashboard</a>
			</li>
		</ul>
		<ul class="navbar-nav ml-auto">
			<li class="nav-item">
				<a class="nav-link" data-widget="fullscreen" href="#" role="button">
					<i class="fas fa-expand-arrows-alt"></i>
				</a>
			</li>
			<li class="nav-item dropdown">
				<a class="nav-link" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false">
					<i class="fas fa-user-circle"></i>
				</a>
				<div class="dropdown-menu dropdown-menu-right">
					<span class="dropdown-item-text text-muted">{{ auth()->user()->name }}</span>
					<div class="dropdown-divider"></div>
					<a href="{{ route('admin.profile') }}" class="dropdown-item">
						<i class="fas fa-user-cog mr-2"></i> Profil Admin
					</a>
					<a href="{{ route('dashboard') }}" class="dropdown-item">
						<i class="fas fa-home mr-2"></i> Kembali ke Situs
					</a>
					<div class="dropdown-divider"></div>
					<a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form-admin').submit();">
						<i class="fas fa-sign-out-alt mr-2"></i> Logout
					</a>
					<form id="logout-form-admin" action="{{ route('admin.logout') }}" method="POST" class="d-none">
						@csrf
					</form>
				</div>
			</li>
		</ul>
	</nav>

	<aside class="main-sidebar sidebar-dark-primary elevation-4">
		<a href="{{ route('admin.dashboard') }}" class="brand-link">
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
						<a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
							<i class="nav-icon fas fa-tachometer-alt"></i>
							<p>Dashboard</p>
						</a>
					</li>
										<li class="nav-item">
					    <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
				    <i class="nav-icon fas fa-users"></i>
				    <p>Pengguna</p>
								</a>
							</li>
							<li class="nav-item">
					    <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
				    <i class="nav-icon fas fa-cogs"></i>
				    <p>Pengaturan</p>
								</a>
							</li>
				</ul>
			</nav>
		</div>
	</aside>

		<div class="content-wrapper">
			<section class="content-header">
				<div class="container-fluid">
					<div class="row mb-2">
						<div class="col-sm-6">
							<h1>@yield('page_title', $title ?? 'Admin')</h1>
						</div>
						<div class="col-sm-6">
							<ol class="breadcrumb float-sm-right">
												@hasSection('breadcrumbs')
													@yield('breadcrumbs')
												@else
													@php
														$routeName = \Illuminate\Support\Facades\Route::currentRouteName();
														$parts = $routeName ? explode('.', $routeName) : [];
													@endphp
													<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Beranda</a></li>
													@if(count($parts) > 0)
														@foreach($parts as $i => $part)
															@if($i === count($parts)-1)
																<li class="breadcrumb-item active">{{ ucfirst($part) }}</li>
															@else
																<li class="breadcrumb-item">{{ ucfirst($part) }}</li>
															@endif
														@endforeach
													@else
														<li class="breadcrumb-item active">@yield('page_title', $title ?? 'Admin')</li>
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
  @stack('scripts')
</div>
</body>
</html>
