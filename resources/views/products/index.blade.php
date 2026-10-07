@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="title">Products</h1>
                <p class="muted">Manage your inventory catalog</p>
            </div>
            <a class="btn btn-primary" href="/dashboard">Back to dashboard</a>
        </div>

        <div class="card" style="padding: 20px; margin-bottom: 20px;">
            <h3 style="margin:0 0 16px;">Add product</h3>
            <form id="productForm" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; align-items: end;">
                <div class="field">
                    <label for="product_name">Product name</label>
                    <input id="product_name" name="product_name" type="text" required>
                </div>
                <div class="field">
                    <label for="sku">SKU</label>
                    <input id="sku" name="sku" type="text" required>
                </div>
                <div class="field">
                    <label for="selling_price">Price</label>
                    <input id="selling_price" name="selling_price" type="number" min="0" step="0.01" required>
                </div>
                <div class="field">
                    <label for="current_quantity">Quantity</label>
                    <input id="current_quantity" name="current_quantity" type="number" min="0" step="1" required>
                </div>
                <button class="btn btn-primary" type="submit">Save product</button>
            </form>
            <div id="productMessage" class="alert success" style="display:none; margin-top:16px;"></div>
        </div>

        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">
                        <tr>
                            <td colspan="5" class="muted">Loading products...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="pagination" class="pagination" aria-live="polite"></div>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('inventory_token');

        if (!token) {
            window.location.href = '/login';
        }

        let currentPage = 1;

        async function loadProducts(page = 1) {
            currentPage = page;
            const response = await fetch(`/api/v1/products?per_page=15&page=${page}`, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token,
                },
            });

            if (!response.ok) {
                throw new Error('Cannot load products.');
            }

            const payload = await response.json();
            const products = Array.isArray(payload) ? payload : (payload.data ?? []);
            const pagination = payload.meta ?? {};
            const paginationLinks = payload.links ?? {};
            const tbody = document.getElementById('productTableBody');
            const paginationContainer = document.getElementById('pagination');

            if (!products.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="muted">No products yet.</td></tr>';
                paginationContainer.innerHTML = '';
                return;
            }

            tbody.innerHTML = products.map(product => {
                const quantity = Number(product.current_quantity ?? 0);
                const stockStatus = quantity <= 5 ? 'low-stock' : 'in-stock';
                const label = quantity <= 5 ? 'Low stock' : 'In stock';
                return `
                    <tr>
                        <td>${product.product_name || '—'}</td>
                        <td>${product.sku || '—'}</td>
                        <td>${quantity}</td>
                        <td>₹${Number(product.selling_price || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td><span class="status ${stockStatus}">${label}</span></td>
                    </tr>
                `;
            }).join('');

            const pageCount = Number(pagination.last_page ?? 1);
            const pageButtons = [];

            if (paginationLinks.prev) {
                pageButtons.push(`<button class="btn btn-secondary" type="button" data-page="${currentPage - 1}">Previous</button>`);
            }

            pageButtons.push(`<span class="muted">Page ${currentPage} of ${pageCount}</span>`);

            if (paginationLinks.next) {
                pageButtons.push(`<button class="btn btn-secondary" type="button" data-page="${currentPage + 1}">Next</button>`);
            }

            paginationContainer.innerHTML = pageButtons.join('');
            paginationContainer.querySelectorAll('[data-page]').forEach(button => {
                button.addEventListener('click', () => loadProducts(Number(button.dataset.page)));
            });
        }

        document.getElementById('productForm').addEventListener('submit', async function (event) {
            event.preventDefault();

            const payload = {
                product_name: document.getElementById('product_name').value,
                sku: document.getElementById('sku').value,
                selling_price: Number(document.getElementById('selling_price').value),
                current_quantity: Number(document.getElementById('current_quantity').value),
            };

            const message = document.getElementById('productMessage');

            try {
                const response = await fetch('/api/v1/products', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token,
                    },
                    body: JSON.stringify(payload),
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Unable to add product.');
                }

                message.className = 'alert success';
                message.textContent = 'Product saved successfully.';
                message.style.display = 'block';
                document.getElementById('productForm').reset();
                await loadProducts(currentPage);
            } catch (error) {
                message.className = 'alert error';
                message.textContent = error.message;
                message.style.display = 'block';
            }
        });

        loadProducts().catch((error) => {
            document.getElementById('productTableBody').innerHTML = `
                <tr><td colspan="5" class="muted">${error.message}</td></tr>
            `;
        });
    </script>
@endsection
