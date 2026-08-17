@extends('layouts.dashboard')

@section('title', 'Data Pengguna')

@section('content')
    @php($hasFilters = collect($filters)->contains(fn ($value) => filled($value)))

    <header class="rs-page-header d-flex flex-column flex-md-row align-items-md-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="rs-page-title h3 mb-1">Data Pengguna</h1>
            <p class="rs-page-description text-body-secondary mb-0">Daftar akun pengguna internal SIRAPI.</p>
        </div>
        <a class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2" href="{{ route('users.create') }}">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
            <span>Tambah Pengguna</span>
        </a>
    </header>

    <section class="card rs-card shadow-sm mb-4" aria-label="Filter pengguna">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('users.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-lg-6 col-xl-4">
                    <label class="form-label" for="search">Pencarian</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body" aria-hidden="true">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        </span>
                        <input
                            class="form-control"
                            id="search"
                            name="search"
                            type="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Nama atau email"
                        >
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3 col-xl-2">
                    <label class="form-label" for="role">Peran</label>
                    <select class="form-select" id="role" name="role">
                        <option value="">Semua peran</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(($filters['role'] ?? null) == $role->id)>
                                {{ $role->display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3 col-xl-2">
                    <label class="form-label" for="division">Divisi</label>
                    <select class="form-select" id="division" name="division">
                        <option value="">Semua divisi</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->id }}" @selected(($filters['division'] ?? null) == $division->id)>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3 col-xl-2">
                    <label class="form-label" for="status">Status akun</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Semua status</option>
                        <option value="active" @selected(($filters['status'] ?? null) === 'active')>Aktif</option>
                        <option value="inactive" @selected(($filters['status'] ?? null) === 'inactive')>Nonaktif</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3 col-xl-2">
                    <div class="d-grid d-sm-flex gap-2">
                        <button class="btn btn-primary flex-sm-fill d-inline-flex align-items-center justify-content-center gap-2" type="submit">
                            <i class="fa-solid fa-filter" aria-hidden="true"></i>
                            <span>Terapkan Filter</span>
                        </button>
                        <a class="btn btn-outline-secondary flex-sm-fill d-inline-flex align-items-center justify-content-center gap-2" href="{{ route('users.index') }}">
                            <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                            <span>Reset</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="card rs-card shadow-sm overflow-hidden" aria-label="Daftar pengguna">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 rs-table">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Nama</th>
                        <th scope="col">Email</th>
                        <th scope="col">Nomor pegawai</th>
                        <th scope="col">Jabatan</th>
                        <th scope="col">Divisi</th>
                        <th class="text-center" scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php($isOwnActiveAccount = auth()->user()->is($user) && $user->is_active)
                        <tr>
                            <td class="fw-semibold text-body-emphasis">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->employee_number ?: '-' }}</td>
                            <td>{{ $user->position ?: '-' }}</td>
                            <td>{{ $user->division?->name ?: '-' }}</td>
                            <td class="text-center">
                                <div class="rs-action-group d-flex flex-nowrap justify-content-center align-items-center gap-2">
                                    <a
                                        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                                        href="{{ route('users.show', $user) }}"
                                        aria-label="Lihat detail {{ $user->name }}"
                                    >
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                        <span>Detail</span>
                                    </a>
                                    @if ($isOwnActiveAccount)
                                        <a
                                            class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                                            href="{{ route('users.edit', $user) }}"
                                            aria-label="Edit {{ $user->name }}"
                                            data-testid="user-edit-link"
                                        >
                                            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                            <span>Edit</span>
                                        </a>
                                        <span
                                            class="badge text-bg-light border text-secondary d-inline-flex align-items-center gap-1 px-2 py-2"
                                            title="Akun yang sedang digunakan tidak dapat dinonaktifkan"
                                        >
                                            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                                            <span>Akun Anda</span>
                                        </span>
                                    @else
                                        <form
                                            method="POST"
                                            action="{{ route('users.status', $user) }}"
                                            data-status-form
                                            data-user-name="{{ $user->name }}"
                                            data-status-action="{{ $user->is_active ? 'menonaktifkan' : 'mengaktifkan' }}"
                                            data-next-status="{{ $user->is_active ? 'nonaktif' : 'aktif' }}"
                                            data-testid="user-status-form"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="is_active" value="{{ $user->is_active ? 0 : 1 }}">
                                            <button
                                                class="btn btn-sm {{ $user->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} d-inline-flex align-items-center gap-1"
                                                type="submit"
                                            >
                                                @if ($user->is_active)
                                                    <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
                                                    <span>Nonaktifkan</span>
                                                @else
                                                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                                    <span>Aktifkan</span>
                                                @endif
                                            </button>
                                        </form>
                                        <div class="dropdown">
                                            <button
                                                class="btn btn-sm btn-link rs-overflow-toggle d-inline-flex align-items-center justify-content-center"
                                                id="userActions{{ $user->id }}"
                                                type="button"
                                                data-bs-toggle="dropdown"
                                                data-bs-boundary="viewport"
                                                data-rs-table-dropdown
                                                aria-expanded="false"
                                                aria-label="Aksi lainnya untuk pengguna {{ $user->name }}"
                                                data-testid="user-utility-menu"
                                            >
                                                <i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end rs-table-dropdown-menu" aria-labelledby="userActions{{ $user->id }}">
                                                <li>
                                                    <a
                                                        class="dropdown-item d-flex align-items-center gap-2"
                                                        href="{{ route('users.edit', $user) }}"
                                                        aria-label="Edit {{ $user->name }}"
                                                        data-testid="user-edit-link"
                                                    >
                                                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                                        <span>Edit Data</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="rs-empty-state text-center text-body-secondary py-5" colspan="6">
                                @if ($hasFilters)
                                    <x-empty-state
                                        icon="fa-solid fa-magnifying-glass"
                                        title="Data tidak ditemukan"
                                        description="Tidak ada data yang sesuai dengan pencarian atau filter."
                                        :action-url="route('users.index')"
                                        action-label="Reset"
                                    />
                                @else
                                    <x-empty-state
                                        icon="fa-solid fa-users"
                                        title="Belum ada Pengguna"
                                        description="Akun pengguna yang dibuat akan tampil di sini."
                                        :action-url="route('users.create')"
                                        action-label="Tambah Pengguna"
                                        action-icon="fa-plus"
                                        action-variant="primary"
                                    />
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="card-footer bg-body d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 py-3">
                <span class="small text-body-secondary">
                    Halaman {{ $users->currentPage() }} dari {{ $users->lastPage() }} ({{ $users->total() }} pengguna)
                </span>
                <div class="rs-pagination">
                    {{ $users->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </section>
@endsection
