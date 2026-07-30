@extends('layouts.dashboard')

@section('title', 'Data Divisi')

@section('content')
    <header class="rs-page-header d-flex flex-column flex-md-row align-items-md-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="rs-page-title h3 mb-1">Data Divisi</h1>
            <p class="rs-page-description text-body-secondary mb-0">Kelola data divisi yang tersedia pada Radar Kediri.</p>
        </div>
        <a class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2" href="{{ route('divisions.create') }}">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            <span>Tambah Divisi</span>
        </a>
    </header>

    <section class="card rs-card shadow-sm mb-4" aria-label="Pencarian divisi">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('divisions.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-lg-8">
                    <label class="form-label" for="search">Pencarian</label>
                    <input
                        class="form-control"
                        id="search"
                        name="search"
                        type="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama atau kode divisi"
                    >
                </div>
                <div class="col-12 col-lg-4">
                    <div class="d-grid d-sm-flex gap-2">
                        <button class="btn btn-primary flex-sm-fill d-inline-flex align-items-center justify-content-center gap-2" type="submit">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            <span>Cari</span>
                        </button>
                        <a class="btn btn-outline-secondary flex-sm-fill d-inline-flex align-items-center justify-content-center gap-2" href="{{ route('divisions.index') }}">
                            <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                            <span>Reset</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="card rs-card shadow-sm overflow-hidden" aria-label="Daftar divisi">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 rs-table">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Nama Divisi</th>
                        <th scope="col">Kode</th>
                        <th scope="col">Jumlah Pengguna</th>
                        <th class="text-center" scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($divisions as $division)
                        <tr>
                            <td class="fw-semibold text-body-emphasis">{{ $division->name }}</td>
                            <td>{{ $division->code ?: '-' }}</td>
                            <td>{{ $division->users_count }}</td>
                            <td class="text-center">
                                <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                                    <a
                                        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                                        href="{{ route('divisions.show', $division) }}"
                                        data-testid="division-detail-link"
                                        aria-label="Lihat detail {{ $division->name }}"
                                    >
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                        <span>Detail</span>
                                    </a>
                                    <a
                                        class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-1"
                                        href="{{ route('divisions.edit', $division) }}"
                                        data-testid="division-edit-link"
                                        aria-label="Edit {{ $division->name }}"
                                    >
                                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                        <span>Edit</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="rs-empty-state text-center text-body-secondary py-5" colspan="4">
                                <i class="fa-solid fa-building-circle-xmark d-block fs-3 mb-2" aria-hidden="true"></i>
                                @if (request()->filled('search'))
                                    Data divisi yang dicari tidak ditemukan.
                                @else
                                    <span class="d-block">Belum ada data divisi.</span>
                                    <span class="d-block">Silakan tambahkan divisi baru.</span>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
