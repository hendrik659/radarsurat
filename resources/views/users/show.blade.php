@extends('layouts.dashboard')

@section('title', 'Detail Pengguna')

@push('styles')
    <style>
        .detail-card { max-width: 820px; padding: 24px; border: 1px solid var(--line); border-radius: 14px; background: #fff; box-shadow: 0 8px 26px rgba(31, 41, 55, .05); }
        .detail-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; margin: 0; }
        .detail-list div { padding-bottom: 14px; border-bottom: 1px solid #edf0f4; }
        .detail-list .detail-wide { grid-column: 1 / -1; }
        .detail-list dt { color: var(--muted); font-size: 13px; font-weight: 700; }
        .detail-list dd { margin: 6px 0 0; font-weight: 650; }
        .detail-actions { display: flex; align-items: center; gap: 10px; margin-top: 24px; }
        .detail-action { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 42px; padding: 0 15px; border: 1px solid transparent; border-radius: 8px; font-size: 14px; font-weight: 750; line-height: 1; text-decoration: none; transition: border-color .15s ease, background .15s ease, box-shadow .15s ease, transform .15s ease; }
        .detail-action:hover { transform: translateY(-1px); }
        .detail-action:focus-visible { outline: 3px solid rgba(49, 130, 206, .2); outline-offset: 2px; }
        .detail-action-primary { color: #fff; background: var(--primary); }
        .detail-action-primary:hover { color: #fff; background: var(--primary-dark); box-shadow: 0 6px 14px rgba(44, 82, 130, .2); }
        .detail-action-secondary { border-color: #cbd5e1; color: #334155; background: #fff; }
        .detail-action-secondary:hover { border-color: #94a3b8; color: var(--primary-dark); background: #f8fafc; }
        .detail-action svg { flex: 0 0 auto; }
        .success-message { max-width: 820px; padding: 12px 15px; border: 1px solid #bbebca; border-radius: 9px; color: #166534; background: #effbf3; font-weight: 650; }
        @media (max-width: 620px) { .detail-list { grid-template-columns: 1fr; } }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <h1>Detail Pengguna</h1>
        <p>Informasi akun {{ $user->name }}.</p>
    </div>

    @if (session('success'))
        <p class="success-message" role="status">{{ session('success') }}</p>
    @endif

    <section class="detail-card">
        <dl class="detail-list">
            <div><dt>Nama</dt><dd>{{ $user->name }}</dd></div>
            <div><dt>Email</dt><dd>{{ $user->email }}</dd></div>
            <div><dt>Telepon</dt><dd>{{ $user->phone ?: '-' }}</dd></div>
            <div><dt>Nomor pegawai</dt><dd>{{ $user->employee_number ?: '-' }}</dd></div>
            <div><dt>Jabatan</dt><dd>{{ $user->position ?: '-' }}</dd></div>
            <div><dt>Role</dt><dd>{{ $user->role?->name ?? '-' }}</dd></div>
            <div><dt>Divisi</dt><dd>{{ $user->division?->name ?? '-' }}</dd></div>
            <div class="detail-wide"><dt>Status</dt><dd>{{ $user->is_active ? 'Aktif' : 'Tidak aktif' }}</dd></div>
        </dl>
        <div class="detail-actions">
            <a class="detail-action detail-action-primary" href="{{ route('users.edit', $user) }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m14.7 5.3 4 4M4 20l4.7-1 10-10a2.8 2.8 0 0 0-4-4l-10 10L4 20Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/></svg>
                Edit
            </a>
            <a class="detail-action detail-action-secondary" href="{{ route('users.index') }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m10 6-6 6 6 6M4 12h16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/></svg>
                Kembali ke daftar
            </a>
        </div>
    </section>
@endsection
