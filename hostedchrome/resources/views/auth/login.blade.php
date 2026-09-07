<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institutional Login | ExamFort Portal</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --bg-base: #060911;
            --bg-card: rgba(15, 23, 42, 0.85);
            --border: rgba(255, 255, 255, 0.08);
            --primary: #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.35);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg-base);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(14, 165, 233, 0.12) 0%, transparent 40%);
        }

        .login-box {
            width: 100%;
            max-width: 480px;
            background: var(--bg-card);
            backdrop-filter: blur(24px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 40px 36px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            position: relative;
            overflow: hidden;
        }

        .login-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6366f1, #3b82f6, #06b6d4);
        }

        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #6366f1, #3b82f6);
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 0 25px var(--primary-glow);
            margin-bottom: 16px;
        }

        .brand-name {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .brand-subtitle {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #64748b;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px 12px 44px;
            background: rgba(10, 15, 29, 0.8);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: #fff;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
            background: rgba(15, 23, 42, 1);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            color: white;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 15px var(--primary-glow);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.5);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .demo-accounts {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid var(--border);
            font-size: 12px;
            color: #94a3b8;
        }

        .demo-chip {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            padding: 8px 12px;
            border-radius: 8px;
            display: block;
            margin-top: 8px;
            cursor: pointer;
            transition: all 0.15s;
        }

        .demo-chip:hover {
            background: rgba(99, 102, 241, 0.15);
            border-color: rgba(99, 102, 241, 0.4);
            color: #fff;
            transform: translateX(4px);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #818cf8;
            font-size: 13px;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-box">
        <div class="brand-header">
            <div class="brand-icon">
                <i data-lucide="shield-check" style="width: 32px; height: 32px;"></i>
            </div>
            <h1 class="brand-name">EXAMFORT PORTAL</h1>
            <p class="brand-subtitle">Admin, Principal & Faculty Command Center</p>
        </div>

        @if(session('error'))
            <div class="alert-error">
                <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(session('success'))
            <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Email or Faculty / Admin ID</label>
                <div class="input-wrapper">
                    <i data-lucide="user" class="input-icon" style="width: 18px; height: 18px;"></i>
                    <input type="text" id="emailInput" name="email" class="form-control" placeholder="admin@examfort.com" value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrapper">
                    <i data-lucide="lock" class="input-icon" style="width: 18px; height: 18px;"></i>
                    <input type="password" id="passwordInput" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <span>Sign In to Command Center</span>
                <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
            </button>
        </form>

        <div class="demo-accounts">
            <div><strong>Click to Quick-Fill Credentials:</strong></div>
            <div class="demo-chip" onclick="fillCreds('admin@examfort.com', 'admin123')">
                🛡️ <strong>Super Admin:</strong> <span style="color: #818cf8;">admin@examfort.com</span> / admin123
            </div>
            <div class="demo-chip" onclick="fillCreds('principal@examfort.com', 'principal123')">
                🏛️ <strong>Dean / Principal:</strong> <span style="color: #fbbf24;">principal@examfort.com</span> / principal123
            </div>
            <div class="demo-chip" onclick="fillCreds('teacher@examfort.com', 'teacher123')">
                🎓 <strong>Teacher / Proctor:</strong> <span style="color: #34d399;">teacher@examfort.com</span> / teacher123
            </div>
        </div>

        <div class="back-link">
            <a href="{{ route('public.home') }}">&larr; Back to Public Website</a>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function fillCreds(email, pass) {
            document.getElementById('emailInput').value = email;
            document.getElementById('passwordInput').value = pass;
        }
    </script>
</body>
</html>
