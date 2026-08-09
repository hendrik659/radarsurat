<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light">
        <title>@yield('title', 'Dashboard') - Radarsurat</title>
        <script>
            try {
                if (window.matchMedia('(min-width: 992px)').matches && localStorage.getItem('rs-sidebar-state') === 'collapsed') {
                    document.documentElement.classList.add('rs-sidebar-collapsed');
                }
            } catch (error) {
                // The layout safely defaults to an expanded sidebar.
            }
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="rs-body">
        @php
            $currentUser = auth()->user();
            $reportRoles = ['admin_surat', 'pimpinan', 'ketua_divisi', 'anggota_divisi'];
            $canViewReports = $currentUser?->is_active
                && in_array($currentUser?->role?->slug, $reportRoles, true)
                && (! in_array($currentUser?->role?->slug, ['ketua_divisi', 'anggota_divisi'], true) || $currentUser?->division_id !== null);
            $reportsActive = request()->routeIs('reports.*');
            $currentUserRoleLabel = $currentUser?->role?->name
                ?? \Illuminate\Support\Str::headline($currentUser?->role?->slug ?? 'Pengguna');
            $currentUserInitials = \Illuminate\Support\Str::of($currentUser?->name ?? 'Pengguna')
                ->trim()
                ->explode(' ')
                ->filter()
                ->take(2)
                ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
                ->implode('');
        @endphp

        <aside
            class="rs-sidebar d-none d-lg-flex flex-column"
            id="rsDesktopSidebar"
            data-desktop-sidebar
            data-testid="desktop-sidebar"
            aria-label="Sidebar utama"
        >
            <a
                class="rs-sidebar-brand d-flex align-items-center gap-3 text-decoration-none"
                href="{{ route('dashboard') }}"
                data-sidebar-tooltip="Radarsurat"
                aria-label="Radarsurat"
            >
                <span class="rs-sidebar-brand-icon d-inline-flex align-items-center justify-content-center" aria-hidden="true">
                    <i class="fa-solid fa-envelope"></i>
                </span>
                <span class="rs-sidebar-label">Radarsurat</span>
            </a>

            @include('layouts.partials.sidebar-navigation', [
                'mode' => 'desktop',
                'reportMenuId' => 'rsDesktopReportsMenu',
            ])

            @include('layouts.partials.sidebar-profile', ['mode' => 'desktop'])
        </aside>

        <div class="offcanvas offcanvas-start text-bg-primary rs-offcanvas" tabindex="-1" id="rsMobileSidebar" aria-labelledby="rsMobileSidebarLabel">
            <div class="offcanvas-header">
                <h2 class="offcanvas-title h5 mb-0" id="rsMobileSidebarLabel">Menu Radarsurat</h2>
                <button class="btn-close btn-close-white" type="button" data-bs-dismiss="offcanvas" aria-label="Tutup menu navigasi"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column p-3">
                @include('layouts.partials.sidebar-navigation', [
                    'mode' => 'mobile',
                    'reportMenuId' => 'rsMobileReportsMenu',
                ])

                @include('layouts.partials.sidebar-profile', ['mode' => 'mobile'])
            </div>
        </div>

        <div class="rs-main-wrapper" data-testid="dashboard-main-wrapper">
            <header class="navbar navbar-light rs-navbar" data-testid="dashboard-global-header">
                <div class="container-fluid flex-nowrap gap-3">
                    <div class="d-flex align-items-center gap-2 gap-sm-3 rs-navbar-start">
                        <button
                            class="btn rs-menu-toggle d-none d-lg-inline-flex align-items-center justify-content-center"
                            type="button"
                            data-desktop-sidebar-toggle
                            data-testid="desktop-sidebar-toggle"
                            aria-controls="rsDesktopSidebar"
                            aria-expanded="true"
                            aria-label="Ciutkan sidebar"
                        >
                            <i class="fa-solid fa-bars" aria-hidden="true"></i>
                        </button>

                        <button
                            class="btn rs-menu-toggle d-inline-flex d-lg-none align-items-center justify-content-center"
                            type="button"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#rsMobileSidebar"
                            data-testid="mobile-sidebar-toggle"
                            aria-controls="rsMobileSidebar"
                            aria-expanded="false"
                            aria-label="Buka menu navigasi"
                        >
                            <i class="fa-solid fa-bars" aria-hidden="true"></i>
                        </button>

                        <div class="rs-global-heading">
                            <div class="rs-global-title">@yield('header-title', 'Radarsurat')</div>
                            @hasSection('header-subtitle')
                                <div class="rs-global-subtitle">@yield('header-subtitle')</div>
                            @endif
                        </div>
                    </div>
                </div>
            </header>

            <main class="rs-main d-flex flex-column">
                @if (session('success'))
                    <div class="alert alert-success rs-flash-alert" role="status">
                        <i class="fa-solid fa-circle-check me-2" aria-hidden="true"></i>{{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger rs-flash-alert" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2" aria-hidden="true"></i>{{ session('error') }}
                    </div>
                @endif

                @yield('content')

                @if (request()->routeIs('dashboard'))
                    <footer class="rs-footer d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mt-auto pt-5" aria-label="Informasi aplikasi">
                        <span>© {{ now()->year }} Radarsurat - Radar Kediri.</span>
                    </footer>
                @endif
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
