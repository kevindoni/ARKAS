@extends('adminlte.layouts.admin')

@section('page_title', 'Pengguna')

@section('breadcrumbs')
  <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
  <li class="breadcrumb-item active">Pengguna</li>
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <form method="GET" action="{{ route('admin.users') }}" class="form-inline">
      <div class="input-group input-group-sm mr-2" style="width: 250px;">
        <input type="text" name="q" class="form-control" placeholder="Cari nama atau email" value="{{ request('q') }}">
        <div class="input-group-append">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
      <div class="form-group form-group-sm mr-2">
        <select name="role" class="form-control form-control-sm" onchange="this.form.submit()">
          <option value="">Semua Peran</option>
          <option value="admin" {{ request('role')==='admin' ? 'selected' : '' }}>Admin</option>
          <option value="user" {{ request('role')==='user' ? 'selected' : '' }}>User</option>
        </select>
      </div>
      <a href="{{ route('admin.users') }}" class="btn btn-sm btn-default ml-1">Reset</a>
    </form>
  </div>
  <div class="card-body p-0">
    @if(session('status'))
      <div class="alert alert-success m-3">{{ session('status') }}</div>
    @endif
    @if($errors && $errors->any())
      <div class="alert alert-danger m-3">
        @foreach($errors->all() as $err)
          <div>{{ $err }}</div>
        @endforeach
      </div>
    @endif
    <div class="table-responsive">
      <table class="table table-hover table-striped mb-0">
        <thead>
          <tr>
            <th style="width: 50px;">#</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Peran</th>
            <th>2FA</th>
            <th>Dibuat</th>
            <th style="width: 260px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $user)
            <tr>
              <td>{{ ($users->currentPage()-1)*$users->perPage() + $loop->iteration }}</td>
              <td>{{ $user->name }}</td>
              <td><span class="text-monospace">{{ $user->email }}</span></td>
              <td>
                @if($user->is_admin)
                  <span class="badge badge-primary"><i class="fas fa-user-shield mr-1"></i> Admin</span>
                @else
                  <span class="badge badge-secondary"><i class="fas fa-user mr-1"></i> User</span>
                @endif
              </td>
              <td>
                @if($user->two_factor_confirmed_at)
                  <span class="badge badge-success">Aktif</span>
                @elseif($user->two_factor_secret)
                  <span class="badge badge-info">Menunggu</span>
                @else
                  <span class="badge badge-secondary">Nonaktif</span>
                @endif
              </td>
              <td>{{ $user->created_at?->format('Y-m-d H:i') }}</td>
              <td>
                <div class="btn-group btn-group-sm align-items-center">
                  <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info" title="Lihat"><i class="fas fa-eye"></i></a>
                  <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                  <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus {{ $user->email }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus"><i class="fas fa-trash"></i></button>
                  </form>
                  @if($user->two_factor_secret)
                    <form action="{{ route('admin.users.2fa.codes', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Regenerasi recovery codes untuk {{ $user->email }}?');">
                      @csrf
                      <button class="btn btn-secondary btn-sm" title="Regenerasi recovery codes"><i class="fas fa-sync"></i></button>
                    </form>
                    <form action="{{ route('admin.users.2fa.reset', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Nonaktifkan 2FA untuk {{ $user->email }}?');">
                      @csrf
                      <button class="btn btn-danger btn-sm" title="Nonaktifkan 2FA"><i class="fas fa-shield-alt"></i></button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted p-4">Tidak ada data.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @if($users instanceof \Illuminate\Contracts\Pagination\Paginator || $users instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
    <div class="card-footer clearfix">
      <div class="float-right">
        {{ $users->links() }}
      </div>
      <div class="text-muted small">
        Menampilkan {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} dari {{ $users->total() ?? $users->count() }} pengguna
      </div>
    </div>
  @endif
</div>
@endsection
