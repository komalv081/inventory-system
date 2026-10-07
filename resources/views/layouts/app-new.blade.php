<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Inventory System' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <a href="/dashboard" class="brand" aria-label="Inventory System home">
                <span class="brand-mark">I</span>
                <span><strong>Inventory</strong><small>System</small></span>
            </a>
            <nav class="sidebar-nav" aria-label="Main navigation">
                <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">⌂</span><span>Dashboard</span>
                </a>
                <a href="/products" class="nav-link {{ request()->is('products') || request()->is('products/*') ? 'active' : '' }}">
                    <span class="nav-icon">▦</span><span>Products</span>
                </a>
                <a href="/suppliers" class="nav-link {{ request()->is('suppliers') || request()->is('suppliers/*') ? 'active' : '' }}">
                    <span class="nav-icon">◇</span><span>Suppliers</span>
                </a>
                <a href="/purchase-orders" class="nav-link {{ request()->is('purchase-orders') || request()->is('purchase-orders/*') ? 'active' : '' }}">
                    <span class="nav-icon">▤</span><span>Purchase Orders</span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <div class="user-avatar">IS</div>
                <div><strong>Inventory User</strong><small>Store owner</small></div>
                <button type="button" id="logoutButton" class="icon-button" aria-label="Log out">↗</button>
            </div>
        </aside>

        <div class="app-main">
            <header class="topbar">
                <button type="button" class="mobile-menu" id="mobileMenu" aria-label="Open navigation">☰</button>
                <div class="topbar-title">
                    <span class="eyebrow">Inventory workspace</span>
                    <strong>{{ $title ?? 'Dashboard' }}</strong>
                </div>
                <div class="topbar-actions">
                    <span class="status-pill"><i></i> System online</span>
                    <button type="button" class="avatar-button" aria-label="User menu">IS</button>
                </div>
            </header>
            <main class="page-content">
                @yield('content')
            </main>
        </div>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <script>
        const menuButton = document.getElementById('mobileMenu');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const closeSidebar = () => { sidebar.classList.remove('open'); overlay.classList.remove('visible'); };
        menuButton?.addEventListener('click', () => { sidebar.classList.add('open'); overlay.classList.add('visible'); });
        overlay?.addEventListener('click', closeSidebar);
        document.getElementById('logoutButton')?.addEventListener('click', function () {
            localStorage.removeItem('inventory_token');
            window.location.href = '/login';
        });
    </script>
</body>
</html>
