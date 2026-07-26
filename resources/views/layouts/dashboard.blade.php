<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light">
        <title>@yield('title', 'Dashboard') - Radarsurat</title>
        <style>
            :root { --primary: #3182CE; --primary-dark: #2C5282; --navbar: #109BDA; --ink: #1f2937; --muted: #6b7280; --line: #e2e8f0; --canvas: #f7f9fc; --navbar-height: 68px; --sidebar-width: 260px; }
            * { box-sizing: border-box; }
            html, body { max-width: 100%; overflow-x: hidden; }
            body { min-width: 320px; min-height: 100vh; margin: 0; color: var(--ink); background: var(--canvas); font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
            button { font: inherit; }
            .topbar { position: sticky; top: 0; z-index: 40; display: flex; align-items: center; justify-content: space-between; min-height: var(--navbar-height); padding: 0 28px; color: #fff; background: var(--navbar); box-shadow: 0 2px 12px rgba(31, 41, 55, .12); }
            .topbar-start, .topbar-end { display: flex; align-items: center; min-width: 0; }
            .topbar-start { gap: 13px; } .topbar-end { gap: 18px; }
            .brand { color: #fff; font-size: 19px; font-weight: 800; letter-spacing: -.025em; text-decoration: none; }
            .menu-button { display: none; width: 40px; height: 40px; padding: 0; border: 1px solid rgba(255, 255, 255, .45); border-radius: 9px; color: #fff; background: transparent; cursor: pointer; }
            .menu-button:hover { background: rgba(255, 255, 255, .14); }
            .menu-button:focus-visible, .logout-button:focus-visible { outline: 3px solid rgba(255, 255, 255, .75); outline-offset: 3px; }
            .user-name { overflow: hidden; max-width: 240px; color: #fff; font-size: 14px; font-weight: 650; text-overflow: ellipsis; white-space: nowrap; }
            .logout-form { margin: 0; }
            .logout-button { min-height: 38px; padding: 0 14px; border: 1px solid rgba(255, 255, 255, .6); border-radius: 8px; color: #fff; background: transparent; cursor: pointer; font-size: 14px; font-weight: 700; }
            .logout-button:hover { color: var(--primary-dark); background: #fff; }
            .app-shell { display: flex; min-height: calc(100vh - var(--navbar-height)); }
            .sidebar { z-index: 30; flex: 0 0 var(--sidebar-width); width: var(--sidebar-width); min-height: calc(100vh - var(--navbar-height)); padding: 18px 12px; background: var(--primary); }
            .nav-list { display: grid; gap: 6px; margin: 0; padding: 0; list-style: none; }
            .nav-link, .nav-disabled { display: flex; align-items: center; min-height: 44px; padding: 0 14px; border-radius: 8px; color: rgba(255, 255, 255, .86); font-size: 14px; font-weight: 750; text-decoration: none; }
            .nav-link:hover, .nav-link.active { color: #fff; background: rgba(255, 255, 255, .16); }
            .nav-disabled { color: rgba(255, 255, 255, .52); cursor: not-allowed; }
            .content { flex: 1; min-width: 0; padding: 36px; }
            .page-heading { margin-bottom: 26px; }
            .page-heading h1 { margin: 0; color: var(--primary-dark); font-size: 28px; letter-spacing: -.04em; }
            .page-heading p { margin: 8px 0 0; color: var(--muted); }
            .summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; max-width: 960px; margin-bottom: 24px; }
            .summary-card { padding: 22px; border: 1px solid var(--line); border-radius: 14px; background: #fff; box-shadow: 0 8px 26px rgba(31, 41, 55, .05); }
            .summary-label { display: block; color: var(--muted); font-size: 14px; font-weight: 700; }
            .summary-card strong { display: block; margin-top: 10px; color: var(--primary-dark); font-size: 32px; letter-spacing: -.04em; }
            .primary-button { display: inline-flex; align-items: center; min-height: 42px; padding: 0 15px; border-radius: 8px; color: #fff; background: var(--primary); font-size: 14px; font-weight: 750; text-decoration: none; }
            .primary-button:hover { background: var(--primary-dark); }
            .sidebar-overlay { position: fixed; z-index: 20; inset: var(--navbar-height) 0 0; visibility: hidden; background: rgba(15, 23, 42, .46); opacity: 0; pointer-events: none; transition: opacity .25s ease, visibility .25s ease; }
            @media (max-width: 768px) {
                .topbar { padding: 0 18px; }
                .menu-button { display: inline-grid; place-items: center; }
                .topbar-end { gap: 10px; }
                .user-name { max-width: 125px; }
                .app-shell { width: 100%; }
                .sidebar { position: fixed; top: var(--navbar-height); bottom: 0; left: 0; z-index: 30; width: min(82vw, 280px); min-height: 0; overflow-y: auto; overscroll-behavior: contain; box-shadow: 14px 0 35px rgba(31, 41, 55, .16); transform: translateX(-100%); transition: transform .25s ease; }
                body.sidebar-open { overflow: hidden; }
                body.sidebar-open .sidebar { transform: translateX(0); }
                body.sidebar-open .sidebar-overlay { visibility: visible; opacity: 1; pointer-events: auto; }
                .content { width: 100%; min-width: 0; margin-left: 0; padding: 28px 20px; }
                .summary-grid { grid-template-columns: 1fr; }
            }
            @media (max-width: 430px) { .topbar { padding: 0 14px; } .topbar-start { gap: 10px; } .brand { font-size: 17px; } .user-name { max-width: 90px; } .logout-button { padding: 0 10px; } }
        </style>
        @stack('styles')
    </head>
    <body>
        @php($currentUser = auth()->user())
        <header class="topbar">
            <div class="topbar-start">
                <button class="menu-button" type="button" aria-label="Buka sidebar" aria-controls="sidebar" aria-expanded="false" data-sidebar-toggle>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-linecap="round" stroke-width="2"/></svg>
                </button>
                <a class="brand" href="{{ route('dashboard') }}">Radarsurat</a>
            </div>
            <div class="topbar-end">
                <span class="user-name" title="{{ $currentUser?->name }}">{{ $currentUser?->name }}</span>
                <form class="logout-form" method="POST" action="{{ route('logout') }}">@csrf <button class="logout-button" type="submit">Keluar</button></form>
            </div>
        </header>
        <div class="app-shell">
            <aside id="sidebar" class="sidebar" aria-label="Navigasi dashboard">
                <nav>
                    <ul class="nav-list">
                        <li><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a></li>
                        @if ($currentUser?->role?->slug === 'admin_surat')
                            <li><a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">Data Pengguna</a></li>
                        @endif
                        <li><span class="nav-disabled" aria-disabled="true">Data Divisi</span></li>
                    </ul>
                </nav>
            </aside>
            <div class="sidebar-overlay" aria-hidden="true" data-sidebar-overlay></div>
            <main class="content">@yield('content')</main>
        </div>
        <script>
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('[data-sidebar-toggle]');
            const overlay = document.querySelector('[data-sidebar-overlay]');
            const mobileSidebar = window.matchMedia('(max-width: 768px)');
            const setSidebarState = (isOpen) => {
                const shouldOpen = mobileSidebar.matches && isOpen;
                document.body.classList.toggle('sidebar-open', shouldOpen);
                toggle.setAttribute('aria-expanded', String(shouldOpen));
                toggle.setAttribute('aria-label', shouldOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi');
            };

            toggle.addEventListener('click', () => setSidebarState(!document.body.classList.contains('sidebar-open')));
            overlay.addEventListener('click', () => setSidebarState(false));
            sidebar.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', () => {
                    if (mobileSidebar.matches) setSidebarState(false);
                });
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') setSidebarState(false);
            });
            mobileSidebar.addEventListener('change', () => setSidebarState(false));
        </script>
        @stack('scripts')
    </body>
</html>
