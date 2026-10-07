@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <p class="muted" style="margin:0 0 6px;">SUPPLIER COMMUNICATION</p>
                <h1 class="title">Purchase Orders</h1>
            </div>
            <a class="btn btn-primary" href="/purchase-orders/create">Create order</a>
        </div>

        <div id="historyMessage" class="alert error" role="status" hidden></div>
        <section class="card filters" aria-label="Filter purchase orders">
            <input id="searchFilter" type="search" placeholder="Search supplier or product" aria-label="Search supplier or product">
            <select id="supplierFilter" aria-label="Filter by supplier"><option value="">All suppliers</option></select>
            <select id="statusFilter" aria-label="Filter by status">
                <option value="">All statuses</option>
                <option value="draft">Draft</option>
                <option value="reviewed">Reviewed</option>
                <option value="sent">Sent</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <input id="fromFilter" type="date" aria-label="From date">
            <input id="toFilter" type="date" aria-label="To date">
        </section>

        <section class="card history-table">
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Date</th><th>Supplier</th><th>Products</th><th>Channel</th><th>Status</th><th></th></tr></thead>
                    <tbody id="orderRows"><tr><td colspan="6" class="muted">Loading purchase orders...</td></tr></tbody>
                </table>
            </div>
        </section>
    </div>

    <style>
        .filters { display: grid; grid-template-columns: minmax(190px, 1.5fr) repeat(4, minmax(130px, 1fr)); gap: 10px; padding: 16px; margin-bottom: 18px; }
        .filters input, .filters select { min-width: 0; width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 6px; background: white; font: inherit; }
        .history-table { overflow: hidden; }
        .history-table .table-wrap { overflow-x: auto; }
        .history-table table { min-width: 700px; }
        .state-label { text-transform: capitalize; }
        @media (max-width: 900px) { .filters { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 520px) { .filters { grid-template-columns: 1fr; } }
    </style>

    <script>
        const token = localStorage.getItem('inventory_token');
        if (!token) window.location.href = '/login';

        const orderRows = document.getElementById('orderRows');
        const historyMessage = document.getElementById('historyMessage');
        const filters = ['searchFilter', 'supplierFilter', 'statusFilter', 'fromFilter', 'toFilter'];

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, character => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
            })[character]);
        }

        async function getJson(url) {
            const response = await fetch(url, { headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token } });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'Unable to load purchase orders.');
            return payload;
        }

        async function loadSuppliers() {
            const payload = await getJson('/api/v1/purchase-orders/suppliers?include_inactive=true');
            document.getElementById('supplierFilter').innerHTML = '<option value="">All suppliers</option>' + (payload.data || []).map(supplier =>
                `<option value="${supplier.id}">${escapeHtml(supplier.name)}</option>`
            ).join('');
        }

        async function loadOrders() {
            const params = new URLSearchParams();
            const values = {
                search: document.getElementById('searchFilter').value.trim(),
                supplier_id: document.getElementById('supplierFilter').value,
                status: document.getElementById('statusFilter').value,
                from: document.getElementById('fromFilter').value,
                to: document.getElementById('toFilter').value,
            };
            Object.entries(values).forEach(([key, value]) => { if (value) params.set(key, value); });

            try {
                const payload = await getJson('/api/v1/purchase-orders?' + params.toString());
                const orders = payload.data || [];
                if (!orders.length) {
                    orderRows.innerHTML = '<tr><td colspan="6" class="muted">No purchase orders match these filters.</td></tr>';
                    return;
                }
                orderRows.innerHTML = orders.map(order => {
                    const latest = (order.communications || []).slice(-1)[0];
                    const channel = latest?.channel || order.channel || 'Not selected';
                    const status = latest?.status === 'opened' ? 'Opened in ' + channel : order.status;
                    const productCount = (order.items || []).length;
                    return `<tr>
                        <td>${new Date(order.created_at).toLocaleDateString()}</td>
                        <td><strong>${escapeHtml(order.supplier?.name || 'Supplier unavailable')}</strong></td>
                        <td>${productCount} product${productCount === 1 ? '' : 's'}</td>
                        <td class="state-label">${escapeHtml(channel)}</td>
                        <td><span class="status ${status === 'cancelled' ? 'low-stock' : 'in-stock'} state-label">${escapeHtml(status)}</span></td>
                        <td><a class="btn btn-secondary" style="display:inline-block;padding:8px 11px;" href="/purchase-orders/${order.id}">View</a></td>
                    </tr>`;
                }).join('');
                historyMessage.hidden = true;
            } catch (error) {
                historyMessage.hidden = false;
                historyMessage.textContent = error.message;
                orderRows.innerHTML = '<tr><td colspan="6" class="muted">Orders could not be loaded.</td></tr>';
            }
        }

        filters.forEach(id => document.getElementById(id).addEventListener(id === 'searchFilter' ? 'input' : 'change', loadOrders));
        loadSuppliers().then(loadOrders).catch(error => {
            historyMessage.hidden = false;
            historyMessage.textContent = error.message;
        });
    </script>
@endsection
