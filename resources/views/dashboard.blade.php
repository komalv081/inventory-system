@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="title">Dashboard</h1>
                <p class="muted">Overview of your inventory and activity</p>
            </div>
            <a class="btn btn-secondary" href="/products">View products</a>
        </div>

        <div id="dashboardLoader" class="card" style="padding: 28px;">
            <p class="muted">Loading your store data...</p>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('inventory_token');

        if (!token) {
            window.location.href = '/login';
        }

        async function loadDashboard() {
            try {
                const meResponse = await fetch('/api/v1/auth/me', {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token,
                    },
                });

                if (!meResponse.ok) {
                    throw new Error('Your session has expired.');
                }

                const me = await meResponse.json();
                const user = me.user || {};
                const shop = me.shop || {};

                const productsResponse = await fetch('/api/v1/products', {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token,
                    },
                });

                if (!productsResponse.ok) {
                    throw new Error('Unable to fetch product data.');
                }

                const products = await productsResponse.json();

                const stats = [
                    { label: 'Products', value: products.length || 0 },
                    { label: 'Shop', value: shop.name || 'N/A' },
                    { label: 'Owner', value: user.name || 'N/A' },
                ];

                const loader = document.getElementById('dashboardLoader');
                loader.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:12px;">
                        <div>
                            <h2 style="margin:0; font-size:1.2rem;">${user.name || 'Store Owner'}</h2>
                            <p class="muted" style="margin: 6px 0 0;">${shop.name || 'Main Shop'}</p>
                        </div>
                    </div>

                    <div class="grid" style="margin-bottom: 20px;">
                        ${stats.map(item => `
                            <div class="stat">
                                <div class="muted">${item.label}</div>
                                <div class="stat-value">${item.value}</div>
                            </div>
                        `).join('')}
                    </div>

                    <div class="card" style="border: 1px solid var(--border); border-radius: 16px; background: #fff; padding: 20px;">
                        <h3 style="margin-top:0;">Quick actions</h3>
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <a class="btn btn-primary" href="/products">Manage inventory</a>
                            <a class="btn btn-secondary" href="/register">Create another shop</a>
                        </div>
                    </div>
                `;
            } catch (error) {
                document.getElementById('dashboardLoader').innerHTML = `
                    <div class="alert error">${error.message}</div>
                `;
            }
        }

        loadDashboard();
    </script>
@endsection
