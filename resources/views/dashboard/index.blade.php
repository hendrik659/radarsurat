@extends('layouts.dashboard')

@section('title', $isAdminDashboard ? 'Dashboard Admin Surat' : 'Dashboard')
@section('header-title', $isAdminDashboard ? 'Dashboard Admin Surat' : 'Dashboard')
@section('header-subtitle', $isAdminDashboard ? 'Ringkasan administrasi dan aktivitas Radarsurat' : '')

@section('content')
    @if (! $isAdminDashboard)
        <header class="rs-page-header mb-4">
            <h1 class="rs-page-title h2 mb-1">Dashboard</h1>
            <p class="rs-page-description mb-0">Dashboard untuk peran Anda akan tersedia pada fase berikutnya.</p>
        </header>
    @else
        @php
            $statusLabels = [
                'baru_diterima' => 'Baru Diterima',
                'menunggu_pemeriksaan' => 'Menunggu Pemeriksaan',
                'selesai' => 'Selesai',
            ];
            $statusBadgeClasses = [
                'baru_diterima' => 'text-bg-info',
                'menunggu_pemeriksaan' => 'text-bg-warning',
                'selesai' => 'text-bg-success',
            ];
            $quickAccessItems = [
                [
                    'label' => 'Tambah Surat Masuk',
                    'icon' => 'fa-square-plus',
                    'route' => route('incoming-letters.create'),
                ],
                [
                    'label' => 'Surat Masuk',
                    'icon' => 'fa-envelope-open-text',
                    'route' => route('incoming-letters.index'),
                ],
                [
                    'label' => 'Surat Keluar',
                    'icon' => 'fa-paper-plane',
                    'route' => route('outgoing-letters.index'),
                ],
                [
                    'label' => 'Laporan Surat Masuk',
                    'icon' => 'fa-chart-line',
                    'route' => route('reports.incoming-letters.index'),
                ],
                [
                    'label' => 'Laporan Surat Keluar',
                    'icon' => 'fa-chart-column',
                    'route' => route('reports.outgoing-letters.index'),
                ],
                [
                    'label' => 'Users',
                    'icon' => 'fa-users',
                    'route' => route('users.index'),
                ],
                [
                    'label' => 'Divisions',
                    'icon' => 'fa-building',
                    'route' => route('divisions.index'),
                ],
            ];
        @endphp

        <section class="rs-dashboard-banner d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4" aria-labelledby="dashboardBannerTitle" data-testid="dashboard-admin-banner">
            <div class="d-flex align-items-center gap-3">
                <span class="rs-dashboard-banner-icon d-inline-flex align-items-center justify-content-center" aria-hidden="true">
                    <i class="fa-solid fa-table-cells-large"></i>
                </span>
                <div>
                    <h2 class="h4 mb-1" id="dashboardBannerTitle">Dashboard Admin Surat</h2>
                    <p class="mb-0">Ringkasan administrasi surat internal.</p>
                </div>
            </div>
            <time class="rs-dashboard-date" datetime="{{ now()->toDateString() }}">
                <i class="fa-regular fa-calendar me-2" aria-hidden="true"></i>{{ $todayLabel }}
            </time>
        </section>

        <section class="mb-4" aria-labelledby="dashboardStatisticsTitle">
            <h2 class="visually-hidden" id="dashboardStatisticsTitle">Statistik utama surat</h2>
            <div class="row g-3">
                @foreach ([
                    ['Total Surat Masuk', $totalIncomingLetters, 'fa-envelope-open-text', 'primary'],
                    ['Baru Diterima', $newIncomingLetters, 'fa-inbox', 'info'],
                    ['Menunggu Pemeriksaan', $waitingReviewIncomingLetters, 'fa-hourglass-half', 'warning'],
                    ['Total Surat Keluar', $totalOutgoingLetters, 'fa-paper-plane', 'success'],
                ] as [$label, $value, $icon, $accent])
                    <div class="col-12 col-sm-6 col-xl-3">
                        <article class="card rs-dashboard-stat h-100 shadow-sm" data-testid="dashboard-kpi">
                            <div class="card-body d-flex align-items-center justify-content-between gap-3">
                                <div>
                                    <div class="rs-dashboard-stat-label">{{ $label }}</div>
                                    <strong class="rs-dashboard-stat-value" data-kpi-label="{{ $label }}">{{ $value }}</strong>
                                </div>
                                <span class="rs-dashboard-stat-icon rs-dashboard-stat-icon-{{ $accent }} d-inline-flex align-items-center justify-content-center" aria-hidden="true">
                                    <i class="fa-solid {{ $icon }}"></i>
                                </span>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="mb-4" aria-labelledby="quickAccessTitle">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h5 mb-0" id="quickAccessTitle">Akses Cepat</h2>
            </div>
            <div class="rs-quick-access-grid" data-testid="dashboard-quick-access-grid">
                @foreach ($quickAccessItems as $item)
                    <div class="rs-quick-access-item">
                        <a class="rs-quick-card d-flex h-100 align-items-center gap-3 text-decoration-none" href="{{ $item['route'] }}" data-testid="dashboard-quick-access">
                            <span class="rs-quick-card-icon d-inline-flex align-items-center justify-content-center" aria-hidden="true">
                                <i class="fa-solid {{ $item['icon'] }}"></i>
                            </span>
                            <span class="rs-quick-card-copy">
                                <strong class="rs-quick-card-label d-block">{{ $item['label'] }}</strong>
                            </span>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="card rs-dashboard-panel rs-dashboard-chart-panel shadow-sm mb-4" aria-labelledby="letterTrendTitle" data-testid="dashboard-trend-section">
            <div class="card-body p-3 p-md-4">
                <div class="mb-3">
                    <h2 class="h5 mb-1" id="letterTrendTitle">Tren Surat (6 Bulan Terakhir)</h2>
                    <p class="small text-body-secondary mb-0" id="letterTrendDescription">Perbandingan jumlah Surat Masuk berdasarkan tanggal diterima dan Surat Keluar berdasarkan tanggal surat.</p>
                </div>
                <div class="rs-dashboard-chart" role="group" aria-labelledby="letterTrendTitle" aria-describedby="letterTrendDescription">
                    <canvas
                        data-dashboard-trend-chart
                        data-chart="{{ json_encode([
                            'labels' => $sixMonthLabels,
                            'incoming' => $sixMonthIncomingTrend,
                            'outgoing' => $sixMonthOutgoingTrend,
                        ]) }}"
                        role="img"
                        aria-label="Diagram garis tren Surat Masuk dan Surat Keluar selama enam bulan terakhir"
                    >
                        Diagram tren Surat Masuk dan Surat Keluar selama enam bulan terakhir.
                    </canvas>
                </div>
            </div>
        </section>

        <div class="row g-4 mb-4">
            <div class="col-12 col-xl-6">
                <section class="card rs-dashboard-panel h-100 shadow-sm" aria-labelledby="recentIncomingTitle">
                    <div class="card-header bg-body d-flex align-items-center justify-content-between gap-3 py-3">
                        <h2 class="h5 mb-0" id="recentIncomingTitle">Surat Masuk Terbaru</h2>
                        <a class="small fw-semibold text-decoration-none" href="{{ route('incoming-letters.index') }}">Lihat Semua <span aria-hidden="true">→</span></a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 rs-dashboard-table">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Perihal</th>
                                    <th scope="col">Pengirim</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentIncomingLetters as $letter)
                                    <tr data-testid="dashboard-recent-incoming-row">
                                        <td class="fw-semibold text-body-emphasis">{{ $letter->subject }}</td>
                                        <td>{{ $letter->sender_name }}</td>
                                        <td class="text-nowrap">{{ $letter->received_date?->format('d-m-Y') ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ $statusBadgeClasses[$letter->status] ?? 'text-bg-secondary' }}">
                                                {{ $statusLabels[$letter->status] ?? $letter->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="rs-dashboard-empty text-center text-body-secondary py-4" colspan="4">Belum ada Surat Masuk.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div class="col-12 col-xl-6">
                <section class="card rs-dashboard-panel h-100 shadow-sm" aria-labelledby="recentOutgoingTitle">
                    <div class="card-header bg-body d-flex align-items-center justify-content-between gap-3 py-3">
                        <h2 class="h5 mb-0" id="recentOutgoingTitle">Surat Keluar Terbaru</h2>
                        <a class="small fw-semibold text-decoration-none" href="{{ route('outgoing-letters.index') }}">Lihat Semua <span aria-hidden="true">→</span></a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 rs-dashboard-table">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Perihal</th>
                                    <th scope="col">Tujuan</th>
                                    <th scope="col">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentOutgoingLetters as $letter)
                                    <tr data-testid="dashboard-recent-outgoing-row">
                                        <td>
                                            <span class="fw-semibold text-body-emphasis d-block">{{ $letter->subject }}</span>
                                            <small class="text-body-secondary">{{ $letter->reference_code }}</small>
                                        </td>
                                        <td>{{ $letter->recipient_name }}</td>
                                        <td class="text-nowrap">{{ $letter->letter_date?->format('d-m-Y') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="rs-dashboard-empty text-center text-body-secondary py-4" colspan="3">Belum ada Surat Keluar.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-6">
                <section class="card rs-dashboard-panel h-100 shadow-sm" aria-labelledby="recentActivitiesTitle">
                    <div class="card-header bg-body py-3">
                        <h2 class="h5 mb-0" id="recentActivitiesTitle">Aktivitas Terbaru</h2>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="rs-dashboard-timeline">
                            @forelse ($recentActivities as $activity)
                                <article class="rs-dashboard-activity position-relative ps-4 pb-4" data-testid="dashboard-activity">
                                    <span class="rs-dashboard-activity-marker" aria-hidden="true"></span>
                                    <div class="d-flex flex-column flex-sm-row justify-content-between gap-1 mb-1">
                                        <strong>{{ $activity['activity'] }}</strong>
                                        <time class="small text-body-secondary text-nowrap" datetime="{{ $activity['created_at']->toIso8601String() }}">
                                            {{ $activity['created_at']->locale('id')->diffForHumans() }}
                                        </time>
                                    </div>
                                    <div class="small text-body-secondary">
                                        {{ $activity['reference'] }} · {{ $activity['subject'] }}
                                    </div>
                                    <div class="small mt-1">Oleh {{ $activity['actor'] }}</div>
                                </article>
                            @empty
                                <div class="rs-dashboard-empty text-center text-body-secondary py-4">Belum ada aktivitas surat.</div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-12 col-xl-6">
                <section class="card rs-dashboard-panel h-100 shadow-sm" aria-labelledby="masterDataTitle">
                    <div class="card-header bg-body py-3">
                        <h2 class="h5 mb-0" id="masterDataTitle">Data Master</h2>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="row g-3">
                            @foreach ([
                                ['Users Aktif', $activeUsers, 'fa-user-check'],
                                ['Users Nonaktif', $inactiveUsers, 'fa-user-slash'],
                                ['Divisi Aktif', $activeDivisions, 'fa-building-circle-check'],
                                ['Total Users', $totalUsers, 'fa-users'],
                            ] as [$label, $value, $icon])
                                <div class="col-12 col-sm-6">
                                    <article class="rs-master-stat h-100 d-flex align-items-center gap-3">
                                        <span class="rs-master-stat-icon d-inline-flex align-items-center justify-content-center" aria-hidden="true"><i class="fa-solid {{ $icon }}"></i></span>
                                        <div><span class="small text-body-secondary d-block">{{ $label }}</span><strong class="fs-4" data-master-label="{{ $label }}">{{ $value }}</strong></div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                        <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
                            <a class="btn btn-outline-primary" href="{{ route('users.index') }}">Kelola Pengguna</a>
                            <a class="btn btn-outline-primary" href="{{ route('divisions.index') }}">Kelola Divisi</a>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    @endif
@endsection
