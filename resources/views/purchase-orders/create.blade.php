@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <p class="muted" style="margin:0 0 6px;">PURCHASE ORDERS</p>
                <h1 class="title">Create an order</h1>
            </div>
            <a class="btn btn-secondary" href="/purchase-orders">Order history</a>
        </div>

        <div id="orderNotice" class="alert" role="status" aria-live="polite" hidden></div>

        <section id="selectionStep" class="card order-panel">
            <div class="order-heading">
                <div>
                    <h2>Select products</h2>
                    <p class="muted">Choose what you need and enter the quantities yourself.</p>
                </div>
            </div>
            <div class="order-controls">
                <div class="field">
                    <label for="supplierSelect">Supplier</label>
                    <select id="supplierSelect" required>
                        <option value="">Loading suppliers...</option>
                    </select>
                </div>
                <div class="field">
                    <label for="productSearch">Find a product</label>
                    <input id="productSearch" type="search" placeholder="Search products">
                </div>
            </div>
            <div id="productList" class="product-list" aria-live="polite">
                <p class="muted">Loading products...</p>
            </div>
            <div class="order-actions">
                <span id="selectedCount" class="muted">0 products selected</span>
                <button id="reviewOrder" class="btn btn-primary" type="button">Review order</button>
            </div>
        </section>

        <section id="reviewStep" class="card order-panel" hidden>
            <div class="order-heading">
                <div>
                    <p class="muted" style="margin:0 0 6px;">REVIEW BEFORE SENDING</p>
                    <h2 id="reviewSupplier"></h2>
                    <p id="reviewPhone" class="muted"></p>
                </div>
            </div>
            <div id="reviewProducts" class="review-products"></div>
            <div class="field message-field">
                <label for="orderMessage">Message</label>
                <textarea id="orderMessage" rows="10" maxlength="10000" required></textarea>
            </div>
            <p id="handoffNote" class="muted" role="status" aria-live="polite"></p>
            <a id="handoffLink" class="btn btn-secondary" hidden target="_blank" rel="noopener">Open message composer</a>
            <div class="order-actions review-actions">
                <a class="btn btn-secondary" href="/purchase-orders">Back</a>
                <button id="saveDraft" class="btn btn-secondary" type="button">Save draft</button>
                <button class="btn btn-primary handoff-button" data-channel="whatsapp" type="button">Send via WhatsApp</button>
                <button class="btn btn-primary handoff-button" data-channel="sms" type="button">Send via SMS</button>
            </div>
        </section>
    </div>

    <style>
        .order-panel { padding: 24px; }
        .order-heading { display: flex; justify-content: space-between; gap: 20px; align-items: flex-start; }
        .order-heading h2 { margin: 0; font-size: 1.35rem; }
        .order-heading p { margin: 8px 0 0; }
        .order-controls { display: grid; grid-template-columns: minmax(220px, 1fr) minmax(220px, 1fr); gap: 16px; margin: 24px 0; }
        .order-controls select, .order-controls input { width: 100%; padding: 12px 14px; border: 1px solid var(--border); border-radius: 6px; background: white; font-size: 1rem; }
        .product-list { border-top: 1px solid var(--border); }
        .product-row { display: grid; grid-template-columns: 28px minmax(0, 1fr) 150px; align-items: center; gap: 14px; padding: 16px 4px; border-bottom: 1px solid var(--border); }
        .product-row input[type="checkbox"] { width: 20px; height: 20px; accent-color: var(--primary); }
        .product-name { display: block; font-weight: 700; }
        .product-stock { display: block; margin-top: 4px; font-size: .9rem; color: var(--muted); }
        .quantity-input { padding: 10px 12px; border: 1px solid var(--border); border-radius: 6px; width: 100%; }
        .order-actions { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-top: 22px; }
        .review-products { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin: 22px 0; }
        .review-product { border-left: 3px solid var(--primary); padding: 4px 12px; }
        .review-product strong, .review-product span { display: block; }
        .review-product span { color: var(--muted); margin-top: 5px; }
        .message-field textarea { width: 100%; resize: vertical; padding: 14px; border: 1px solid var(--border); border-radius: 6px; font: inherit; line-height: 1.5; }
        .review-actions { justify-content: flex-start; }
        @media (max-width: 640px) {
            .order-panel { padding: 18px; }
            .order-controls { grid-template-columns: 1fr; }
            .product-row { grid-template-columns: 24px minmax(0, 1fr) 104px; gap: 8px; }
            .review-actions > * { flex: 1 1 42%; text-align: center; }
        }
    </style>

    <script>
        const token = localStorage.getItem('inventory_token');
        if (!token) window.location.href = '/login';

        const notice = document.getElementById('orderNotice');
        const productList = document.getElementById('productList');
        const supplierSelect = document.getElementById('supplierSelect');
        let products = [];
        let suppliers = [];
        let orderId = null;

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, character => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
            })[character]);
        }

        function showNotice(message, type = 'error') {
            notice.hidden = false;
            notice.className = 'alert ' + type;
            notice.textContent = message;
        }

        function normalizeWhatsappNumber(phone) {
            const number = phone.replace(/\D+/g, '');
            return number.startsWith('0') && number.length === 11
                ? `91${number.slice(1)}`
                : number;
        }

        function createHandoffUrl(channel, phone, message) {
            const number = channel === 'whatsapp'
                ? normalizeWhatsappNumber(phone)
                : phone.replace(/[^+\d]/g, '');
            const encodedMessage = encodeURIComponent(message);

            return channel === 'whatsapp'
                ? `https://wa.me/${number}?text=${encodedMessage}`
                : `sms:${number}?body=${encodedMessage}`;
        }

        async function api(url, options = {}) {
            const response = await fetch(url, {
                ...options,
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token,
                    ...(options.body ? { 'Content-Type': 'application/json' } : {}),
                    ...(options.headers || {}),
                },
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'The request could not be completed.');
            return payload;
        }

        function renderProducts() {
            const search = document.getElementById('productSearch').value.trim().toLowerCase();
            const visibleProducts = products.filter(product => (product.product_name || '').toLowerCase().includes(search));
            if (!visibleProducts.length) {
                productList.innerHTML = '<p class="muted" style="padding:18px 0;">No products found.</p>';
                return;
            }

            productList.innerHTML = visibleProducts.map(product => `
                <label class="product-row">
                    <input type="checkbox" class="product-choice" data-product-id="${product.id}" aria-label="Select ${escapeHtml(product.product_name)}">
                    <span>
                        <span class="product-name">${escapeHtml(product.product_name)}</span>
                        <span class="product-stock">Current stock: ${Number(product.current_quantity || 0)} ${escapeHtml(product.unit || '')}</span>
                    </span>
                    <input class="quantity-input" data-quantity-for="${product.id}" type="number" min="1" step="1" placeholder="Order qty" disabled aria-label="Order quantity for ${escapeHtml(product.product_name)}">
                </label>
            `).join('');
        }

        productList.addEventListener('change', event => {
            if (!event.target.matches('.product-choice')) return;
            const quantity = productList.querySelector(`[data-quantity-for="${event.target.dataset.productId}"]`);
            quantity.disabled = !event.target.checked;
            updateSelectedCount();
        });

        function updateSelectedCount() {
            const count = productList.querySelectorAll('.product-choice:checked').length;
            document.getElementById('selectedCount').textContent = `${count} product${count === 1 ? '' : 's'} selected`;
        }

        async function createOrder() {
            const selected = [...productList.querySelectorAll('.product-choice:checked')].map(checkbox => {
                const quantity = Number(productList.querySelector(`[data-quantity-for="${checkbox.dataset.productId}"]`).value);
                return { product_id: Number(checkbox.dataset.productId), quantity };
            });
            if (!selected.length || selected.some(item => !Number.isInteger(item.quantity) || item.quantity < 1)) {
                showNotice('Select at least one product and enter a quantity greater than zero.');
                return;
            }
            if (!supplierSelect.value) {
                showNotice('Choose a supplier before reviewing the order.');
                supplierSelect.focus();
                return;
            }

            try {
                const payload = await api('/api/v1/purchase-orders', {
                    method: 'POST',
                    body: JSON.stringify({ supplier_id: Number(supplierSelect.value), items: selected }),
                });
                orderId = payload.data.id;
                showReview(payload.data);
            } catch (error) {
                showNotice(error.message);
            }
        }

        function showReview(order) {
            document.getElementById('selectionStep').hidden = true;
            document.getElementById('reviewStep').hidden = false;
            document.getElementById('reviewSupplier').textContent = order.supplier.name;
            document.getElementById('reviewPhone').textContent = 'Phone: ' + (order.supplier.phone || 'Not provided');
            document.getElementById('reviewProducts').innerHTML = order.items.map(item => `
                <div class="review-product">
                    <strong>${escapeHtml(item.product_name)}</strong>
                    <span>${Number(item.quantity)} ${escapeHtml(item.unit || 'pcs')}</span>
                </div>
            `).join('');
            document.getElementById('orderMessage').value = order.message || '';
            notice.hidden = true;
        }

        async function saveDraft() {
            try {
                const payload = await api(`/api/v1/purchase-orders/${orderId}/draft`, {
                    method: 'PUT',
                    body: JSON.stringify({ message: document.getElementById('orderMessage').value }),
                });
                showNotice('Draft saved. No message was sent.', 'success');
                document.getElementById('orderMessage').value = payload.data.message;
            } catch (error) {
                showNotice(error.message);
            }
        }

        async function handoff(channel) {
            const supplier = suppliers.find(item => Number(item.id) === Number(supplierSelect.value));
            const channelName = channel === 'whatsapp' ? 'WhatsApp' : 'SMS';
            const phone = channel === 'whatsapp' ? supplier?.whatsapp_phone : supplier?.phone;
            if (!phone) {
                showNotice(`This supplier does not have a ${channelName}-capable phone number.`);
                return;
            }
            if (!document.getElementById('orderMessage').value.trim()) {
                showNotice('The message cannot be empty.');
                return;
            }
            const message = document.getElementById('orderMessage').value.trim();

            try {
                const payload = await api(`/api/v1/purchase-orders/${orderId}/handoff`, {
                    method: 'POST',
                    body: JSON.stringify({ channel, message }),
                });
                const handoffLink = document.getElementById('handoffLink');
                const handoffNote = document.getElementById('handoffNote');

                if (payload.handoff_url) {
                    handoffLink.href = payload.handoff_url;
                    handoffLink.hidden = false;
                    handoffNote.textContent = `Opening ${channelName}. The message is not sent yet; press Send in ${channelName} to complete it.`;
                    showNotice(`Handoff recorded as ${payload.handoff_status}.`, 'success');
                    window.location.assign(payload.handoff_url);
                } else {
                    handoffLink.hidden = true;
                    handoffNote.textContent = `SMS handoff result through Twilio: ${payload.handoff_status}.`;
                    showNotice(`SMS handoff recorded as ${payload.handoff_status}.`, payload.handoff_status === 'sent' ? 'success' : 'error');
                }
            } catch (error) {
                showNotice(error.message);
            }
        }

        document.getElementById('productSearch').addEventListener('input', renderProducts);
        document.getElementById('reviewOrder').addEventListener('click', createOrder);
        document.getElementById('saveDraft').addEventListener('click', saveDraft);
        document.querySelectorAll('.handoff-button').forEach(button => {
            button.addEventListener('click', () => handoff(button.dataset.channel));
        });

        Promise.all([api('/api/v1/purchase-orders/products'), api('/api/v1/purchase-orders/suppliers')])
            .then(([productPayload, supplierPayload]) => {
                products = productPayload.data || productPayload;
                suppliers = supplierPayload.data || [];
                supplierSelect.innerHTML = '<option value="">Select a supplier</option>' + suppliers.map(supplier =>
                    `<option value="${supplier.id}">${escapeHtml(supplier.name)}</option>`
                ).join('');
                renderProducts();
            })
            .catch(error => {
                productList.innerHTML = `<p class="muted">${escapeHtml(error.message)}</p>`;
                showNotice(error.message);
            });
    </script>
@endsection
