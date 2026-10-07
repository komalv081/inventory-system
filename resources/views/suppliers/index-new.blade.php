@extends('layouts.app-new')

@section('content')
    <div class="page-heading">
        <div><span class="eyebrow">Partners</span><h1>Suppliers</h1><p>Maintain your supplier and importer directory.</p></div>
        <a href="/suppliers/create" class="btn btn-primary">+ Add supplier</a>
    </div>
    <section class="filter-card">
        <div class="filter-search"><span>⌕</span><input id="supplierSearch" type="search" placeholder="Search supplier, company, or contact..."></div>
        <select id="supplierStatus" aria-label="Filter by status"><option value="active">Active only</option><option value="all">All suppliers</option><option value="inactive">Inactive only</option></select>
    </section>
    <section class="card table-card">
        <div class="table-toolbar"><div><h2>Supplier directory</h2><p id="supplierSummary">Loading suppliers...</p></div></div>
        <div class="table-scroll"><table class="data-table"><thead><tr><th>Supplier</th><th>Company</th><th>Contact</th><th>Status</th><th>Actions</th></tr></thead><tbody id="supplierTableBody"><tr><td colspan="5" class="empty-state">Loading suppliers...</td></tr></tbody></table></div>
    </section>
    <div id="confirmModal" class="modal-backdrop" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
            <div class="modal-header"><div><h2 id="confirmTitle">Delete supplier?</h2><p id="confirmDescription">This action cannot be undone.</p></div><button class="modal-close" type="button" data-close-modal aria-label="Close">×</button></div>
            <div class="modal-actions"><button class="btn btn-secondary" type="button" data-close-modal>Cancel</button><button id="confirmAction" class="btn btn-danger" type="button">Delete</button></div>
        </div>
    </div>
    <script>
        const token = localStorage.getItem('inventory_token'); if (!token) window.location.href = '/login';
        const tableBody = document.getElementById('supplierTableBody');
        const searchInput = document.getElementById('supplierSearch');
        const statusInput = document.getElementById('supplierStatus');
        function escapeHtml(value) { return String(value || '').replace(/[&<>'"]/g, char => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', "'":'&#39;', '"':'&quot;' }[char])); }
        function statusBadge(active) { return `<span class="status ${active ? 'success' : 'warning'}"><i></i>${active ? 'Active' : 'Inactive'}</span>`; }
        async function loadSuppliers() {
            const params = new URLSearchParams({ search: searchInput.value.trim(), active_only: statusInput.value === 'active' ? 'true' : statusInput.value === 'inactive' ? 'false' : '' });
            const response = await fetch('/api/v1/suppliers?' + params.toString(), { headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } });
            if (!response.ok) throw new Error('Unable to load suppliers.');
            const payload = await response.json(); const suppliers = payload.data || [];
            document.getElementById('supplierSummary').textContent = `${suppliers.length} supplier${suppliers.length === 1 ? '' : 's'}`;
            tableBody.innerHTML = suppliers.length ? suppliers.map(supplier => `<tr><td><div class="entity-name"><span>${escapeHtml(supplier.name.slice(0, 2).toUpperCase())}</span><div><strong>${escapeHtml(supplier.name)}</strong><small>${escapeHtml(supplier.company_name || 'No company')}</small></div></div></td><td>${escapeHtml(supplier.company_name || '—')}</td><td><strong>${escapeHtml(supplier.phone || '—')}</strong><small>${escapeHtml(supplier.email || 'No email')}</small></td><td>${statusBadge(supplier.is_active)}</td><td><div class="table-actions"><a href="/suppliers/${supplier.id}" class="btn btn-ghost">View</a><a href="/suppliers/${supplier.id}/edit" class="btn btn-ghost">Edit</a><button class="btn btn-danger" type="button" data-delete-supplier="${supplier.id}">Delete</button></div></td></tr>`).join('') : '<tr><td colspan="5" class="empty-state">No suppliers found.</td></tr>';
            document.querySelectorAll('[data-delete-supplier]').forEach(button => button.addEventListener('click', () => confirmDelete('supplier', button.dataset.deleteSupplier, `Delete “${suppliers.find(item => String(item.id) === button.dataset.deleteSupplier)?.name || 'this supplier'}”?`, 'Supplier deleted successfully.')));
        }
        const confirmModal = document.getElementById('confirmModal');
        const confirmAction = document.getElementById('confirmAction');
        let pendingDelete = null;
        function confirmDelete(type, id, description, successMessage) { pendingDelete = { type, id, successMessage }; document.getElementById('confirmTitle').textContent = `Delete ${type}?`; document.getElementById('confirmDescription').textContent = description; confirmModal.hidden = false; document.body.style.overflow = 'hidden'; confirmAction.focus(); }
        function closeConfirm() { confirmModal.hidden = true; document.body.style.overflow = ''; pendingDelete = null; }
        confirmAction.addEventListener('click', async () => { if (!pendingDelete) return; const { type, id, successMessage } = pendingDelete; confirmAction.disabled = true; try { const response = await fetch(`/api/v1/${type === 'product' ? 'products' : 'suppliers'}/${id}`, { method: 'DELETE', headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } }); const result = await response.json(); if (!response.ok) throw new Error(result.message || `Unable to delete ${type}.`); closeConfirm(); await loadSuppliers(); } catch (error) { document.getElementById('confirmDescription').textContent = error.message; } finally { confirmAction.disabled = false; } });
        confirmModal.querySelectorAll('[data-close-modal]').forEach(button => button.addEventListener('click', closeConfirm)); confirmModal.addEventListener('click', event => { if (event.target === confirmModal) closeConfirm(); }); document.addEventListener('keydown', event => { if (event.key === 'Escape' && !confirmModal.hidden) closeConfirm(); });
        searchInput.addEventListener('input', loadSuppliers); statusInput.addEventListener('change', loadSuppliers);
        loadSuppliers().catch(error => { tableBody.innerHTML = `<tr><td colspan="5" class="empty-state">${error.message}</td></tr>`; });
    </script>
@endsection
