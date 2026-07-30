@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
    <header class="rs-page-header mb-4">
        <h1 class="rs-page-title h2 mb-1">Dashboard</h1>
        <p class="rs-page-description mb-0">Ringkasan operasional Radarsurat.</p>
    </header>

    @if (auth()->user()?->role?->slug === 'admin_surat')
        <section aria-label="Ringkasan manajemen pengguna">
            <div class="row g-3 g-xl-4">
                <div class="col-12 col-md-6 col-xl-4">
                    <article class="card h-100 border-0 shadow-sm rs-summary-card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="rs-summary-icon d-inline-flex align-items-center justify-content-center" aria-hidden="true">
                                <i class="fa-solid fa-users"></i>
                            </span>
                            <div>
                                <span class="rs-summary-label d-block">Total Pengguna</span>
                                <strong class="rs-summary-value d-block">{{ $totalUsers }}</strong>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="col-12 col-md-6 col-xl-4">
                    <article class="card h-100 border-0 shadow-sm rs-summary-card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="rs-summary-icon d-inline-flex align-items-center justify-content-center" aria-hidden="true">
                                <i class="fa-solid fa-user-check"></i>
                            </span>
                            <div>
                                <span class="rs-summary-label d-block">Pengguna Aktif</span>
                                <strong class="rs-summary-value d-block">{{ $activeUsers }}</strong>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="col-12 col-md-6 col-xl-4">
                    <article class="card h-100 border-0 shadow-sm rs-summary-card">
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="rs-summary-icon d-inline-flex align-items-center justify-content-center" aria-hidden="true">
                                <i class="fa-solid fa-building"></i>
                            </span>
                            <div>
                                <span class="rs-summary-label d-block">Total Divisi</span>
                                <strong class="rs-summary-value d-block">{{ $totalDivisions }}</strong>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <a class="btn btn-primary d-inline-flex align-items-center gap-2 mt-4" href="{{ route('users.index') }}">
                <i class="fa-solid fa-users" aria-hidden="true"></i>
                <span>Kelola Pengguna</span>
            </a>
        </section>
    @endif
@endsection
