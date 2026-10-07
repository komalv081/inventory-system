@extends('layouts.app-new')

@section('content')
    <div class="page-heading"><div><span class="eyebrow">Procurement</span><h1>Purchase orders</h1><p>Track supplier orders from draft to handoff.</p></div><a href="/purchase-orders/create" class="btn btn-primary">+ Create order</a></div>
    <section class="filter-card order-filters">
        <label class="filter-search"><span>⌕</span><input id="searchFilter" type="search" placeholder="Search supplier or product"></label>
        <select id="supplierFilter" aria-label="Filter by supplier"><option value="">All suppliers</option></select>
        <select id="statusFilter" aria-label="Filter by status"><option value="">All statuses</option><option value="draft">Draft</option><option value="reviewed">Reviewed</option><option value="sent">Sent</option><option value="cancelled">Cancelled</option></select>
        <input id="fromFilter" type="date" aria-label="From date"><input id="toFilter" type="date" aria-label="To date">
    </section>
    <section id="historyMessage" class="alert error" role="status" hidden></section>
    <section class="card table-card"><div class="table-toolbar"><div><h2>Order history</h2><p id="orderSummary">Loading orders...</p></div></div><div class="table-scroll"><table class="data-table"><thead><tr><th>Date</th><th>Supplier</th><th>Products</th><th>Channel</th><th>Status</th><th>Actions</th></tr></thead><tbody id="orderRows"><tr><td colspan="6" class="empty-state">Loading purchase orders...</td></tr></tbody></table></div></section>
    <div id="orderModal" class="modal-backdrop" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="orderModalTitle">
            <div class="modal-header"><div><span class="eyebrow">Order overview</span><h2 id="orderModalTitle">Purchase order</h2><p id="orderModalSubtitle"></p></div><button class="modal-close" type="button" data-close-order-modal aria-label="Close">×</button></div>
            <div id="orderModalSummary" class="modal-summary"></div>
            <div class="modal-actions"><button class="btn btn-secondary" type="button" data-close-order-modal>Close</button><a id="orderDetailsLink" class="btn btn-primary" href="#">Open full details</a></div>
        </div>
    </div>
    <div id="confirmModal" class="modal-backdrop" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
            <div class="modal-header"><div><h2 id="confirmTitle">Delete purchase order?</h2><p id="confirmDescription">This action cannot be undone.</p></div><button class="modal-close" type="button" data-close-modal aria-label="Close">×</button></div>
            <div class="modal-actions"><button class="btn btn-secondary" type="button" data-close-modal>Cancel</button><button id="confirmAction" class="btn btn-danger" type="button">Delete</button></div>
        </div>
    </div>
    <script>
        const token = localStorage.getItem('inventory_token'); if (!token) window.location.href = '/login';
        const orderRows = document.getElementById('orderRows'); const historyMessage = document.getElementById('historyMessage');
        const filters = ['searchFilter','supplierFilter','statusFilter','fromFilter','toFilter'];
        function escapeHtml(value) { return String(value || '').replace(/[&<>'"]/g, char => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', "'":'&#39;', '"':'&quot;' }[char])); }
        function statusClass(status) { return status === 'cancelled' ? 'warning' : status === 'sent' ? 'success' : 'info'; }
        async function getJson(url) { const response = await fetch(url, { headers: { Accept:'application/json', Authorization:`Bearer ${token}` } }); const data = await response.json(); if (!response.ok) throw new Error(data.message || 'Request failed.'); return data; }
        async function loadSuppliers() { const payload = await getJson('/api/v1/purchase-orders/suppliers?include_inactive=true'); document.getElementById('supplierFilter').innerHTML = '<option value="">All suppliers</option>' + (payload.data || []).map(item => `<option value="${item.id}">${escapeHtml(item.name)}</option>`).join(''); }
        async function loadOrders() {
            const values = { search: document.getElementById('searchFilter').value.trim(), supplier_id: document.getElementById('supplierFilter').value, status: document.getElementById('statusFilter').value, from: document.getElementById('fromFilter').value, to: document.getElementById('toFilter').value };
            const params = new URLSearchParams(Object.entries(values).filter(([, value]) => value));
            try { const payload = await getJson('/api/v1/purchase-orders?' + params); const orders = payload.data || []; document.getElementById('orderSummary').textContent = `${orders.length} order${orders.length === 1 ? '' : 's'}`; orderRows.innerHTML = orders.length ? orders.map(order => { const latest = (order.communications || []).slice(-1)[0]; const channel = latest?.channel || order.channel || 'Not selected'; const displayStatus = latest?.status === 'opened' ? `Opened in ${channel}` : order.status; const itemCount = (order.items || []).length; const deleteButton = ['draft', 'reviewed'].includes(order.status) ? `<button class="btn btn-danger" type="button" data-delete-order="${order.id}">Delete</button>` : ''; return `<tr><td>${new Date(order.created_at).toLocaleDateString()}</td><td><strong>${escapeHtml(order.supplier?.name || 'Unavailable')}</strong></td><td>${itemCount} item${itemCount === 1 ? '' : 's'}</td><td><span class="channel-pill">${escapeHtml(channel)}</span></td><td><span class="status ${statusClass(displayStatus)}"><i></i>${escapeHtml(displayStatus)}</span></td><td><div class="table-actions"><button class="btn btn-ghost" type="button" data-view-order="${order.id}">View order</button>${deleteButton}</div></td></tr>`; }).join('') : '<tr><td colspan="6" class="empty-state">No purchase orders match these filters.</td></tr>'; document.querySelectorAll('[data-view-order]').forEach(button => button.addEventListener('click', () => openOrderModal(orders.find(order => String(order.id) === button.dataset.viewOrder)))); document.querySelectorAll('[data-delete-order]').forEach(button => button.addEventListener('click', () => confirmDelete('purchase order', button.dataset.deleteOrder, 'Delete this purchase order? This action cannot be undone.'))); historyMessage.hidden = true; } catch (error) { historyMessage.hidden = false; historyMessage.textContent = error.message; orderRows.innerHTML = '<tr><td colspan="6" class="empty-state">Orders could not be loaded.</td></tr>'; }
        }
        const orderModal = document.getElementById('orderModal'); const orderModalSummary = document.getElementById('orderModalSummary'); const orderDetailsLink = document.getElementById('orderDetailsLink');
        function openOrderModal(order) {
            if (!order) return;
            const items = (order.items || []).map(item => `<div class="modal-item"><span><strong>${escapeHtml(item.product_name)}</strong><small>${escapeHtml(item.quantity)} ${escapeHtml(item.unit || 'units')}</small></span><span class="mono">${escapeHtml(item.barcode || '—')}</span></div>`).join('');
            document.getElementById('orderModalTitle').textContent = `Order #${order.id}`;
            document.getElementById('orderModalSubtitle').textContent = `Created ${new Date(order.created_at).toLocaleString()}`;
            orderDetailsLink.href = `/purchase-orders/${order.id}`;
            orderModalSummary.innerHTML = `<div class="modal-summary-row"><span>Supplier</span><strong>${escapeHtml(order.supplier?.name || 'Unavailable')}</strong></div><div class="modal-summary-row"><span>Status</span><strong>${escapeHtml(order.status)}</strong></div><div class="modal-summary-row"><span>Channel</span><strong>${escapeHtml(order.channel || 'Not selected')}</strong></div><div class="modal-summary-row"><span>Items</span><strong>${(order.items || []).length}</strong></div><div class="modal-item-list">${items || '<p class="muted">No items added.</p>'}</div>`;
            orderModal.hidden = false; document.body.style.overflow = 'hidden'; document.querySelector('[data-close-order-modal]').focus();
        }
        function closeOrderModal() { orderModal.hidden = true; document.body.style.overflow = ''; }
        document.querySelectorAll('[data-close-order-modal]').forEach(button => button.addEventListener('click', closeOrderModal));
        orderModal.addEventListener('click', event => { if (event.target === orderModal) closeOrderModal(); });
        const confirmModal = document.getElementById('confirmModal'); const confirmAction = document.getElementById('confirmAction'); let pendingDelete = null;
        function confirmDelete(type, id, description) { pendingDelete = { type, id }; document.getElementById('confirmTitle').textContent = `Delete ${type}?`; document.getElementById('confirmDescription').textContent = description; confirmModal.hidden = false; document.body.style.overflow = 'hidden'; confirmAction.focus(); }
        function closeConfirm() { confirmModal.hidden = true; document.body.style.overflow = ''; pendingDelete = null; }
        confirmAction.addEventListener('click', async () => { if (!pendingDelete) return; confirmAction.disabled = true; try { const response = await fetch(`/api/v1/purchase-orders/${pendingDelete.id}`, { method: 'DELETE', headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } }); const result = await response.json(); if (!response.ok) throw new Error(result.message || 'Unable to delete purchase order.'); closeConfirm(); closeOrderModal(); await loadOrders(); } catch (error) { document.getElementById('confirmDescription').textContent = error.message; } finally { confirmAction.disabled = false; } });
        confirmModal.querySelectorAll('[data-close-modal]').forEach(button => button.addEventListener('click', closeConfirm)); confirmModal.addEventListener('click', event => { if (event.target === confirmModal) closeConfirm(); }); document.addEventListener('keydown', event => { if (event.key === 'Escape' && !orderModal.hidden) closeOrderModal(); else if (event.key === 'Escape' && !confirmModal.hidden) closeConfirm(); });
        filters.forEach(id => document.getElementById(id).addEventListener(id === 'searchFilter' ? 'input' : 'change', loadOrders));
        loadSuppliers().then(loadOrders).catch(error => { historyMessage.hidden = false; historyMessage.textContent = error.message; });
    </script>
@endsection
