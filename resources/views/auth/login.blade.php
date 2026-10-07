@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card auth-shell">
            <h1 class="title">Welcome back</h1>
            <p class="muted" style="margin-top: 8px;">Sign in to manage inventory and stock.</p>

            <div id="loginMessage" class="alert error" style="display:none;"></div>

            <form id="loginForm">
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" placeholder="you@example.com" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" placeholder="Enter password" required>
                </div>

                <button class="btn btn-primary" type="submit">Login</button>
            </form>

            <div class="inline-link">
                No account yet? <a href="/register">Create one</a>
            </div>
        </div>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const loginMessage = document.getElementById('loginMessage');

        if (localStorage.getItem('inventory_token')) {
            window.location.href = '/dashboard';
        }

        loginForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            loginMessage.style.display = 'none';

            const payload = {
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
            };

            try {
                const response = await fetch('/api/v1/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Unable to log in.');
                }

                localStorage.setItem('inventory_token', result.token);
                window.location.href = '/dashboard';
            } catch (error) {
                loginMessage.textContent = error.message;
                loginMessage.style.display = 'block';
            }
        });
    </script>
@endsection
