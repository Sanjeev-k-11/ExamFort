<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ExamFort - Institutional Command Center')</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        :root {
            --bg-primary: #0a0f1d;
            --bg-secondary: #0f172a;
            --bg-card: rgba(15, 23, 42, 0.75);
            --bg-card-hover: rgba(30, 41, 59, 0.85);
            --border-color: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(99, 102, 241, 0.4);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent-primary: #6366f1;
            --accent-gradient: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%);
            --accent-emerald: #10b981;
            --accent-amber: #f59e0b;
            --accent-rose: #f43f5e;
            --accent-cyan: #06b6d4;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 18px;
            --sidebar-width: 270px;
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
                radial-gradient(circle at 15% 15%, rgba(99, 102, 241, 0.12) 0%, transparent 40%),
                radial-gradient(circle at 85% 75%, rgba(16, 185, 129, 0.08) 0%, transparent 40%);
            background-attachment: fixed;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: rgba(10, 15, 29, 0.95);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 50;
        }

        .sidebar-header {
            padding: 24px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-logo-icon {
            width: 38px;
            height: 38px;
            border-radius: var(--radius-sm);
            background: var(--accent-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
        }

        .sidebar-logo-title {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #fff 30%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-nav {
            padding: 20px 14px;
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .nav-category {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            padding: 12px 12px 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.2s ease;
            position: relative;
        }

        .nav-item:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.04);
        }

        .nav-item.active {
            color: #fff;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            font-weight: 600;
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
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--border-color);
            background: rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--accent-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            color: white;
        }

        .user-meta {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            max-width: 130px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-role-badge {
            font-size: 10px;
            color: #818cf8;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .logout-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 6px;
            border-radius: var(--radius-sm);
            transition: color 0.2s;
        }

        .logout-btn:hover {
            color: #f87171;
            background: rgba(239, 68, 68, 0.1);
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
            backdrop-filter: blur(20px);
            background: rgba(10, 15, 29, 0.8);
            position: sticky;
            top: 0;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
        }

        .page-content {
            padding: 32px;
            flex: 1;
        }

        /* Glass Cards */
        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 24px;
            transition: all 0.25s ease;
        }

        .glass-card:hover {
            border-color: var(--border-hover);
        }

        .card-header-flex {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .card-title {
            font-family: 'Outfit', sans-serif;
            font-size: 17px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Metrics Bar */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .metric-card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent-gradient);
        }

        .metric-card:hover {
            transform: translateY(-2px);
            border-color: var(--border-hover);
        }

        .metric-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-color, #818cf8);
        }

        .metric-info {
            display: flex;
            flex-direction: column;
        }

        .metric-value {
            font-family: 'Outfit', sans-serif;
            font-size: 26px;
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
        }

        .metric-label {
            font-size: 12px;
            color: var(--text-secondary);
            font-weight: 500;
            margin-top: 4px;
        }

        /* Table */
        .custom-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13.5px;
        }

        .custom-table th {
            background: rgba(255, 255, 255, 0.02);
            color: var(--text-muted);
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .custom-table td {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-secondary);
            vertical-align: middle;
        }

        .custom-table tr:hover td {
            background: rgba(255, 255, 255, 0.02);
            color: #fff;
        }

        /* Status Pills */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .status-pill.active {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .status-pill.upcoming {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .status-pill.completed {
            background: rgba(148, 163, 184, 0.15);
            color: #cbd5e1;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        .status-pill.danger {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            color: #fff;
            font-size: 13.5px;
            font-family: inherit;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
            background: rgba(0, 0, 0, 0.5);
        }

        input[type="date"], input[type="time"] {
            color-scheme: dark;
            cursor: pointer;
        }

        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator {
            filter: invert(0.9) hue-rotate(180deg);
            cursor: pointer;
            opacity: 0.85;
            transition: transform 0.2s, opacity 0.2s;
        }

        input[type="date"]::-webkit-calendar-picker-indicator:hover,
        input[type="time"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
            transform: scale(1.15);
        }

        /* Standard & Modern Button System */
        .btn, a.btn, button.btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none !important;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
            font-family: inherit;
            line-height: 1.4;
            white-space: nowrap;
        }

        .btn-primary, a.btn-primary, button.btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35) !important;
            text-decoration: none !important;
        }

        .btn-primary:hover, a.btn-primary:hover, button.btn-primary:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%) !important;
            transform: translateY(-1.5px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5) !important;
            color: #ffffff !important;
            text-decoration: none !important;
        }

        .btn-secondary, a.btn-secondary, button.btn-secondary {
            background: rgba(255, 255, 255, 0.05) !important;
            color: #e2e8f0 !important;
            border: 1px solid var(--border-color) !important;
            box-shadow: none !important;
            text-decoration: none !important;
        }

        .btn-secondary:hover, a.btn-secondary:hover, button.btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            border-color: var(--border-hover) !important;
            color: #ffffff !important;
            transform: translateY(-1px);
            text-decoration: none !important;
        }

        .btn-emerald, a.btn-emerald, button.btn-emerald {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35) !important;
            text-decoration: none !important;
        }

        .btn-emerald:hover, a.btn-emerald:hover, button.btn-emerald:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
            transform: translateY(-1.5px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5) !important;
            color: #ffffff !important;
            text-decoration: none !important;
        }

        .btn-danger, a.btn-danger, button.btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35) !important;
            text-decoration: none !important;
        }

        .btn-danger:hover, a.btn-danger:hover, button.btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
            transform: translateY(-1.5px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5) !important;
            color: #ffffff !important;
            text-decoration: none !important;
        }

        .btn:active, .btn-primary:active, .btn-secondary:active, .btn-emerald:active {
            transform: translateY(0) !important;
        }

        .tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 18px;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text-secondary);
            text-decoration: none !important;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .tab-btn:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.03);
            text-decoration: none !important;
        }

        .tab-btn.active {
            color: #818cf8 !important;
            font-weight: 700;
            border-bottom: 2px solid #6366f1;
            background: rgba(99, 102, 241, 0.08);
            text-decoration: none !important;
        }

        /* Quick Action Button */
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
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
        }

        .quick-action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.45);
        }

        .quick-action-btn.secondary {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            box-shadow: none;
            color: #fff;
        }

        .quick-action-btn.secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--border-hover);
        }

        .quick-action-btn.danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3);
        }

        /* Modal Overlay */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal-box {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 650px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 28px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
            padding: 12px 18px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            padding: 12px 18px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-responsive {
            overflow-x: auto;
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
                <div style="font-size: 10px; color: #818cf8; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">
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
                <i data-lucide="radio" style="width: 18px; height: 18px; color: #10b981;"></i>
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
                    <i data-lucide="crown" style="width: 18px; height: 18px; color: #fbbf24;"></i>
                    <span>Principals &amp; Deans</span>
                </a>
                <a href="{{ route('inquiries.index') }}" class="nav-item {{ request()->routeIs('inquiries.*') ? 'active' : '' }}">
                    <i data-lucide="mail-question" style="width: 18px; height: 18px; color: #38bdf8;"></i>
                    <span>Consultation Leads</span>
                    @php
                        $unreadInq = \App\Models\ContactInquiry::where('status', 'PENDING')->count();
                    @endphp
                    @if($unreadInq > 0)
                        <span class="nav-badge" style="background: rgba(239, 68, 68, 0.2); color: #f87171;">{{ $unreadInq }} New</span>
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
            <a href="{{ route('placement-exams.index') }}" class="nav-item {{ request()->routeIs('placement-exams.*') ? 'active' : '' }}" style="background: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.25);">
                <i data-lucide="briefcase" style="width: 18px; height: 18px; color: #818cf8;"></i>
                <span style="font-weight: 700; color: #fff;">Placement Drives</span>
                @php
                    $drivesCount = \App\Models\PlacementExam::where('status', 'ACTIVE')->orWhere('status', 'SCHEDULED')->count();
                @endphp
                @if($drivesCount > 0)
                    <span class="nav-badge" style="background: linear-gradient(135deg, #10b981, #059669); color: white;">{{ $drivesCount }} Active</span>
                @endif
            </a>

            <div class="nav-category">Assessments & Evaluation</div>
            <a href="{{ route('ai.generator') }}" class="nav-item {{ request()->routeIs('ai.*') ? 'active' : '' }}" style="color: #c7d2fe; background: rgba(99, 102, 241, 0.1); border: 1px dashed rgba(99, 102, 241, 0.35);">
                <i data-lucide="sparkles" style="width: 18px; height: 18px; color: #38bdf8;"></i>
                <span style="font-weight: 700;">AI Paper Generator</span>
                <span class="nav-badge" style="background: linear-gradient(135deg, #6366f1, #06b6d4); color: white;">AI ✨</span>
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
                <i data-lucide="shield-alert" style="width: 18px; height: 18px; color: #ef4444;"></i>
                <span>Threat Incidents</span>
            </a>

            <div class="nav-category">Public Website</div>
            <a href="{{ route('public.home') }}" target="_blank" class="nav-item">
                <i data-lucide="globe" style="width: 18px; height: 18px; color: #38bdf8;"></i>
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
                    <i data-lucide="log-out" style="width: 18px; height: 18px;"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Workspace -->
    <div class="workspace">
        <header class="topbar">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 14px; font-weight: 600; color: var(--text-secondary);">
                    @yield('breadcrumb', 'Command Center')
                </span>
            </div>

            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 6px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                    <span>100% MySQL Pure Engine</span>
                </div>
                <div style="height: 18px; width: 1px; background: var(--border-color);"></div>
                <a href="{{ route('monitoring.index') }}" class="quick-action-btn secondary" style="font-size: 12px; padding: 6px 12px;">
                    <i data-lucide="radio" style="width: 14px; height: 14px; color: #10b981;"></i>
                    <span>Live Radar</span>
                </a>
            </div>
        </header>

        <div class="page-content">
            @if(session('success'))
                <div class="alert-success">
                    <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-error">
                    <i data-lucide="alert-triangle" style="width: 18px; height: 18px;"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="alert-error" style="flex-direction: column; align-items: flex-start;">
                    <strong style="margin-bottom: 6px;">Please correct the following errors:</strong>
                    <ul style="margin-left: 20px; font-size: 12.5px;">
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
