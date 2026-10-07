<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Inventory System' }}</title>
    <style>
        :root {
            --bg: #f3f6fb;
            --card: #ffffff;
            --panel: #eef4ff;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --text: #0f172a;
            --muted: #475569;
            --border: #dbe3f0;
            --success: #16a34a;
            --danger: #dc2626;
            --shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        a { color: inherit; text-decoration: none; }

        .topbar {
            background: #0f172a;
            color: white;
            padding: 14px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
        }

        .brand {
            font-size: 1.1rem;
            font-weight: bold;
            letter-spacing: 0.04em;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .nav a {
            color: #dbeafe;
            font-size: 0.95rem;
        }

        .nav button {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            border-radius: 6px;
            padding: 7px 12px;
            cursor: pointer;
        }

        .container {
            max-width: 1180px;
            margin: 36px auto;
            padding: 0 24px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .auth-shell {
            max-width: 540px;
            margin: 80px auto;
            padding: 32px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            gap: 12px;
            flex-wrap: wrap;
        }

        .title {
            font-size: clamp(1.8rem, 2vw, 2.5rem);
            margin: 0;
        }

        .muted {
            color: var(--muted);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .stat {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 18px;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 6px 0 0;
        }

        .table-wrap {
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            text-align: left;
            padding: 14px 10px;
            border-bottom: 1px solid var(--border);
        }

        th {
            font-size: 0.8rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.07em;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: bold;
        }

        .status.in-stock { background: rgba(22,163,74,0.1); color: var(--success); }
        .status.low-stock { background: rgba(234,179,8,0.12); color: #a16207; }

        form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        label {
            font-size: 0.9rem;
            color: var(--muted);
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: white;
            font-size: 1rem;
        }

        input:focus {
            outline: 2px solid rgba(37, 99, 235, 0.2);
            border-color: var(--primary);
        }

        .btn {
            border: none;
            border-radius: 10px;
            padding: 12px 18px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover { background: var(--primary-dark); }

        .btn-secondary {
            background: #e2e8f0;
            color: var(--text);
        }

        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 0.94rem;
            margin-bottom: 18px;
        }

        .alert.error {
            background: rgba(220,38,38,0.08);
            color: var(--danger);
            border: 1px solid rgba(220,38,38,0.18);
        }

        .alert.success {
            background: rgba(22,163,74,0.08);
            color: var(--success);
            border: 1px solid rgba(22,163,74,0.15);
        }

        .inline-link {
            text-align: center;
            margin-top: 18px;
            color: var(--muted);
        }

        .inline-link a { color: var(--primary); font-weight: 600; }

        @media (max-width: 640px) {
            .topbar {
                padding: 12px 18px;
                align-items: flex-start;
                flex-direction: column;
                gap: 12px;
            }

            .nav {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="brand">Inventory System</div>
        <nav class="nav">
            <a href="/dashboard">Dashboard</a>
            <a href="/products">Products</a>
            <a href="/suppliers">Suppliers</a>
            <a href="/purchase-orders">Purchase Orders</a>
            @if(session('token') || request()->cookie('inventory_token'))
                <button id="logoutButton" type="button">Logout</button>
            @else
                <a href="/login">Login</a>
                <a href="/register">Register</a>
            @endif
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <script>
        document.getElementById('logoutButton')?.addEventListener('click', function () {
            localStorage.removeItem('inventory_token');
            window.location.href = '/login';
        });
    </script>
</body>
</html>
