@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="title">Suppliers</h1>
                <p class="muted">Manage the businesses you buy stock from</p>
            </div>
            <a class="btn btn-primary" href="/suppliers/create">Add Supplier</a>
        </div>

        <div class="card" style="padding: 20px; margin-bottom: 20px;">
            <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
                <input id="supplierSearch" type="search" placeholder="Search suppliers" style="max-width: 320px;">
                <select id="supplierStatus" style="padding: 12px 14px; border-radius: 10px; border: 1px solid var(--border);">
                    <option value="active">Active</option>
                    <option value="all">All</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Company</th>
                            <th>Phone</th>
                            <th>WhatsApp</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="supplierTableBody">
                        <tr>
                            <td colspan="7" class="muted">Loading suppliers...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('inventory_token');

        if (!token) {
            window.location.href = '/login';
        }

        const tableBody = document.getElementById('supplierTableBody');
        const searchInput = document.getElementById('supplierSearch');
        const statusInput = document.getElementById('supplierStatus');

        function renderStatus(isActive) {
            return isActive
                ? '<span class="status in-stock">Active</span>'
                : '<span class="status low-stock">Inactive</span>';
        }

        async function loadSuppliers() {
            const search = searchInput.value.trim();
            const status = statusInput.value;
            const params = new URLSearchParams();

            if (search) params.set('search', search);
            if (status && status !== 'all') params.set('status', status);

            if (status === 'active') {
                params.set('active_only', 'true');
            } else if (status === 'inactive') {
                params.set('active_only', 'false');
            }

            const response = await fetch('/api/v1/suppliers?' + params.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token,
                },
            });

            if (!response.ok) {
                throw new Error('Unable to load suppliers.');
            }

            const payload = await response.json();
            const suppliers = payload.data || [];

            if (!suppliers.length) {
                tableBody.innerHTML = '<tr><td colspan="7" class="muted">No suppliers found.</td></tr>';
                return;
            }

            tableBody.innerHTML = suppliers.map(supplier => `
                <tr>
                    <td>
                        <strong>${supplier.name || '—'}</strong>
                    </td>
                    <td>${supplier.company_name || '—'}</td>
                    <td>${supplier.phone || '—'}</td>
                    <td>${supplier.whatsapp_phone || '—'}</td>
                    <td>${supplier.email || '—'}</td>
                    <td>${renderStatus(supplier.is_active)}</td>
                    <td>
                        <a href="/suppliers/${supplier.id}" class="btn btn-secondary" style="padding: 8px 10px; display:inline-block; margin-right:4px;">View</a>
                        <a href="/suppliers/${supplier.id}/edit" class="btn btn-secondary" style="padding: 8px 10px; display:inline-block;">Edit</a>
                    </td>
                </tr>
            `).join('');
        }

        searchInput.addEventListener('input', loadSuppliers);
        statusInput.addEventListener('change', loadSuppliers);

        loadSuppliers().catch((error) => {
            tableBody.innerHTML = '<tr><td colspan="7" class="muted">' + error.message + '</td></tr>';
        });
    </script>
@endsection
