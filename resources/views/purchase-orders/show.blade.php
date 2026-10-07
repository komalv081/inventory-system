@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <p class="muted" style="margin:0 0 6px;">PURCHASE ORDER #{{ $purchaseOrderId }}</p>
                <h1 class="title">Order details</h1>
            </div>
            <a class="btn btn-secondary" href="/purchase-orders">Back to orders</a>
        </div>

        <div id="detailNotice" class="alert" role="status" aria-live="polite" hidden></div>
        <div id="orderDetail" class="detail-layout"><p class="muted">Loading order...</p></div>
    </div>

    <style>
        .detail-layout { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(260px, .75fr); gap: 18px; align-items: start; }
        .detail-section { padding: 22px; margin-bottom: 18px; }
        .detail-section h2 { font-size: 1.15rem; margin: 0 0 16px; }
        .supplier-name { font-size: 1.25rem; font-weight: 700; }
        .item-line, .communication-line { padding: 12px 0; border-top: 1px solid var(--border); }
        .item-line { display: flex; justify-content: space-between; gap: 16px; }
        .communication-line p { white-space: pre-wrap; margin: 8px 0; }
        .message-editor textarea { width: 100%; min-height: 200px; padding: 13px; border: 1px solid var(--border); border-radius: 6px; font: inherit; line-height: 1.5; resize: vertical; }
        .detail-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; }
        @media (max-width: 760px) { .detail-layout { grid-template-columns: 1fr; } .detail-section { padding: 18px; } }
    </style>

    <script>
        const token = localStorage.getItem('inventory_token');
        if (!token) window.location.href = '/login';
        const orderId = @json($purchaseOrderId);
        const detail = document.getElementById('orderDetail');
        const notice = document.getElementById('detailNotice');
        let order = null;

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, character => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
            })[character]);
        }

        function showNotice(message, success = false) {
            notice.hidden = false;
            notice.className = 'alert ' + (success ? 'success' : 'error');
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
                },
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'The request could not be completed.');
            return payload;
        }

        function renderOrder() {
            const canEdit = ['draft', 'reviewed'].includes(order.status);
            const communications = (order.communications || []).length
                ? order.communications.map(item => `<div class="communication-line">
                    <strong>${escapeHtml(item.channel)} · ${escapeHtml(item.status)}</strong>
                    <span class="muted">${item.created_at ? new Date(item.created_at).toLocaleString() : ''}</span>
                    <p>${escapeHtml(item.message)}</p>
                </div>`).join('')
                : '<p class="muted">No messages have been opened for this order.</p>';

            detail.innerHTML = `
                <div>
                    <section class="card detail-section">
                        <h2>Supplier</h2>
                        <div class="supplier-name">${escapeHtml(order.supplier?.name || 'Supplier unavailable')}</div>
                        <p class="muted">Phone: ${escapeHtml(order.supplier?.phone || 'Not provided')}</p>
                        <p>Status: <strong class="state-text">${escapeHtml(order.status)}</strong></p>
                    </section>
                    <section class="card detail-section">
                        <h2>Products</h2>
                        ${(order.items || []).map(item => `<div class="item-line">
                            <span><strong>${escapeHtml(item.product_name)}</strong><br><span class="muted">${escapeHtml(item.barcode || '')}</span></span>
                            <strong>${Number(item.quantity)} ${escapeHtml(item.unit || 'pcs')}</strong>
                        </div>`).join('')}
                    </section>
                </div>
                <div>
                    <section class="card detail-section message-editor">
                        <h2>Message</h2>
                        <textarea id="messageText" maxlength="10000" ${canEdit ? '' : 'readonly'}>${escapeHtml(order.message || '')}</textarea>
                        <div id="detailHandoffNote" class="muted" role="status" aria-live="polite"></div>
                        <div id="detailHandoffLink"></div>
                        <div class="detail-actions">
                            ${canEdit ? '<button id="saveOrderDraft" class="btn btn-secondary" type="button">Save draft</button><button class="btn btn-primary detail-handoff" data-channel="whatsapp" type="button">WhatsApp</button><button class="btn btn-primary detail-handoff" data-channel="sms" type="button">SMS</button><button id="cancelOrder" class="btn btn-secondary" type="button">Cancel order</button>' : ''}
                        </div>
                    </section>
                    <section class="card detail-section">
                        <h2>Communication history</h2>
                        ${communications}
                    </section>
                </div>
            `;

            document.getElementById('saveOrderDraft')?.addEventListener('click', saveDraft);
            document.getElementById('cancelOrder')?.addEventListener('click', cancelOrder);
            document.querySelectorAll('.detail-handoff').forEach(button => button.addEventListener('click', () => handoff(button.dataset.channel)));
        }

        async function saveDraft() {
            try {
                const payload = await api(`/api/v1/purchase-orders/${orderId}/draft`, {
                    method: 'PUT', body: JSON.stringify({ message: document.getElementById('messageText').value }),
                });
                order = payload.data;
                showNotice('Draft saved. No message was sent.', true);
                renderOrder();
            } catch (error) { showNotice(error.message); }
        }

        async function cancelOrder() {
            if (!window.confirm('Cancel this purchase order?')) return;
            try {
                const payload = await api(`/api/v1/purchase-orders/${orderId}/cancel`, { method: 'POST' });
                order = payload.data;
                showNotice('Order cancelled.', true);
                renderOrder();
            } catch (error) { showNotice(error.message); }
        }

        async function handoff(channel) {
            const phone = channel === 'whatsapp' ? order.supplier?.whatsapp_phone : order.supplier?.phone;
            if (!phone) { showNotice('The supplier does not have a phone number for this channel.'); return; }
            const channelName = channel === 'whatsapp' ? 'WhatsApp' : 'SMS';
            if (!document.getElementById('messageText').value.trim()) { showNotice('The message cannot be empty.'); return; }
            const message = document.getElementById('messageText').value.trim();

            try {
                const payload = await api(`/api/v1/purchase-orders/${orderId}/handoff`, {
                    method: 'POST',
                    body: JSON.stringify({ channel, message }),
                });
                order = payload.data;
                renderOrder();

                if (payload.handoff_url) {
                    document.getElementById('detailHandoffNote').textContent = `Opening ${channelName}. The message is not sent until you press Send in ${channelName}.`;
                    document.getElementById('detailHandoffLink').innerHTML = `<a id="detailComposerLink" target="_blank" rel="noopener noreferrer" href="${escapeHtml(payload.handoff_url)}">Open message composer</a>`;
                    window.location.assign(payload.handoff_url);
                } else {
                    document.getElementById('detailHandoffNote').textContent = `SMS handoff result through Twilio: ${payload.handoff_status}.`;
                    document.getElementById('detailHandoffLink').innerHTML = '';
                    showNotice(`SMS handoff recorded as ${payload.handoff_status}.`, payload.handoff_status === 'sent' ? 'success' : 'error');
                }
            } catch (error) { showNotice(error.message); }
        }

        api(`/api/v1/purchase-orders/${orderId}`).then(payload => {
            order = payload.data;
            renderOrder();
        }).catch(error => {
            detail.innerHTML = '';
            showNotice(error.message);
        });
    </script>
@endsection
