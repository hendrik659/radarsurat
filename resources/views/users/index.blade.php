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
        .user-table { width: 100%; min-width: 860px; border-collapse: collapse; }
        .user-table th,
        .user-table td { padding: 16px 18px; border-bottom: 1px solid #edf0f4; text-align: left; vertical-align: middle; }
        .user-table th { color: #475569; background: #f8fafc; font-size: 12px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; white-space: nowrap; }
        .user-table td { color: #334155; font-size: 14px; }
        .user-table tbody tr:last-child td { border-bottom: 0; }
        .user-table tbody tr:hover { background: #fbfdff; }
        .primary-text { color: var(--ink); font-weight: 700; }
        .page-actions { display: flex; justify-content: flex-end; margin: -8px 0 18px; }
        .alert-success { padding: 12px 14px; border: 1px solid #bbebca; border-radius: 9px; color: #166534; background: #effbf3; }
        .user-table th.actions-heading,
        .user-table td.actions-cell { text-align: center; }
        .row-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 7px; }
        .row-actions form { margin: 0; }
        .action-button { display: inline-flex; align-items: center; gap: 5px; min-height: 34px; padding: 0 10px; border: 1px solid transparent; border-radius: 7px; cursor: pointer; font: inherit; font-size: 12px; font-weight: 750; line-height: 1; text-decoration: none; transition: border-color .15s ease, background .15s ease, transform .15s ease; }
        .action-button:hover { transform: translateY(-1px); }
        .action-button svg { flex: 0 0 auto; }
        .action-view { border-color: #bfdbfe; color: #1d4f91; background: #eff6ff; }
        .action-view:hover { border-color: #93c5fd; background: #dbeafe; }
        .action-edit { border-color: #cbd5e1; color: #475569; background: #fff; }
        .action-edit:hover { border-color: #94a3b8; background: #f8fafc; }
        .action-deactivate { border-color: #fecdd3; color: #be123c; background: #fff1f2; }
        .action-deactivate:hover { border-color: #fda4af; background: #ffe4e6; }
        .action-activate { border-color: #bbf7d0; color: #15803d; background: #f0fdf4; }
        .action-activate:hover { border-color: #86efac; background: #dcfce7; }
        .self-account-note { display: inline-flex; align-items: center; gap: 5px; min-height: 34px; padding: 0 9px; border-radius: 7px; color: #64748b; background: #f1f5f9; font-size: 12px; font-weight: 700; }
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

    @if (session('success'))
        <p class="alert-success" role="status">{{ session('success') }}</p>
    @endif

    <div class="page-actions">
        <a class="button button-primary" href="{{ route('users.create') }}">Tambah Pengguna</a>
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
                <button class="button button-primary" type="submit">Cari</button>
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
                        <th>Divisi</th>
                        <th class="actions-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="primary-text">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->employee_number ?: '-' }}</td>
                            <td>{{ $user->position ?: '-' }}</td>
                            <td>{{ $user->division?->name ?: '-' }}</td>
                            <td class="actions-cell">
                                <div class="row-actions">
                                    <a class="action-button action-view" href="{{ route('users.show', $user) }}" aria-label="Lihat detail {{ $user->name }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2.5 12s3.4-5.5 9.5-5.5 9.5 5.5 9.5 5.5-3.4 5.5-9.5 5.5S2.5 12 2.5 12Z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="2.6" stroke="currentColor" stroke-width="1.8"/></svg>
                                        Detail
                                    </a>
                                    <a class="action-button action-edit" href="{{ route('users.edit', $user) }}" aria-label="Edit {{ $user->name }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m14.7 5.3 4 4M4 20l4.7-1 10-10a2.8 2.8 0 0 0-4-4l-10 10L4 20Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/></svg>
                                        Edit
                                    </a>
                                    @if (auth()->user()->is($user) && $user->is_active)
                                        <span class="self-account-note" title="Akun yang sedang digunakan tidak dapat dinonaktifkan">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 4.5 6v5c0 4.7 3.2 8.4 7.5 10 4.3-1.6 7.5-5.3 7.5-10V6L12 3Z" stroke="currentColor" stroke-linejoin="round" stroke-width="1.8"/></svg>
                                            Akun Anda
                                        </span>
                                    @else
                                        <form method="POST" action="{{ route('users.status', $user) }}" data-status-form data-user-name="{{ $user->name }}" data-next-status="{{ $user->is_active ? 'nonaktif' : 'aktif' }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="is_active" value="{{ $user->is_active ? 0 : 1 }}">
                                            <button class="action-button {{ $user->is_active ? 'action-deactivate' : 'action-activate' }}" type="submit">
                                                @if ($user->is_active)
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="m8.8 8.8 6.4 6.4" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/></svg>
                                                    Nonaktifkan
                                                @else
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m5 12 4.2 4.2L19 6.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                                    Aktifkan
                                                @endif
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="6">Tidak ada pengguna yang sesuai dengan pencarian atau filter.</td></tr>
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

@push('scripts')
    <script>
        document.querySelectorAll('[data-status-form]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                const userName = form.dataset.userName;
                const nextStatus = form.dataset.nextStatus;
                const action = nextStatus === 'aktif' ? 'mengaktifkan' : 'menonaktifkan';

                if (!window.confirm(`Yakin ingin ${action} akun ${userName}?`)) {
                    event.preventDefault();
                }
            });
        });
    </script>
@endpush
