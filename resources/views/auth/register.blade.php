@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card auth-shell">
            <h1 class="title">Create your account</h1>
            <p class="muted" style="margin-top: 8px;">Start managing your shop and products in minutes.</p>

            <div id="registerMessage" class="alert error" style="display:none;"></div>

            <form id="registerForm">
                <div class="field">
                    <label for="name">Full name</label>
                    <input id="name" name="name" type="text" placeholder="Jane Smith" required>
                </div>

                <div class="field">
                    <label for="shop_name">Shop name</label>
                    <input id="shop_name" name="shop_name" type="text" placeholder="My Shop" required>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" placeholder="you@example.com" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" placeholder="Minimum 8 characters" required>
                </div>

                <button class="btn btn-primary" type="submit">Register</button>
            </form>

            <div class="inline-link">
                Already have an account? <a href="/login">Login</a>
            </div>
        </div>
    </div>

    <script>
        const registerForm = document.getElementById('registerForm');
        const registerMessage = document.getElementById('registerMessage');

        if (localStorage.getItem('inventory_token')) {
            window.location.href = '/dashboard';
        }

        registerForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            registerMessage.style.display = 'none';

            const payload = {
                name: document.getElementById('name').value,
                shop_name: document.getElementById('shop_name').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
            };

            try {
                const response = await fetch('/api/v1/auth/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Unable to create account.');
                }

                localStorage.setItem('inventory_token', result.token);
                window.location.href = '/dashboard';
            } catch (error) {
                registerMessage.textContent = error.message;
                registerMessage.style.display = 'block';
            }
        });
    </script>
@endsection
