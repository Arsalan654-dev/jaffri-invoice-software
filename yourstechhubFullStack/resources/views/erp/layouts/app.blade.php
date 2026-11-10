<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Demo ERP')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div class="layout" id="app">
        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <button class="sidebar-close-btn" id="sidebarCloseBtn" data-icon="x"></button>
            <div class="sidebar-header">
                <div class="logo-container">
                    <h1 class="sidebar-logo-text" style="color: white; font-size: 1.5rem; font-weight: bold;">Demo ERP</h1>
                </div>
            </div>
            <nav class="sidebar-nav">
                @php $navItems = erp_navigation(); @endphp
                @foreach($navItems as $item)
                    <a class="nav-item {{ request()->routeIs($item['active'] ?? 'never') ? 'active' : '' }}" href="{{ $item['href'] }}" data-icon="{{ $item['icon'] }}">
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                    <div style="min-width:0">
                        <div class="user-email">{{ auth()->user()->email ?? 'admin@yourstechhub.com' }}</div>
                        <div class="user-role">{{ auth()->user()->roles->pluck('name')->join(', ') ?: 'User' }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('erp.logout') }}" style="display:inline">
                    @csrf
                    <button type="submit" class="sign-out-btn" data-icon="logout"><span>Log Out</span></button>
                </form>
            </div>
        </aside>

        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <header class="header">
                <div class="header-left">
                    <button class="menu-btn" id="menuBtn" data-icon="menu" aria-label="Menu"></button>
                    <div class="header-title-container">
                        <h2 class="header-title">@yield('title', 'Dashboard')</h2>
                        <p class="header-subtitle">@yield('subtitle', 'Manage your project dashboard and performance')</p>
                    </div>
                </div>
                <div class="header-right">
                    <button class="icon-btn" data-icon="bell" aria-label="Notifications"></button>
                    <div class="header-divider"></div>
                    <div class="header-profile">
                        <div class="user-role" style="font-size:.75rem;color:var(--text-muted);margin-right:8px">{{ auth()->user()->roles->pluck('name')->join(', ') ?: 'User' }}</div>
                        <div class="user-avatar-small">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                    </div>
                </div>
            </header>

            <div class="content" id="pageContent">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            @yield('content')
        </div>

        @include('erp.partials.forbidden-modal')

            <footer class="app-footer">
                <span>Created By</span>
                <a class="footer-brand" href="https://wa.me/923352352689" target="_blank">Yourtechhub</a>
                <span class="footer-sep">&middot;</span>
                <a class="footer-phone" href="tel:+923352352689">+92 335 2352689</a>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
