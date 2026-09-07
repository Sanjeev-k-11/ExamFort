<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ExamFort - Next-Gen AI Secure Proctoring & Examination Platform')</title>
    <meta name="description" content="ExamFort is a military-grade AI-proctored examination ecosystem for universities, institutes, and certification boards.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --bg-primary: #030712;
            --bg-surface: rgba(15, 23, 42, 0.75);
            --bg-surface-elevated: rgba(30, 41, 59, 0.85);
            --border-color: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(99, 102, 241, 0.35);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent-primary: #6366f1;
            --accent-gradient: linear-gradient(135deg, #6366f1 0%, #3b82f6 50%, #8b5cf6 100%);
            --accent-emerald: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --accent-cyan: #06b6d4;
            --radius-sm: 8px;
            --radius-md: 14px;
            --radius-lg: 20px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 20% 15%, rgba(99, 102, 241, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 80% 60%, rgba(59, 130, 246, 0.12) 0%, transparent 50%),
                radial-gradient(circle at 50% 90%, rgba(16, 185, 129, 0.08) 0%, transparent 45%);
            background-attachment: fixed;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Navbar */
        .public-nav {
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            background: rgba(3, 7, 18, 0.8);
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #fff;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--accent-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.4);
        }

        .brand-text {
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #fff 40%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 32px;
            list-style: none;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 600;
            transition: color 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-link:hover, .nav-link.active {
            color: #fff;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--accent-gradient);
            color: #fff;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.35);
            transition: all 0.25s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.5);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            border: 1px solid var(--border-color);
            transition: all 0.25s ease;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--border-hover);
        }

        .btn-emerald {
            background: var(--accent-emerald);
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.35);
        }

        .btn-emerald:hover {
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.5);
        }

        /* Glass Cards */
        .glass-panel {
            background: var(--bg-surface);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 32px;
            transition: all 0.3s ease;
        }

        .glass-panel:hover {
            border-color: var(--border-hover);
        }

        /* Footer */
        .public-footer {
            background: rgba(2, 6, 23, 0.9);
            border-top: 1px solid var(--border-color);
            padding: 60px 24px 30px;
            margin-top: 80px;
        }

        .footer-container {
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr repeat(3, 1fr);
            gap: 40px;
        }

        .footer-heading {
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .footer-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer-link {
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 13px;
            transition: color 0.2s;
        }

        .footer-link:hover {
            color: #818cf8;
        }

        @media (max-width: 900px) {
            .nav-links { display: none; }
            .footer-container { grid-template-columns: 1fr; }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Navigation Header -->
    <header class="public-nav">
        <div class="nav-container">
            <a href="{{ route('public.home') }}" class="brand-logo">
                <div class="brand-icon">
                    <i data-lucide="shield-check" style="color: white; width: 22px; height: 22px;"></i>
                </div>
                <div>
                    <span class="brand-text">ExamFort</span>
                    <span style="display: block; font-size: 9px; letter-spacing: 1px; color: #818cf8; font-weight: 700; text-transform: uppercase;">Secure Assessment Cloud</span>
                </div>
            </a>

            <nav>
                <ul class="nav-links">
                    <li><a href="{{ route('public.home') }}" class="nav-link {{ request()->routeIs('public.home') ? 'active' : '' }}">Overview</a></li>
                    <li><a href="{{ route('public.download') }}" class="nav-link {{ request()->routeIs('public.download') ? 'active' : '' }}"><i data-lucide="download" style="width: 14px; height: 14px;"></i> Downloads</a></li>
                    <li><a href="{{ route('public.about') }}" class="nav-link {{ request()->routeIs('public.about') ? 'active' : '' }}">About</a></li>
                    <li><a href="{{ route('public.help') }}" class="nav-link {{ request()->routeIs('public.help') ? 'active' : '' }}">Help &amp; Guide</a></li>
                    <li><a href="{{ route('public.contact') }}" class="nav-link {{ request()->routeIs('public.contact') ? 'active' : '' }}">Contact</a></li>
                </ul>
            </nav>

            <div style="display: flex; align-items: center; gap: 12px;">
                @if(session()->has('auth_user_id'))
                    <a href="{{ route('dashboard') }}" class="btn-primary">
                        <i data-lucide="layout-dashboard" style="width: 16px; height: 16px;"></i>
                        <span>Faculty Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-secondary">
                        <i data-lucide="log-in" style="width: 15px; height: 15px;"></i>
                        <span>Faculty / Admin Login</span>
                    </a>
                    <a href="{{ route('public.download') }}" class="btn-primary btn-emerald">
                        <i data-lucide="download" style="width: 15px; height: 15px;"></i>
                        <span>Get Client App</span>
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <main>
        @yield('content')
    </main>

    <!-- Global Footer -->
    <footer class="public-footer">
        <div class="footer-container">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                    <div class="brand-icon" style="width: 32px; height: 32px;">
                        <i data-lucide="shield-check" style="color: white; width: 18px; height: 18px;"></i>
                    </div>
                    <span style="font-family: 'Outfit', sans-serif; font-size: 20px; font-weight: 800; color: #fff;">ExamFort</span>
                </div>
                <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.6; max-width: 360px;">
                    Enterprise-grade AI-powered assessment ecosystem for universities, colleges, and national exam boards. Ensuring zero-compromise integrity with real-time multi-modal proctoring.
                </p>
                <div style="display: flex; gap: 10px; margin-top: 16px;">
                    <span style="font-size: 11px; padding: 4px 8px; border-radius: 4px; background: rgba(16, 185, 129, 0.15); color: #34d399; font-weight: 700;">ISO 27001 Certified</span>
                    <span style="font-size: 11px; padding: 4px 8px; border-radius: 4px; background: rgba(99, 102, 241, 0.15); color: #818cf8; font-weight: 700;">100% MySQL Engine</span>
                </div>
            </div>

            <div>
                <h4 class="footer-heading">Product & Suite</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('public.download') }}" class="footer-link">Windows Desktop Client (.exe)</a></li>
                    <li><a href="{{ route('public.download') }}" class="footer-link">Chrome Kiosk Extension</a></li>
                    <li><a href="{{ route('public.home') }}#features" class="footer-link">Live Audio/Video Radar</a></li>
                    <li><a href="{{ route('public.home') }}#sandbox" class="footer-link">Multi-Language Code Sandbox</a></li>
                    <li><a href="{{ route('public.home') }}#rubrics" class="footer-link">Dynamic AI Rubric Grader</a></li>
                </ul>
            </div>

            <div>
                <h4 class="footer-heading">Hierarchy & Roles</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('login') }}" class="footer-link">Super Administrator Portal</a></li>
                    <li><a href="{{ route('login') }}" class="footer-link">Dean / Principal Management</a></li>
                    <li><a href="{{ route('login') }}" class="footer-link">Teacher / Faculty Command</a></li>
                    <li><a href="{{ route('public.help') }}" class="footer-link">Candidate Secure Instructions</a></li>
                </ul>
            </div>

            <div>
                <h4 class="footer-heading">Support & Contact</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('public.help') }}" class="footer-link">Help & Troubleshooting</a></li>
                    <li><a href="{{ route('public.contact') }}" class="footer-link">Request Institute Demo</a></li>
                    <li><a href="{{ route('public.about') }}" class="footer-link">Security Whitepaper</a></li>
                    <li><a href="mailto:support@examfort.com" class="footer-link">support@examfort.com</a></li>
                </ul>
            </div>
        </div>

        <div style="max-width: 1280px; margin: 40px auto 0; padding-top: 24px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; font-size: 12px; color: var(--text-muted);">
            <div>&copy; {{ date('Y') }} ExamFort Inc. All rights reserved. Developed for High-Security Institutional Assessments.</div>
            <div class="mono" style="color: #818cf8;">Engineered with Laravel 12 & MySQL</div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
    @yield('scripts')
</body>
</html>
