@php($isDesktopSidebar = $mode === 'desktop')

<nav class="rs-sidebar-navigation flex-grow-1 {{ $isDesktopSidebar ? 'p-3' : 'p-0' }}" aria-label="{{ $isDesktopSidebar ? 'Navigasi dashboard' : 'Navigasi dashboard mobile' }}">
    <ul class="nav nav-pills flex-column gap-1 rs-sidebar-nav">
        <li class="nav-item">
            <a
                class="nav-link rs-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                href="{{ route('dashboard') }}"
                @if ($isDesktopSidebar) data-sidebar-tooltip="Dashboard" aria-label="Dashboard" @endif
                @if (request()->routeIs('dashboard')) aria-current="page" @endif
            >
                <i class="fa-solid fa-house rs-nav-icon" aria-hidden="true"></i>
                <span class="rs-sidebar-label">Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a
                class="nav-link rs-nav-link {{ request()->routeIs('incoming-letters.*') ? 'active' : '' }}"
                href="{{ route('incoming-letters.index') }}"
                data-testid="incoming-letter-menu-{{ $mode }}"
                @if ($isDesktopSidebar) data-sidebar-tooltip="Surat Masuk" aria-label="Surat Masuk" @endif
                @if (request()->routeIs('incoming-letters.*')) aria-current="page" @endif
            >
                <i class="fa-solid fa-envelope-open-text rs-nav-icon" aria-hidden="true"></i>
                <span class="rs-sidebar-label">Surat Masuk</span>
            </a>
        </li>

        <li class="nav-item">
            <a
                class="nav-link rs-nav-link {{ request()->routeIs('outgoing-letters.*') ? 'active' : '' }}"
                href="{{ route('outgoing-letters.index') }}"
                data-testid="outgoing-letter-menu-{{ $mode }}"
                @if ($isDesktopSidebar) data-sidebar-tooltip="Surat Keluar" aria-label="Surat Keluar" @endif
                @if (request()->routeIs('outgoing-letters.*')) aria-current="page" @endif
            >
                <i class="fa-solid fa-paper-plane rs-nav-icon" aria-hidden="true"></i>
                <span class="rs-sidebar-label">Surat Keluar</span>
            </a>
        </li>

        @if ($currentUser?->role?->slug === 'admin_surat')
            <li class="nav-item">
                <a
                    class="nav-link rs-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                    href="{{ route('users.index') }}"
                    @if ($isDesktopSidebar) data-sidebar-tooltip="Users" aria-label="Users" @endif
                    @if (request()->routeIs('users.*')) aria-current="page" @endif
                >
                    <i class="fa-solid fa-users rs-nav-icon" aria-hidden="true"></i>
                    <span class="rs-sidebar-label">Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a
                    class="nav-link rs-nav-link {{ request()->routeIs('divisions.*') ? 'active' : '' }}"
                    href="{{ route('divisions.index') }}"
                    data-testid="division-menu-{{ $mode }}"
                    @if ($isDesktopSidebar) data-sidebar-tooltip="Divisions" aria-label="Divisions" @endif
                    @if (request()->routeIs('divisions.*')) aria-current="page" @endif
                >
                    <i class="fa-solid fa-building rs-nav-icon" aria-hidden="true"></i>
                    <span class="rs-sidebar-label">Divisions</span>
                </a>
            </li>
        @endif

        @if ($canViewReports)
            <li class="nav-item" data-testid="reports-menu-{{ $mode }}">
                <button
                    class="nav-link rs-nav-link rs-nav-collapse-button {{ $reportsActive ? 'active' : '' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#{{ $reportMenuId }}"
                    data-sidebar-report-toggle
                    @if ($isDesktopSidebar) data-sidebar-tooltip="Laporan" aria-label="Laporan" @endif
                    aria-expanded="{{ $reportsActive ? 'true' : 'false' }}"
                    aria-controls="{{ $reportMenuId }}"
                >
                    <i class="fa-solid fa-chart-column rs-nav-icon" aria-hidden="true"></i>
                    <span class="rs-sidebar-label">Laporan</span>
                    <i class="fa-solid fa-chevron-down rs-nav-collapse-icon" aria-hidden="true"></i>
                </button>
                <div class="collapse {{ $reportsActive ? 'show' : '' }}" id="{{ $reportMenuId }}">
                    <ul class="nav flex-column rs-nav-submenu">
                        <li class="nav-item">
                            <a class="nav-link rs-nav-link rs-nav-sublink {{ request()->routeIs('reports.incoming-letters.*') ? 'active' : '' }}" href="{{ route('reports.incoming-letters.index') }}"
                                data-testid="incoming-report-menu-{{ $mode }}"
                                @if (request()->routeIs('reports.incoming-letters.*')) aria-current="page" @endif
                            >
                                <i class="fa-solid fa-envelope-open-text rs-nav-icon" aria-hidden="true"></i>
                                <span>Surat Masuk</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link rs-nav-link rs-nav-sublink {{ request()->routeIs('reports.outgoing-letters.*') ? 'active' : '' }}" href="{{ route('reports.outgoing-letters.index') }}"
                                data-testid="outgoing-report-menu-{{ $mode }}"
                                @if (request()->routeIs('reports.outgoing-letters.*')) aria-current="page" @endif
                            >
                                <i class="fa-solid fa-paper-plane rs-nav-icon" aria-hidden="true"></i>
                                <span>Surat Keluar</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif
    </ul>
</nav>
