<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ExamFort - Institutional Command Center')</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        :root {
            /* Canvas & Light Surfaces */
            --bg-primary: #f8fafc;
            --bg-secondary: #ffffff;
            --bg-card: rgba(255, 255, 255, 0.85);
            --bg-card-hover: rgba(255, 255, 255, 0.96);
            --bg-card-subtle: rgba(248, 250, 252, 0.75);
            
            /* Glassmorphism & Borders */
            --border-color: rgba(226, 232, 240, 0.85);
            --border-glass: rgba(255, 255, 255, 0.95);
            --border-hover: rgba(99, 102, 241, 0.45);
            --border-highlight: rgba(99, 102, 241, 0.25);
            
            /* Typography Palette */
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --text-dark: #0f172a;
            
            /* Vivid Harmonious Accents */
            --accent-primary: #4f46e5;
            --accent-indigo: #6366f1;
            --accent-gradient: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            --accent-purple: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
            --accent-emerald: #059669;
            --accent-amber: #d97706;
            --accent-rose: #e11d48;
            --accent-cyan: #0284c7;
            
            /* Shadows & Glows */
            --glass-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.05), 0 4px 12px -2px rgba(15, 23, 42, 0.02);
            --glass-shadow-hover: 0 20px 35px -8px rgba(79, 70, 229, 0.12), 0 8px 16px -4px rgba(15, 23, 42, 0.04);
            --primary-glow: rgba(79, 70, 229, 0.25);
            
            /* Radii */
            --radius-sm: 8px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-xl: 24px;
            --sidebar-width: 275px;
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
            display: flex;
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.09) 0px, transparent 45%),
                radial-gradient(at 100% 0%, rgba(14, 165, 233, 0.08) 0px, transparent 45%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.07) 0px, transparent 45%),
                radial-gradient(at 0% 100%, rgba(244, 63, 94, 0.06) 0px, transparent 45%);
            background-attachment: fixed;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Frosted Glass Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 50;
            box-shadow: 4px 0 24px rgba(15, 23, 42, 0.03);
        }

        .sidebar-header {
            padding: 22px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.5);
        }

        .sidebar-logo-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-md);
            background: var(--accent-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
            flex-shrink: 0;
        }

        .sidebar-logo-title {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #0f172a;
            line-height: 1.1;
        }

        .sidebar-nav {
            padding: 18px 12px;
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(203, 213, 225, 0.8);
            border-radius: 4px;
        }

        .nav-category {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-muted);
            padding: 14px 12px 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: var(--radius-md);
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        .nav-item:hover {
            color: #0f172a;
            background: rgba(79, 70, 229, 0.06);
            transform: translateX(2px);
        }

        .nav-item.active {
            color: #4338ca;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.12) 0%, rgba(59, 130, 246, 0.08) 100%);
            border: 1px solid rgba(99, 102, 241, 0.25);
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.08);
        }

        .nav-item.active i {
            color: var(--accent-primary);
        }

        .nav-badge {
            margin-left: auto;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: rgba(16, 185, 129, 0.15);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }

        .sidebar-footer {
            padding: 16px 18px;
            border-top: 1px solid var(--border-color);
            background: rgba(248, 250, 252, 0.8);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow: hidden;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--accent-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
            color: white;
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
            flex-shrink: 0;
        }

        .user-meta {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .user-name {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            max-width: 125px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-role-badge {
            font-size: 10px;
            color: #4f46e5;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .logout-btn {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #dc2626;
            cursor: pointer;
            padding: 7px;
            border-radius: var(--radius-sm);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-btn:hover {
            color: #fff;
            background: #ef4444;
            transform: scale(1.05);
        }

        /* Main Workspace */
        .workspace {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            height: 64px;
            border-bottom: 1px solid var(--border-color);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            background: rgba(255, 255, 255, 0.85);
            position: sticky;
            top: 0;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.02);
        }

        .page-content {
            padding: 32px;
            flex: 1;
        }

        /* Glassmorphism Cards */
        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid var(--border-glass);
            outline: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--glass-shadow);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        .glass-card:hover {
            border-color: rgba(99, 102, 241, 0.35);
            box-shadow: var(--glass-shadow-hover);
        }

        .card-header-flex {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .card-title {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Metrics Grid & Morphism Metric Cards */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .metric-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid var(--border-glass);
            outline: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: var(--radius-lg);
            padding: 22px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--glass-shadow);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-gradient, linear-gradient(90deg, #4f46e5, #3b82f6));
        }

        .metric-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--glass-shadow-hover);
            border-color: rgba(99, 102, 241, 0.35);
        }

        .metric-icon-box {
            width: 52px;
            height: 52px;
            border-radius: var(--radius-md);
            background: rgba(79, 70, 229, 0.08);
            border: 1px solid rgba(79, 70, 229, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-color, #4f46e5);
            flex-shrink: 0;
        }

        .metric-info {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .metric-value {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        .metric-label {
            font-size: 12.5px;
            color: var(--text-secondary);
            font-weight: 600;
            margin-top: 4px;
        }

        /* Polished Light Table */
        .custom-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13.5px;
        }

        .custom-table th {
            background: rgba(241, 245, 249, 0.85);
            color: #475569;
            font-weight: 700;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 14px 18px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .custom-table th:first-child {
            border-top-left-radius: var(--radius-sm);
        }
        .custom-table th:last-child {
            border-top-right-radius: var(--radius-sm);
        }

        .custom-table td {
            padding: 16px 18px;
            border-bottom: 1px solid rgba(226, 232, 240, 0.7);
            color: var(--text-secondary);
            vertical-align: middle;
            background: rgba(255, 255, 255, 0.6);
            transition: background 0.15s ease;
        }

        .custom-table tr:hover td {
            background: rgba(248, 250, 252, 0.95);
            color: #0f172a;
        }

        /* Status Pills */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .status-pill.active {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .status-pill.upcoming {
            background: rgba(245, 158, 11, 0.12);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .status-pill.completed {
            background: rgba(100, 116, 139, 0.12);
            color: #475569;
            border: 1px solid rgba(100, 116, 139, 0.25);
        }

        .status-pill.danger {
            background: rgba(239, 68, 68, 0.12);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        /* Forms & Inputs */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #cbd5e1;
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            color: #0f172a;
            font-size: 13.5px;
            font-family: inherit;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .form-control:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
            background: #ffffff;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        input[type="date"], input[type="time"] {
            color-scheme: light;
            cursor: pointer;
        }

        /* Standard & Modern Button System */
        .btn, a.btn, button.btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none !important;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid transparent;
            font-family: inherit;
            line-height: 1.4;
            white-space: nowrap;
        }

        .btn-primary, a.btn-primary, button.btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3) !important;
        }

        .btn-primary:hover, a.btn-primary:hover, button.btn-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #2563eb 100%) !important;
            transform: translateY(-1.5px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45) !important;
            color: #ffffff !important;
        }

        .btn-secondary, a.btn-secondary, button.btn-secondary {
            background: rgba(255, 255, 255, 0.95) !important;
            color: #334155 !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
        }

        .btn-secondary:hover, a.btn-secondary:hover, button.btn-secondary:hover {
            background: #ffffff !important;
            border-color: #94a3b8 !important;
            color: #0f172a !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08) !important;
        }

        .btn-emerald, a.btn-emerald, button.btn-emerald {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3) !important;
        }

        .btn-emerald:hover, a.btn-emerald:hover, button.btn-emerald:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
            transform: translateY(-1.5px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45) !important;
            color: #ffffff !important;
        }

        .btn-danger, a.btn-danger, button.btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3) !important;
        }

        .btn-danger:hover, a.btn-danger:hover, button.btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
            transform: translateY(-1.5px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.45) !important;
            color: #ffffff !important;
        }

        /* Quick Action Buttons */
        .quick-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--accent-gradient);
            color: #fff;
            padding: 9px 18px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none !important;
            border: none;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
        }

        .quick-action-btn:hover {
            transform: translateY(-1.5px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45);
            color: #fff !important;
        }

        .quick-action-btn.secondary {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #cbd5e1;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            color: #334155;
        }

        .quick-action-btn.secondary:hover {
            background: #ffffff;
            border-color: #94a3b8;
            color: #0f172a !important;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
        }

        .quick-action-btn.danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3);
            color: #fff;
        }

        /* Modal Overlay */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(12px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal-box {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(24px);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            width: 100%;
            max-width: 650px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 28px;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.2);
            color: #0f172a;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #047857;
            padding: 14px 20px;
            border-radius: var(--radius-md);
            margin-bottom: 24px;
            font-size: 13.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.08);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #b91c1c;
            padding: 14px 20px;
            border-radius: var(--radius-md);
            margin-bottom: 24px;
            font-size: 13.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.08);
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: var(--radius-md);
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo-icon">
                <i data-lucide="shield-check" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
                <div class="sidebar-logo-title">ExamFort</div>
                <div style="font-size: 10px; color: #4f46e5; font-weight: 800; letter-spacing: 0.6px; text-transform: uppercase;">
                    {{ session('auth_user_role', 'PORTAL') }} COMMAND
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-category">Command & Control</div>
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard" style="width: 18px; height: 18px;"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('monitoring.index') }}" class="nav-item {{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
                <i data-lucide="radio" style="width: 18px; height: 18px; color: #059669;"></i>
                <span>Live Radar</span>
                <span class="nav-badge">LIVE</span>
            </a>

            @if(in_array(session('auth_user_role'), ['ADMIN', 'SUPERADMIN']))
                <div class="nav-category">Multi-Tenant Governance</div>
                <a href="{{ route('organizations.index') }}" class="nav-item {{ request()->routeIs('organizations.*') ? 'active' : '' }}">
                    <i data-lucide="building-2" style="width: 18px; height: 18px;"></i>
                    <span>Organizations</span>
                </a>
                <a href="{{ route('principals.index') }}" class="nav-item {{ request()->routeIs('principals.*') ? 'active' : '' }}">
                    <i data-lucide="crown" style="width: 18px; height: 18px; color: #d97706;"></i>
                    <span>Principals &amp; Deans</span>
                </a>
                <a href="{{ route('inquiries.index') }}" class="nav-item {{ request()->routeIs('inquiries.*') ? 'active' : '' }}">
                    <i data-lucide="mail-question" style="width: 18px; height: 18px; color: #0284c7;"></i>
                    <span>Consultation Leads</span>
                    @php
                        $unreadInq = \App\Models\ContactInquiry::where('status', 'PENDING')->count();
                    @endphp
                    @if($unreadInq > 0)
                        <span class="nav-badge" style="background: rgba(239, 68, 68, 0.15); color: #dc2626;">{{ $unreadInq }} New</span>
                    @endif
                </a>
            @endif

            <div class="nav-category">Faculty & Students</div>
            @php
                $sidebarUser = \App\Models\User::find(session('auth_user_id'));
                $canAccessTeachers = in_array(session('auth_user_role'), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']) || ($sidebarUser && $sidebarUser->canCreateTeachers());
            @endphp
            @if($canAccessTeachers)
                <a href="{{ route('teachers.index') }}" class="nav-item {{ request()->routeIs('teachers.*') ? 'active' : '' }}">
                    <i data-lucide="users-2" style="width: 18px; height: 18px;"></i>
                    <span>Faculty / Teachers</span>
                </a>
            @endif
            <a href="{{ route('candidates.index') }}" class="nav-item {{ request()->routeIs('candidates.*') ? 'active' : '' }}">
                <i data-lucide="graduation-cap" style="width: 18px; height: 18px;"></i>
                <span>Enrolled Students</span>
            </a>

            <div class="nav-category">Campus Placements</div>
            <a href="{{ route('placement-exams.index') }}" class="nav-item {{ request()->routeIs('placement-exams.*') ? 'active' : '' }}" style="background: rgba(99, 102, 241, 0.06); border: 1px solid rgba(99, 102, 241, 0.18);">
                <i data-lucide="briefcase" style="width: 18px; height: 18px; color: #4f46e5;"></i>
                <span style="font-weight: 700; color: #1e293b;">Placement Drives</span>
                @php
                    $drivesCount = \App\Models\PlacementExam::where('status', 'ACTIVE')->orWhere('status', 'SCHEDULED')->count();
                @endphp
                @if($drivesCount > 0)
                    <span class="nav-badge" style="background: linear-gradient(135deg, #10b981, #059669); color: white;">{{ $drivesCount }} Active</span>
                @endif
            </a>

            <div class="nav-category">Assessments & Evaluation</div>
            <a href="{{ route('ai.generator') }}" class="nav-item {{ request()->routeIs('ai.*') ? 'active' : '' }}" style="color: #4338ca; background: rgba(99, 102, 241, 0.08); border: 1px dashed rgba(99, 102, 241, 0.35);">
                <i data-lucide="sparkles" style="width: 18px; height: 18px; color: #0284c7;"></i>
                <span style="font-weight: 700;">AI Paper Generator</span>
                <span class="nav-badge" style="background: linear-gradient(135deg, #4f46e5, #0284c7); color: white;">AI ✨</span>
            </a>
            <a href="{{ route('exams.index') }}" class="nav-item {{ request()->routeIs('exams.*') ? 'active' : '' }}">
                <i data-lucide="file-text" style="width: 18px; height: 18px;"></i>
                <span>Exams & Papers</span>
            </a>
            <a href="{{ route('questions.index') }}" class="nav-item {{ request()->routeIs('questions.*') ? 'active' : '' }}">
                <i data-lucide="list-checks" style="width: 18px; height: 18px;"></i>
                <span>Question Bank</span>
            </a>
            <a href="{{ route('submissions.index') }}" class="nav-item {{ request()->routeIs('submissions.*') ? 'active' : '' }}">
                <i data-lucide="check-circle-2" style="width: 18px; height: 18px;"></i>
                <span>Submissions & Grading</span>
            </a>

            <div class="nav-category">Curriculum & Audit</div>
            <a href="{{ route('courses.index') }}" class="nav-item {{ request()->routeIs('courses.*') ? 'active' : '' }}">
                <i data-lucide="book-open" style="width: 18px; height: 18px;"></i>
                <span>Curriculum & Lessons</span>
            </a>
            <a href="{{ route('violations.index') }}" class="nav-item {{ request()->routeIs('violations.*') ? 'active' : '' }}">
                <i data-lucide="shield-alert" style="width: 18px; height: 18px; color: #dc2626;"></i>
                <span>Threat Incidents</span>
            </a>

            <div class="nav-category">Public Website</div>
            <a href="{{ route('public.home') }}" target="_blank" class="nav-item">
                <i data-lucide="globe" style="width: 18px; height: 18px; color: #0284c7;"></i>
                <span>View Public Site &rarr;</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-pill">
                <div class="user-avatar">
                    {{ substr(session('auth_user_name', 'U'), 0, 1) }}
                </div>
                <div class="user-meta">
                    <span class="user-name">{{ session('auth_user_name', 'Faculty User') }}</span>
                    <span class="user-role-badge">{{ session('auth_user_role', 'PROCTOR') }}</span>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn" title="Sign Out">
                    <i data-lucide="log-out" style="width: 17px; height: 17px;"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Workspace -->
    <div class="workspace">
        <header class="topbar">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 14px; font-weight: 700; color: #0f172a;">
                    @yield('breadcrumb', 'Command Center')
                </span>
            </div>

            <div style="display: flex; align-items: center; gap: 12px;">
                <button type="button" class="quick-action-btn secondary" onclick="const icon=this.querySelector('i'); if(icon) icon.style.transform='rotate(360deg)'; this.style.opacity='0.7'; setTimeout(()=>window.location.reload(), 300);" title="Refresh Page Data" style="font-size: 12px; padding: 6px 12px; border-radius: 999px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    <i data-lucide="refresh-cw" style="width: 13px; height: 13px; color: #4f46e5; transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);"></i>
                    <span>Refresh</span>
                </button>
                <div style="height: 18px; width: 1px; background: var(--border-color);"></div>
                <div style="font-size: 12px; color: #64748b; display: flex; align-items: center; gap: 6px; font-weight: 600;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; box-shadow: 0 0 6px rgba(16, 185, 129, 0.6);"></span>
                    <span>100% MySQL Pure Engine</span>
                </div>
                <div style="height: 18px; width: 1px; background: var(--border-color);"></div>
                <a href="{{ route('monitoring.index') }}" class="quick-action-btn secondary" style="font-size: 12px; padding: 6px 14px; border-radius: 999px;">
                    <i data-lucide="radio" style="width: 14px; height: 14px; color: #059669;"></i>
                    <span>Live Radar</span>
                </a>
            </div>
        </header>

        <div class="page-content">
            @if(session('success'))
                <div class="alert-success">
                    <i data-lucide="check-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-error">
                    <i data-lucide="alert-triangle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="alert-error" style="flex-direction: column; align-items: flex-start;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                        <i data-lucide="alert-triangle" style="width: 18px; height: 18px;"></i>
                        <strong>Please correct the following errors:</strong>
                    </div>
                    <ul style="margin-left: 26px; font-size: 12.5px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
    @yield('scripts')
</body>
</html>
