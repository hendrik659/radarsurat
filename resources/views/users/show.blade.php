@extends('layouts.dashboard')

@section('title', 'Detail Pengguna')

@section('content')
    <header class="rs-page-header mb-4">
        <h1 class="rs-page-title h3 mb-1">Detail Pengguna</h1>
        <p class="rs-page-description text-body-secondary mb-0">Informasi akun {{ $user->name }}.</p>
    </header>

    <section class="card rs-card rs-detail-card shadow-sm">
        <div class="card-body p-3 p-md-4">
            <dl class="row g-3 mb-0 rs-detail-list">
                <div class="col-12 col-md-6 rs-detail-item border-bottom pb-3">
                    <dt class="rs-detail-label small text-body-secondary">Nama</dt>
                    <dd>{{ $user->name }}</dd>
                </div>
                <div class="col-12 col-md-6 rs-detail-item border-bottom pb-3">
                    <dt class="rs-detail-label small text-body-secondary">Email</dt>
                    <dd>{{ $user->email }}</dd>
                </div>
                <div class="col-12 col-md-6 rs-detail-item border-bottom pb-3">
                    <dt class="rs-detail-label small text-body-secondary">Telepon</dt>
                    <dd>{{ $user->phone ?: '-' }}</dd>
                </div>
                <div class="col-12 col-md-6 rs-detail-item border-bottom pb-3">
                    <dt class="rs-detail-label small text-body-secondary">Nomor pegawai</dt>
                    <dd>{{ $user->employee_number ?: '-' }}</dd>
                </div>
                <div class="col-12 col-md-6 rs-detail-item border-bottom pb-3">
                    <dt class="rs-detail-label small text-body-secondary">Jabatan</dt>
                    <dd>{{ $user->position ?: '-' }}</dd>
                </div>
                <div class="col-12 col-md-6 rs-detail-item border-bottom pb-3">
                    <dt class="rs-detail-label small text-body-secondary">Role</dt>
                    <dd>{{ $user->role?->name ?? '-' }}</dd>
                </div>
                <div class="col-12 col-md-6 rs-detail-item border-bottom pb-3">
                    <dt class="rs-detail-label small text-body-secondary">Divisi</dt>
                    <dd>{{ $user->division?->name ?? '-' }}</dd>
                </div>
                <div class="col-12 col-md-6 rs-detail-item border-bottom pb-3">
                    <dt class="rs-detail-label small text-body-secondary">Status</dt>
                    <dd>
                        <span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ $user->is_active ? 'Aktif' : 'Tidak aktif' }}
                        </span>
                    </dd>
                </div>
            </dl>

            <div class="d-flex flex-wrap gap-2 mt-4">
                <a class="btn btn-outline-warning d-inline-flex align-items-center gap-2" href="{{ route('users.edit', $user) }}">
                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                    <span>Edit</span>
                </a>
                <a class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" href="{{ route('users.index') }}">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    <span>Kembali ke daftar</span>
                </a>
            </div>
        </div>
    </section>
@endsection
