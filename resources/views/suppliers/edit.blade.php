@extends('layouts.app')

@section('content')
    <div class="container" style="max-width: 800px;">
        <div class="page-header">
            <div>
                <h1 class="title">Edit Supplier</h1>
                <p class="muted">Update supplier information</p>
            </div>
            <a class="btn btn-secondary" href="/suppliers">Back to suppliers</a>
        </div>

        <div class="card" style="padding: 24px;">
            <form id="supplierForm">
                <div class="field">
                    <label for="name">Supplier Name</label>
                    <input id="name" name="name" type="text" required>
                </div>

                <div class="field">
                    <label for="company_name">Company Name</label>
                    <input id="company_name" name="company_name" type="text">
                </div>

                <div class="field">
                    <label for="phone">Phone Number</label>
                    <input id="phone" name="phone" type="text" required>
                </div>

                <div class="field">
                    <label for="whatsapp_phone">WhatsApp Number</label>
                    <input id="whatsapp_phone" name="whatsapp_phone" type="text">
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email">
                </div>

                <div class="field">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3"></textarea>
                </div>

                <div class="field">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>

                <div class="field" style="display:flex; flex-direction:row; align-items:center; gap:10px;">
                    <input id="is_active" name="is_active" type="checkbox">
                    <label for="is_active" style="margin:0;">Active</label>
                </div>

                <div id="supplierMessage" class="alert success" style="display:none;"></div>

                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                    <button class="btn btn-primary" type="submit">Update Supplier</button>
                    <a class="btn btn-secondary" href="/suppliers">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('inventory_token');

        if (!token) {
            window.location.href = '/login';
        }

        const supplierId = '{{ $supplierId ?? request()->route('supplier') }}';

        async function hydrateForm() {
            const response = await fetch('/api/v1/suppliers/' + supplierId, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token,
                },
            });

            if (!response.ok) {
                throw new Error('Could not load supplier.');
            }

            const supplier = (await response.json()).data;
            document.getElementById('name').value = supplier.name || '';
            document.getElementById('company_name').value = supplier.company_name || '';
            document.getElementById('phone').value = supplier.phone || '';
            document.getElementById('whatsapp_phone').value = supplier.whatsapp_phone || '';
            document.getElementById('email').value = supplier.email || '';
            document.getElementById('address').value = supplier.address || '';
            document.getElementById('notes').value = supplier.notes || '';
            document.getElementById('is_active').checked = !!supplier.is_active;
        }

        document.getElementById('supplierForm').addEventListener('submit', async function (event) {
            event.preventDefault();

            const payload = {
                name: document.getElementById('name').value,
                company_name: document.getElementById('company_name').value,
                phone: document.getElementById('phone').value,
                whatsapp_phone: document.getElementById('whatsapp_phone').value,
                email: document.getElementById('email').value,
                address: document.getElementById('address').value,
                notes: document.getElementById('notes').value,
                is_active: document.getElementById('is_active').checked,
            };

            const message = document.getElementById('supplierMessage');

            try {
                const response = await fetch('/api/v1/suppliers/' + supplierId, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token,
                    },
                    body: JSON.stringify(payload),
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || Object.values(result.errors || {})[0]?.[0] || 'Unable to update supplier.');
                }

                message.className = 'alert success';
                message.textContent = 'Supplier updated successfully.';
                message.style.display = 'block';
                window.location.href = '/suppliers';
            } catch (error) {
                message.className = 'alert error';
                message.textContent = error.message;
                message.style.display = 'block';
            }
        });

        hydrateForm().catch((error) => {
            document.getElementById('supplierMessage').className = 'alert error';
            document.getElementById('supplierMessage').textContent = error.message;
            document.getElementById('supplierMessage').style.display = 'block';
        });
    </script>
@endsection
