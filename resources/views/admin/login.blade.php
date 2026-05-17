<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CardFlow Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: radial-gradient(circle at top, rgba(139,69,19,0.22), transparent 28rem), #1a1210;
            color: #f5e6d8;
            font-family: 'DM Sans', sans-serif;
        }
        .admin-login-card {
            width: 100%;
            max-width: 420px;
            padding: 40px;
            border: 1px solid #3d2b1f;
            border-radius: 20px;
            background: #2a1f1a;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .admin-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #3d2b1f;
            border-radius: 10px;
            background: #1a1210;
            color: #f5e6d8;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            outline: none;
        }
        .admin-input:focus { border-color: #8B4513; }
        .admin-input::placeholder { color: #6b4f3a; }
        .admin-label {
            display: block;
            margin-bottom: 6px;
            color: #c8956c;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .admin-btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 30px;
            background: #8B4513;
            color: #ffffff;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            font-weight: 700;
        }
        .admin-btn:hover { background: #6b3410; }
    </style>
</head>
<body>
    <div class="admin-login-card">
        <div style="text-align:center;margin-bottom:32px;">
            <div style="width:52px;height:52px;background:#8B4513;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;margin:0 auto 12px;">
                CF
            </div>
            <p style="font-size:0.65rem;text-transform:uppercase;letter-spacing:0.12em;color:#c8956c;margin:0 0 4px;">
                CARDFLOW
            </p>
            <h1 style="font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;color:#f5e6d8;margin:0;">
                Admin Panel
            </h1>
            <p style="font-size:0.8rem;color:#8B6F5E;margin:6px 0 0;">
                Restricted access. Authorized personnel only.
            </p>
        </div>

        @if($errors->any() || session('error'))
            <div style="background:#3d1515;border:1px solid #c0392b;border-radius:10px;padding:12px 16px;margin-bottom:20px;">
                <p style="color:#f8d7da;font-size:0.82rem;margin:0;">
                    {{ $errors->first() ?: session('error') }}
                </p>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div style="margin-bottom:16px;">
                <label class="admin-label">Email Address</label>
                <input type="email" name="email" class="admin-input" placeholder="admin@cardflow.test" value="{{ old('email') }}" required autofocus>
            </div>
            <div style="margin-bottom:24px;">
                <label class="admin-label">Password</label>
                <input type="password" name="password" class="admin-input" placeholder="Enter admin password" required>
            </div>
            <button type="submit" class="admin-btn">Sign in to Admin Panel</button>
        </form>

        <div style="text-align:center;margin-top:24px;">
            <a href="{{ route('login') }}" style="font-size:0.78rem;color:#8B6F5E;text-decoration:none;">
                Back to collector login
            </a>
        </div>
    </div>
</body>
</html>
