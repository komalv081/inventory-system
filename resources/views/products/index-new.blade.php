@extends('layouts.app-new')

@section('content')
    <div class="page-heading">
        <div>
            <span class="eyebrow">Catalog</span>
            <h1>Products</h1>
            <p>Manage your inventory, pricing, and stock levels.</p>
        </div>
        <div class="page-actions">
            <button id="refreshProducts" class="btn btn-secondary" type="button">↻ Refresh</button>
        </div>
    </div>

    <section class="card form-card">
        <div class="section-heading">
            <div><h2>Add a product</h2><p>Enter the product details below to create a new item.</p></div>
            <span class="section-badge">New item</span>
        </div>
        <form id="productForm" class="form-grid">
            <div class="form-field"><label for="product_name">Product name</label><input id="product_name" name="product_name" required placeholder="e.g. Organic rice"></div>
            <div class="form-field"><label for="sku">SKU</label><input id="sku" name="sku" required placeholder="e.g. RICE-001"></div>
            <div class="form-field"><label for="selling_price">Selling price (₹)</label><input id="selling_price" name="selling_price" type="number" min="0" step="0.01" required></div>
            <div class="form-field"><label for="current_quantity">Current quantity</label><input id="current_quantity" name="current_quantity" type="number" min="0" step="1" required></div>
            <div class="form-actions"><button class="btn btn-primary" type="submit">Save product</button></div>
        </form>
        <div id="productMessage" class="alert" role="status" hidden></div>
    </section>

    <section class="card table-card">
        <div class="table-toolbar">
            <div><h2>Product list</h2><p id="productSummary">Loading products...</p></div>
            <span class="count-badge"><span id="productCount">0</span> products</span>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead><tr><th>Product</th><th>SKU</th><th>Quantity</th><th>Selling price</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody id="productTableBody"><tr><td colspan="6" class="empty-state">Loading products...</td></tr></tbody>
            </table>
        </div>
        <div id="pagination" class="pagination" aria-live="polite"></div>
    </section>
    <div id="confirmModal" class="modal-backdrop" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
            <div class="modal-header"><div><h2 id="confirmTitle">Delete product?</h2><p id="confirmDescription">This action cannot be undone.</p></div><button class="modal-close" type="button" data-close-modal aria-label="Close">×</button></div>
            <div class="modal-actions"><button class="btn btn-secondary" type="button" data-close-modal>Cancel</button><button id="confirmAction" class="btn btn-danger" type="button">Delete</button></div>
        </div>
    </div>
    <script>
        const token = localStorage.getItem('inventory_token');
        if (!token) window.location.href = '/login';
        let currentPage = 1;
        const currency = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', minimumFractionDigits: 2 });

        function renderStatus(quantity) {
            const low = quantity <= 5;
            return `<span class="status ${low ? 'warning' : 'success'}"><i></i>${low ? 'Low stock' : 'In stock'}</span>`;
        }

        async function loadProducts(page = 1) {
            currentPage = page;
            const response = await fetch(`/api/v1/products?per_page=15&page=${page}`, { headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } });
            if (!response.ok) throw new Error('Unable to load products.');
            const payload = await response.json();
            const products = payload.data || [];
            const pagination = payload.meta || {};
            const links = payload.links || {};
            document.getElementById('productCount').textContent = pagination.total || 0;
            document.getElementById('productSummary').textContent = `Showing ${products.length} of ${pagination.total || 0} products`;
            document.getElementById('productTableBody').innerHTML = products.length ? products.map(product => {
                const quantity = Number(product.current_quantity || 0);
                return `<tr><td><strong>${escapeHtml(product.product_name)}</strong></td><td><span class="mono">${escapeHtml(product.sku)}</span></td><td>${quantity}</td><td>${currency.format(Number(product.selling_price || 0))}</td><td>${renderStatus(quantity)}</td><td><button class="btn btn-danger" type="button" data-delete-product="${product.id}">Delete</button></td></tr>`;
            }).join('') : '<tr><td colspan="6" class="empty-state">No products found.</td></tr>';
            document.querySelectorAll('[data-delete-product]').forEach(button => button.addEventListener('click', () => confirmDelete('product', button.dataset.deleteProduct, `Delete “${products.find(item => String(item.id) === button.dataset.deleteProduct)?.product_name || 'this product'}”?`, 'Product deleted successfully.')));
            const pages = [];
            if (links.prev) pages.push('<button class="pagination-button" data-page="' + (currentPage - 1) + '">← Previous</button>');
            pages.push(`<span>Page ${currentPage} of ${pagination.last_page || 1}</span>`);
            if (links.next) pages.push('<button class="pagination-button" data-page="' + (currentPage + 1) + '">Next →</button>');
            const paginationContainer = document.getElementById('pagination');
            paginationContainer.innerHTML = pages.join('');
            paginationContainer.querySelectorAll('[data-page]').forEach(button => button.addEventListener('click', () => loadProducts(Number(button.dataset.page))));
        }
        function escapeHtml(value) { return String(value || '').replace(/[&<>'"]/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[char])); }
        const confirmModal = document.getElementById('confirmModal');
        const confirmAction = document.getElementById('confirmAction');
        let pendingDelete = null;
        function confirmDelete(type, id, description, successMessage) {
            pendingDelete = { type, id, successMessage };
            document.getElementById('confirmTitle').textContent = `Delete ${type}?`;
            document.getElementById('confirmDescription').textContent = description;
            confirmModal.hidden = false;
            document.body.style.overflow = 'hidden';
            confirmAction.focus();
        }
        function closeConfirm() { confirmModal.hidden = true; document.body.style.overflow = ''; pendingDelete = null; }
        confirmAction.addEventListener('click', async () => {
            if (!pendingDelete) return;
            const { type, id, successMessage } = pendingDelete;
            confirmAction.disabled = true;
            try {
                const response = await fetch(`/api/v1/${type === 'product' ? 'products' : 'suppliers'}/${id}`, { method: 'DELETE', headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || `Unable to delete ${type}.`);
                closeConfirm();
                document.getElementById('productMessage')?.classList.add('success');
                document.getElementById('productMessage')?.replaceChildren(document.createTextNode(successMessage));
                document.getElementById('productMessage')?.removeAttribute('hidden');
                await loadProducts(currentPage);
            } catch (error) { document.getElementById('confirmDescription').textContent = error.message; }
            finally { confirmAction.disabled = false; }
        });
        confirmModal.querySelectorAll('[data-close-modal]').forEach(button => button.addEventListener('click', closeConfirm));
        confirmModal.addEventListener('click', event => { if (event.target === confirmModal) closeConfirm(); });
        document.addEventListener('keydown', event => { if (event.key === 'Escape' && !confirmModal.hidden) closeConfirm(); });
        document.getElementById('refreshProducts').addEventListener('click', () => loadProducts(currentPage));
        document.getElementById('productForm').addEventListener('submit', async event => {
            event.preventDefault();
            const message = document.getElementById('productMessage');
            const payload = { product_name: document.getElementById('product_name').value, sku: document.getElementById('sku').value, selling_price: Number(document.getElementById('selling_price').value), current_quantity: Number(document.getElementById('current_quantity').value) };
            try {
                const response = await fetch('/api/v1/products', { method: 'POST', headers: { Accept: 'application/json', Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Unable to save product.');
                message.className = 'alert success'; message.textContent = 'Product saved successfully.'; message.hidden = false;
                document.getElementById('productForm').reset(); await loadProducts(1);
            } catch (error) { message.className = 'alert error'; message.textContent = error.message; message.hidden = false; }
        });
        loadProducts().catch(error => { document.getElementById('productTableBody').innerHTML = `<tr><td colspan="5" class="empty-state">${error.message}</td></tr>`; });
    </script>
@endsection
