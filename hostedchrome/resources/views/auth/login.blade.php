<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institutional Login | ExamFort Portal</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --bg-base: #f8fafc;
            --bg-card: rgba(255, 255, 255, 0.95);
            --border: rgba(226, 232, 240, 0.9);
            --primary: #4f46e5;
            --primary-glow: rgba(79, 70, 229, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg-base);
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background-image: 
                radial-gradient(at 10% 20%, rgba(99, 102, 241, 0.12) 0px, transparent 50%),
                radial-gradient(at 90% 80%, rgba(6, 182, 212, 0.12) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(244, 63, 94, 0.06) 0px, transparent 50%);
            background-attachment: fixed;
        }

        .login-box {
            width: 100%;
            max-width: 480px;
            background: var(--bg-card);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 40px 36px;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(255, 255, 255, 0.8) inset;
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
            background: linear-gradient(90deg, #4f46e5, #06b6d4, #10b981);
        }

        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.4);
            margin-bottom: 16px;
        }

        .brand-name {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .brand-subtitle {
            font-size: 13.5px;
            color: #64748b;
            margin-top: 4px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
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
            color: #94a3b8;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px 12px 44px;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            color: #0f172a;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-glow);
            background: #ffffff;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 15px var(--primary-glow);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .demo-accounts {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #64748b;
        }

        .demo-chip {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 14px;
            border-radius: 10px;
            display: block;
            margin-top: 8px;
            cursor: pointer;
            transition: all 0.15s ease;
            color: #334155;
        }

        .demo-chip:hover {
            background: #eef2ff;
            border-color: #c7d2fe;
            color: #0f172a;
            transform: translateX(4px);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #4f46e5;
            font-size: 13px;
            text-decoration: none;
            font-weight: 700;
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
            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #059669; padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 20px; font-weight: 600;">
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
            <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px;">Click to Quick-Fill Credentials:</div>
            <div class="demo-chip" onclick="fillCreds('admin@examfort.com', 'admin123')">
                🛡️ <strong>Super Admin:</strong> <span style="color: #4f46e5; font-weight: 700;">admin@examfort.com</span> / admin123
            </div>
            <div class="demo-chip" onclick="fillCreds('principal@examfort.com', 'principal123')">
                🏛️ <strong>Dean / Principal:</strong> <span style="color: #d97706; font-weight: 700;">principal@examfort.com</span> / principal123
            </div>
            <div class="demo-chip" onclick="fillCreds('teacher@examfort.com', 'teacher123')">
                🎓 <strong>Teacher / Proctor:</strong> <span style="color: #059669; font-weight: 700;">teacher@examfort.com</span> / teacher123
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
