<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light">
        <title>@yield('title', 'Dashboard') - Radarsurat</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="rs-body">
        @php($currentUser = auth()->user())

        <nav class="navbar navbar-dark sticky-top rs-navbar" aria-label="Navigasi utama">
            <div class="container-fluid flex-nowrap gap-3">
                <div class="d-flex align-items-center gap-2 gap-sm-3 rs-navbar-start">
                    <button
                        class="btn rs-menu-toggle d-inline-flex d-lg-none align-items-center justify-content-center"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#rsMobileSidebar"
                        aria-controls="rsMobileSidebar"
                        aria-label="Buka menu navigasi"
                    >
                        <i class="fa-solid fa-bars" aria-hidden="true"></i>
                    </button>

                    <a class="navbar-brand rs-brand mb-0" href="{{ route('dashboard') }}">Radarsurat</a>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2 gap-sm-3 rs-navbar-end">
                    <span class="rs-user-name" title="{{ $currentUser?->name }}">{{ $currentUser?->name }}</span>

                    <form class="rs-logout-form" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-light rs-logout-button d-inline-flex align-items-center gap-2" type="submit">
                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <div class="offcanvas offcanvas-start text-bg-primary rs-offcanvas" tabindex="-1" id="rsMobileSidebar" aria-labelledby="rsMobileSidebarLabel">
            <div class="offcanvas-header">
                <h2 class="offcanvas-title h5 mb-0" id="rsMobileSidebarLabel">Menu Radarsurat</h2>
                <button class="btn-close btn-close-white" type="button" data-bs-dismiss="offcanvas" aria-label="Tutup menu navigasi"></button>
            </div>
            <div class="offcanvas-body p-3">
                <nav aria-label="Navigasi dashboard mobile">
                    <ul class="nav nav-pills flex-column gap-1 rs-sidebar-nav">
                        <li class="nav-item">
                            <a
                                class="nav-link rs-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                                href="{{ route('dashboard') }}"
                                @if (request()->routeIs('dashboard')) aria-current="page" @endif
                            >
                                <i class="fa-solid fa-house rs-nav-icon" aria-hidden="true"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a
                                class="nav-link rs-nav-link {{ request()->routeIs('incoming-letters.*') ? 'active' : '' }}"
                                href="{{ route('incoming-letters.index') }}"
                                data-testid="incoming-letter-menu-mobile"
                                @if (request()->routeIs('incoming-letters.*')) aria-current="page" @endif
                            >
                                <i class="fa-solid fa-envelope-open-text rs-nav-icon" aria-hidden="true"></i>
                                <span>Surat Masuk</span>
                            </a>
                        </li>

                        @if ($currentUser?->role?->slug === 'admin_surat')
                            <li class="nav-item">
                                <a
                                    class="nav-link rs-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                                    href="{{ route('users.index') }}"
                                    @if (request()->routeIs('users.*')) aria-current="page" @endif
                                >
                                    <i class="fa-solid fa-users rs-nav-icon" aria-hidden="true"></i>
                                    <span>Data Pengguna</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link rs-nav-link {{ request()->routeIs('divisions.*') ? 'active' : '' }}"
                                    href="{{ route('divisions.index') }}"
                                    data-testid="division-menu-mobile"
                                    @if (request()->routeIs('divisions.*')) aria-current="page" @endif
                                >
                                    <i class="fa-solid fa-building rs-nav-icon" aria-hidden="true"></i>
                                    <span>Data Divisi</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>

        <div class="rs-app-shell d-flex">
            <aside class="rs-sidebar d-none d-lg-flex flex-column" aria-label="Navigasi dashboard">
                <nav class="p-3">
                    <ul class="nav nav-pills flex-column gap-1 rs-sidebar-nav">
                        <li class="nav-item">
                            <a
                                class="nav-link rs-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                                href="{{ route('dashboard') }}"
                                @if (request()->routeIs('dashboard')) aria-current="page" @endif
                            >
                                <i class="fa-solid fa-house rs-nav-icon" aria-hidden="true"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a
                                class="nav-link rs-nav-link {{ request()->routeIs('incoming-letters.*') ? 'active' : '' }}"
                                href="{{ route('incoming-letters.index') }}"
                                data-testid="incoming-letter-menu-desktop"
                                @if (request()->routeIs('incoming-letters.*')) aria-current="page" @endif
                            >
                                <i class="fa-solid fa-envelope-open-text rs-nav-icon" aria-hidden="true"></i>
                                <span>Surat Masuk</span>
                            </a>
                        </li>

                        @if ($currentUser?->role?->slug === 'admin_surat')
                            <li class="nav-item">
                                <a
                                    class="nav-link rs-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                                    href="{{ route('users.index') }}"
                                    @if (request()->routeIs('users.*')) aria-current="page" @endif
                                >
                                    <i class="fa-solid fa-users rs-nav-icon" aria-hidden="true"></i>
                                    <span>Data Pengguna</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link rs-nav-link {{ request()->routeIs('divisions.*') ? 'active' : '' }}"
                                    href="{{ route('divisions.index') }}"
                                    data-testid="division-menu-desktop"
                                    @if (request()->routeIs('divisions.*')) aria-current="page" @endif
                                >
                                    <i class="fa-solid fa-building rs-nav-icon" aria-hidden="true"></i>
                                    <span>Data Divisi</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </nav>
            </aside>

            <main class="rs-main flex-grow-1">
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
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
