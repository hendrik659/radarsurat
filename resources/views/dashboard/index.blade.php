@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
    <div class="page-heading">
        <h1>Dashboard</h1>
        <p>Ringkasan operasional Radarsurat.</p>
    </div>

    @if (auth()->user()?->role?->slug === 'admin_surat')
        <section class="summary-grid" aria-label="Ringkasan manajemen pengguna">
            <article class="summary-card"><span class="summary-label">Total Pengguna</span><strong>{{ $totalUsers }}</strong></article>
            <article class="summary-card"><span class="summary-label">Pengguna Aktif</span><strong>{{ $activeUsers }}</strong></article>
            <article class="summary-card"><span class="summary-label">Total Divisi</span><strong>{{ $totalDivisions }}</strong></article>
        </section>
        <a class="primary-button" href="{{ route('users.index') }}">Kelola Pengguna</a>
    @endif
@endsection
