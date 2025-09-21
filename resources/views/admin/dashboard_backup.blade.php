@extends('adminlte.layouts.admin')

@section('page_title', 'Dashboard Admin')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['totalUsers'] ?? 0 }}</h3>
                    <p>User Sekolah</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ route('admin.users') }}" class="small-box-footer">Kelola <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['totalSchools'] ?? 0 }}</h3>
                    <p>Total Sekolah</p>
                </div>
                <div class="icon"><i class="fas fa-school"></i></div>
                <span class="small-box-footer">{{ $stats['completeSchools'] ?? 0 }} lengkap</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $subscriptionStats['active_subscriptions'] ?? 0 }}</h3>
                    <p>Sekolah Aktif</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <span class="small-box-footer">Subscription active</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['completeSchools'] ?? 0 }}</h3>
                    <p>Sekolah Aktif</p>
                </div>
                <div class="icon"><i class="fas fa-school"></i></div>
                <span class="small-box-footer">Data lengkap</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-info">
                <div class="inner">
                    <h3>{{ $systemStats['active_users'] ?? 0 }}</h3>
                    <p>User dengan Data</p>
                </div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
                <span class="small-box-footer">Aktif menggunakan sistem</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-primary">
                <div class="inner">
                    <h3>{{ $systemStats['users_with_data'] ?? 0 }}</h3>
                    <p>User Terdaftar Data</p>
                </div>
                <div class="icon"><i class="fas fa-database"></i></div>
                <span class="small-box-footer">Memiliki transaksi</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['twoFaConfirmed'] ?? 0 }}</h3>
                    <p>2FA Terkonfirmasi</p>
                </div>
                <div class="icon"><i class="fas fa-lock"></i></div>
                <a href="{{ route('admin.users') }}" class="small-box-footer">Lihat pengguna <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['unverified'] ?? 0 }}</h3>
                    <p>Email Belum Terverifikasi</p>
                </div>
                <div class="icon"><i class="fas fa-envelope-open-text"></i></div>
                <a href="{{ route('admin.users') }}" class="small-box-footer">Tindak lanjuti <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title">Pengguna Terbaru</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.users') }}" class="btn btn-tool"><i class="fas fa-external-link-alt"></i></a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table m-0">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Verifikasi</th>
                                    <th>Bergabung</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentUsers as $u)
                                    <tr>
                                        <td>{{ $u->name }}</td>
                                        <td>{{ $u->email }}</td>
                                        <td>
                                            @if($u->email_verified_at)
                                                <span class="badge badge-success">Terverifikasi</span>
                                            @else
                                                <span class="badge badge-warning">Belum</span>
                                            @endif
                                        </td>
                                        <td>{{ $u->created_at->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">Belum ada pengguna</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title">Monitoring Subscription</h3>
                    <div class="card-tools">
                        <button class="btn btn-tool"><i class="fas fa-sync"></i></button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table m-0">
                            <thead>
                                <tr>
                                    <th>Sekolah</th>
                                    <th>Status</th>
                                    <th>Paket</th>
                                    <th>Berakhir</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data dummy untuk demo subscription model -->
                                <tr>
                                    <td><strong>SD Negeri 01</strong><br><small class="text-muted">Kevin - Kevindoni17@gmal.com</small></td>
                                    <td><span class="badge badge-success">Aktif</span></td>
                                    <td><span class="badge badge-info">Tahunan</span></td>
                                    <td><span class="text-success">31 Agu 2026</span><br><small class="text-muted">11 bulan lagi</small></td>
                                    <td><button class="btn btn-sm btn-outline-primary">Detail</button></td>
                                </tr>
                                <tr class="table-warning">
                                    <td><strong>SMP Negeri 05</strong><br><small class="text-muted">Demo - demo@sekolah.com</small></td>
                                    <td><span class="badge badge-warning">Akan Berakhir</span></td>
                                    <td><span class="badge badge-warning">Semester</span></td>
                                    <td><span class="text-warning">15 Okt 2025</span><br><small class="text-muted">23 hari lagi</small></td>
                                    <td><button class="btn btn-sm btn-warning">Perpanjang</button></td>
                                </tr>
                                <tr class="table-info">
                                    <td><strong>SMK Negeri 02</strong><br><small class="text-muted">Trial User - trial@smk.com</small></td>
                                    <td><span class="badge badge-info">Trial</span></td>
                                    <td><span class="badge badge-light">14 Hari</span></td>
                                    <td><span class="text-info">05 Okt 2025</span><br><small class="text-muted">13 hari lagi</small></td>
                                    <td><button class="btn btn-sm btn-success">Upgrade</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-sm-6">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Data dummy untuk demo subscription model
                            </small>
                        </div>
                        <div class="col-sm-6 text-right">
                            <small><strong>Revenue Bulanan: ~Rp 2.500.000</strong></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Alert Subscription</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Akan Berakhir</h6>
                        <p class="mb-1"><strong>SMP Negeri 05</strong></p>
                        <small>Berakhir dalam 23 hari (15 Okt 2025)</small>
                    </div>
                    <div class="alert alert-info">
                        <h6><i class="fas fa-clock"></i> Trial Ending</h6>
                        <p class="mb-1"><strong>SMK Negeri 02</strong></p>
                        <small>Trial berakhir dalam 13 hari</small>
                    </div>
                    <div class="alert alert-success">
                        <h6><i class="fas fa-chart-line"></i> Revenue Update</h6>
                        <p class="mb-1">Proyeksi bulanan: <strong>Rp 2.500.000</strong></p>
                        <small>3 sekolah aktif berlangganan</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title">Pengguna Terbaru</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.users') }}" class="btn btn-tool"><i class="fas fa-external-link-alt"></i></a>
                    </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Sekolah Berlangganan:</strong><br>
                        <span class="text-success h5">{{ $subscriptionStats['active_subscriptions'] ?? 0 }}</span>
                        <small class="text-muted d-block">Active subscription</small>
                    </div>
                    <div class="mb-3">
                        <strong>User Aktif:</strong><br>
                        <span class="text-info h5">{{ $systemStats['active_users'] ?? 0 }}</span>
                        <small class="text-muted d-block">End-user menggunakan sistem</small>
                    </div>
                    <div class="mb-3">
                        <strong>Data Lengkap:</strong><br>
                        <span class="text-primary h5">{{ $stats['completeSchools'] ?? 0 }}</span>
                        <small class="text-muted d-block">Sekolah dengan profil lengkap</small>
                    </div>
                    <div class="mb-3">
                        <strong>Admin Count:</strong><br>
                        <span class="text-secondary h5">{{ $stats['admins'] ?? 0 }}</span>
                        <small class="text-muted d-block">System administrator</small>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tindakan Cepat</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-primary mb-2 mr-2"><i class="fas fa-users mr-1"></i> Kelola Pengguna</a>
                    <a href="{{ route('admin.settings') }}" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-cogs mr-1"></i> Pengaturan</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Sistem</h3>
                </div>
                <div class="card-body">
                    <div class="row small">
                        <div class="col-6"><strong>Laravel:</strong></div>
                        <div class="col-6">{{ $system['laravel'] }}</div>
                        <div class="col-6"><strong>PHP:</strong></div>
                        <div class="col-6">{{ $system['php'] }}</div>
                        <div class="col-6"><strong>Environment:</strong></div>
                        <div class="col-6"><span class="badge badge-{{ $system['env'] === 'production' ? 'success' : 'warning' }}">{{ strtoupper($system['env']) }}</span></div>
                        <div class="col-6"><strong>Debug:</strong></div>
                        <div class="col-6"><span class="badge badge-{{ $system['debug'] === 'off' ? 'success' : 'danger' }}">{{ strtoupper($system['debug']) }}</span></div>
                        <div class="col-6"><strong>Timezone:</strong></div>
                        <div class="col-6">{{ $system['timezone'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
