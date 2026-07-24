@extends('layouts.dashboard')

@section('title', 'Data Pengguna')

@push('styles')
    <style>
        .filter-card,
        .table-card { border: 1px solid var(--line); border-radius: 14px; background: #fff; box-shadow: 0 8px 26px rgba(31, 41, 55, .05); }
        .filter-card { padding: 20px; margin-bottom: 22px; }
        .filters { display: grid; grid-template-columns: minmax(220px, 2fr) repeat(3, minmax(150px, 1fr)) auto; gap: 14px; align-items: end; }
        .field { min-width: 0; }
        .field label { display: block; margin-bottom: 7px; color: #475569; font-size: 13px; font-weight: 700; }
        .field input,
        .field select { width: 100%; height: 42px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 8px; color: var(--ink); background: #fff; font: inherit; }
        .field input:focus,
        .field select:focus { border-color: var(--primary); outline: 3px solid rgba(49, 130, 206, .16); }
        .filter-actions { display: flex; gap: 8px; }
        .button { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 0 14px; border: 1px solid transparent; border-radius: 8px; cursor: pointer; font: inherit; font-size: 14px; font-weight: 700; text-decoration: none; }
        .button-primary { color: #fff; background: var(--primary); }
        .button-primary:hover { background: var(--primary-dark); }
        .button-secondary { border-color: #cbd5e1; color: #475569; background: #fff; }
        .button-secondary:hover { background: #f8fafc; }
        .table-card { overflow: hidden; }
        .table-scroll { overflow-x: auto; }
        .user-table { width: 100%; min-width: 1120px; border-collapse: collapse; }
        .user-table th,
        .user-table td { padding: 16px 18px; border-bottom: 1px solid #edf0f4; text-align: left; vertical-align: middle; }
        .user-table th { color: #475569; background: #f8fafc; font-size: 12px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; white-space: nowrap; }
        .user-table td { color: #334155; font-size: 14px; }
        .user-table tbody tr:last-child td { border-bottom: 0; }
        .user-table tbody tr:hover { background: #fbfdff; }
        .primary-text { color: var(--ink); font-weight: 700; }
        .status { display: inline-flex; padding: 5px 9px; border-radius: 999px; font-size: 12px; font-weight: 800; white-space: nowrap; }
        .status-active { color: #16803c; background: #e8f7ed; }
        .status-inactive { color: #c2414b; background: #fff0f1; }
        .action-placeholder { color: #94a3b8; font-size: 13px; font-style: italic; white-space: nowrap; }
        .empty { padding: 46px 20px; color: var(--muted); text-align: center; }
        .pagination { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 16px 18px; border-top: 1px solid var(--line); color: var(--muted); font-size: 14px; }
        .pagination-actions { display: flex; gap: 8px; }
        .pagination-link { display: inline-flex; align-items: center; min-height: 34px; padding: 0 11px; border: 1px solid #cbd5e1; border-radius: 7px; color: var(--primary-dark); background: #fff; font-weight: 700; text-decoration: none; }
        .pagination-link:hover { border-color: var(--primary); color: var(--primary); }
        .pagination-link[aria-disabled="true"] { pointer-events: none; opacity: .45; }
        @media (max-width: 1060px) { .filters { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 620px) { .filter-card { padding: 16px; } .filters { grid-template-columns: 1fr; } .filter-actions .button { flex: 1; } .pagination { align-items: flex-start; flex-direction: column; } }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <h1>Data Pengguna</h1>
        <p>Daftar akun pengguna Radarsurat.</p>
    </div>

    <section class="filter-card" aria-label="Filter pengguna">
        <form method="GET" action="{{ route('users.index') }}" class="filters">
            <div class="field">
                <label for="search">Pencarian</label>
                <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama atau email">
            </div>
            <div class="field">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="">Semua role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected(($filters['role'] ?? null) == $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="division">Divisi</label>
                <select id="division" name="division">
                    <option value="">Semua divisi</option>
                    @foreach ($divisions as $division)
                        <option value="{{ $division->id }}" @selected(($filters['division'] ?? null) == $division->id)>{{ $division->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="status">Status akun</label>
                <select id="status" name="status">
                    <option value="">Semua status</option>
                    <option value="active" @selected(($filters['status'] ?? null) === 'active')>Aktif</option>
                    <option value="inactive" @selected(($filters['status'] ?? null) === 'inactive')>Tidak aktif</option>
                </select>
            </div>
            <div class="filter-actions">
                <button class="button button-primary" type="submit">Terapkan</button>
                <a class="button button-secondary" href="{{ route('users.index') }}">Reset</a>
            </div>
        </form>
    </section>

    <section class="table-card" aria-label="Daftar pengguna">
        <div class="table-scroll">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Nomor pegawai</th>
                        <th>Jabatan</th>
                        <th>Role</th>
                        <th>Divisi</th>
                        <th>Status akun</th>
                        <th>Terakhir login</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="primary-text">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->employee_number ?: '-' }}</td>
                            <td>{{ $user->position ?: '-' }}</td>
                            <td>{{ $user->role?->name ?: '-' }}</td>
                            <td>{{ $user->division?->name ?: '-' }}</td>
                            <td><span class="status {{ $user->is_active ? 'status-active' : 'status-inactive' }}">{{ $user->is_active ? 'Aktif' : 'Tidak aktif' }}</span></td>
                            <td>{{ $user->last_login_at?->translatedFormat('d M Y, H:i') ?: '-' }}</td>
                            <td><span class="action-placeholder">Belum tersedia</span></td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="9">Tidak ada pengguna yang sesuai dengan pencarian atau filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <nav class="pagination" aria-label="Pagination pengguna">
                <span>Halaman {{ $users->currentPage() }} dari {{ $users->lastPage() }} ({{ $users->total() }} pengguna)</span>
                <div class="pagination-actions">
                    <a class="pagination-link" href="{{ $users->previousPageUrl() ?: '#' }}" @if ($users->onFirstPage()) aria-disabled="true" @endif>Sebelumnya</a>
                    <a class="pagination-link" href="{{ $users->nextPageUrl() ?: '#' }}" @if (! $users->hasMorePages()) aria-disabled="true" @endif>Berikutnya</a>
                </div>
            </nav>
        @endif
    </section>
@endsection
