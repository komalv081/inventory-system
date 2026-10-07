@extends('layouts.app')

@section('content')
    <div class="container" style="max-width: 900px;">
        <div class="page-header">
            <div>
                <h1 class="title">Supplier Details</h1>
                <p class="muted">Review supplier information and status</p>
            </div>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a class="btn btn-secondary" href="/suppliers">Back</a>
                <a class="btn btn-primary" id="editSupplierButton" href="#">Edit</a>
            </div>
        </div>

        <div class="card" style="padding: 24px;">
            <div id="supplierDetails" class="muted">Loading supplier details...</div>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('inventory_token');

        if (!token) {
            window.location.href = '/login';
        }

        const supplierId = '{{ $supplierId ?? request()->route('supplier') }}';

        async function loadSupplier() {
            const response = await fetch('/api/v1/suppliers/' + supplierId, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token,
                },
            });

            if (!response.ok) {
                throw new Error('Supplier not found.');
            }

            const supplier = (await response.json()).data;
            const state = supplier.is_active ? 'Active' : 'Inactive';

            document.getElementById('editSupplierButton').setAttribute('href', '/suppliers/' + supplier.id + '/edit');
            document.getElementById('supplierDetails').innerHTML = `
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px;">
                    <div><strong>Supplier name:</strong><br>${supplier.name || '—'}</div>
                    <div><strong>Company name:</strong><br>${supplier.company_name || '—'}</div>
                    <div><strong>Phone:</strong><br>${supplier.phone || '—'}</div>
                    <div><strong>WhatsApp:</strong><br>${supplier.whatsapp_phone || '—'}</div>
                    <div><strong>Email:</strong><br>${supplier.email || '—'}</div>
                    <div><strong>Status:</strong><br><span class="status ${supplier.is_active ? 'in-stock' : 'low-stock'}">${state}</span></div>
                    <div style="grid-column: 1 / -1;"><strong>Address:</strong><br>${supplier.address || '—'}</div>
                    <div style="grid-column: 1 / -1;"><strong>Notes:</strong><br>${supplier.notes || '—'}</div>
                    <div style="grid-column: 1 / -1;"><strong>Products Usually Purchased From This Supplier:</strong><br>Supplier-product purchasing will be added in the next stage.</div>
                </div>
            `;
        }

        loadSupplier().catch((error) => {
            document.getElementById('supplierDetails').innerHTML = '<div class="alert error">' + error.message + '</div>';
        });
    </script>
@endsection
