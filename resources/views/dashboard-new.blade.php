@extends('layouts.app-new')

@section('content')
    <div class="page-heading">
        <div>
            <span class="eyebrow">Overview</span>
            <h1>Dashboard</h1>
            <p>Monitor your inventory and keep every operation moving.</p>
        </div>
        <div class="page-actions">
            <a href="/suppliers" class="btn btn-secondary">Manage suppliers</a>
            <a href="/products" class="btn btn-primary">Add product</a>
        </div>
    </div>

    <section id="dashboardLoader" class="loading-card" aria-live="polite">
        <div class="spinner"></div>
        <p>Loading your workspace...</p>
    </section>

    <script>
        const token = localStorage.getItem('inventory_token');
        if (!token) window.location.href = '/login';

        async function loadDashboard() {
            try {
                const [userResponse, productsResponse] = await Promise.all([
                    fetch('/api/v1/auth/me', { headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } }),
                    fetch('/api/v1/products?per_page=1', { headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } }),
                ]);

                if (!userResponse.ok || !productsResponse.ok) throw new Error('Unable to load your workspace.');
                const userData = await userResponse.json();
                const productData = await productsResponse.json();
                const user = userData.user || {};
                const shop = userData.shop || {};
                const totalProducts = productData.meta?.total || 0;
                const loader = document.getElementById('dashboardLoader');

                loader.className = 'dashboard-content';
                loader.innerHTML = `
                    <div class="welcome-card">
                        <div><span class="eyebrow">Welcome back</span><h2>${escapeHtml(user.name || 'Store owner')}</h2><p>${escapeHtml(shop.name || 'Your shop')}</p></div>
                        <span class="section-badge">Workspace active</span>
                    </div>
                    <div class="stat-grid">
                        <article class="stat-card"><span>Products</span><strong>${totalProducts}</strong><small>Active catalog items</small></article>
                        <article class="stat-card"><span>Shop</span><strong>${escapeHtml(shop.name || '—')}</strong><small>Current location</small></article>
                        <article class="stat-card"><span>Owner</span><strong>${escapeHtml(user.name || '—')}</strong><small>Account manager</small></article>
                    </div>
                    <div class="quick-actions-card">
                        <div><h2>Quick actions</h2><p>Continue your inventory workflow.</p></div>
                        <div class="page-actions"><a href="/products" class="btn btn-primary">Manage products</a><a href="/purchase-orders/create" class="btn btn-secondary">Create order</a><a href="/suppliers" class="btn btn-secondary">View suppliers</a></div>
                    </div>`;
            } catch (error) {
                document.getElementById('dashboardLoader').innerHTML = `<div class="alert error">${escapeHtml(error.message)}</div>`;
            }
        }

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>'"]/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[character]));
        }

        loadDashboard();
    </script>
@endsection
