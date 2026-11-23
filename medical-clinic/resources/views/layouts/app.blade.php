<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Medical Clinic HMS')</title>
    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --primary-light: #e0f2fe;
            --bg: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --success: #16a34a;
            --success-light: #dcfce7;
            --warning: #d97706;
            --warning-light: #fef3c7;
            --danger: #dc2626;
            --danger-light: #fee2e2;
            --info: #2563eb;
            --info-light: #dbeafe;
            --purple: #7c3aed;
            --purple-light: #ede9fe;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        body { background-color: var(--bg); color: var(--text-main); display: flex; height: 100vh; overflow: hidden; }

        /* Sidebar Navigation */
        .sidebar { width: 260px; background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; flex-shrink: 0; }
        .clinic-brand { padding: 22px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 12px; }
        .brand-icon { width: 38px; height: 38px; background: var(--primary); color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 20px; }
        .brand-text h2 { font-size: 16px; font-weight: 700; color: var(--text-main); }
        .brand-text p { font-size: 12px; color: var(--text-muted); }

        .nav-menu { padding: 16px 12px; flex: 1; overflow-y: auto; }
        .nav-section-title { font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-muted); padding: 8px 12px; letter-spacing: 0.05em; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 10px 14px; color: var(--text-muted); text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 500; margin-bottom: 3px; transition: all 0.15s; }
        .nav-item:hover { background: #f1f5f9; color: var(--text-main); }
        .nav-item.active { background: var(--primary); color: #ffffff; font-weight: 600; }
        .nav-badge-admin { margin-left: auto; font-size: 10px; background: #e2e8f0; color: #475569; padding: 2px 6px; border-radius: 4px; font-weight: 600; }

        /* Main Workspace */
        .main-container { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

        /* Top Bar */
        .topbar { background: var(--surface); border-bottom: 1px solid var(--border); padding: 14px 28px; display: flex; justify-content: space-between; align-items: center; }
        .topbar-left { display: flex; align-items: center; gap: 16px; }
        .topbar-title { font-size: 20px; font-weight: 700; color: var(--text-main); }
        .live-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: var(--success-light); color: var(--success); font-size: 12px; font-weight: 600; border-radius: 999px; }
        .live-dot { width: 8px; height: 8px; background: var(--success); border-radius: 50%; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.3; } 100% { opacity: 1; } }

        .topbar-actions { display: flex; align-items: center; gap: 12px; }
        .btn { padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: background 0.15s; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary { background: #f1f5f9; color: var(--text-main); border: 1px solid var(--border); }
        .btn-secondary:hover { background: #e2e8f0; }

        /* Content Area */
        .content-area { flex: 1; overflow-y: auto; padding: 24px 28px; }

        /* 4 Top Metric / KPI Bars */
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 24px; }
        .kpi-card { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; padding: 18px 22px; position: relative; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
        .kpi-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; }
        .kpi-card.border-primary::before { background: var(--primary); }
        .kpi-card.border-success::before { background: var(--success); }
        .kpi-card.border-danger::before { background: var(--danger); }
        .kpi-card.border-purple::before { background: var(--purple); }

        .kpi-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .kpi-card-title { font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.04em; }
        .kpi-card-icon { font-size: 18px; }
        .kpi-card-value { font-size: 28px; font-weight: 800; line-height: 1.1; margin-bottom: 8px; }
        .kpi-card-sub { font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; padding: 3px 8px; border-radius: 4px; }

        .kpi-sub-emergency { background: var(--danger-light); color: var(--danger); }
        .kpi-sub-active { background: var(--info-light); color: var(--info); }
        .kpi-sub-audit { background: #f1f5f9; color: #475569; }
        .kpi-sub-duration { background: var(--purple-light); color: var(--purple); }

        /* Progress Mini-bar */
        .kpi-progress-bar { width: 100%; height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden; margin-top: 10px; }
        .kpi-progress-fill { height: 100%; border-radius: 3px; transition: width 0.4s ease; }
        .fill-purple { background: var(--purple); }
        .fill-success { background: var(--success); }

        @yield('styles')
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="clinic-brand">
            <div class="brand-icon">MC</div>
            <div class="brand-text">
                <h2>Medical Clinic</h2>
                <p>Hospital System</p>
            </div>
        </div>

        <nav class="nav-menu">
            <div class="nav-section-title">Clinical Workspace</div>
            <a href="#" class="nav-item">
                <span>📊</span> Dashboard
            </a>
            <a href="{{ route('appointments.index') }}" class="nav-item {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                <span>🗓️</span> Appointments
            </a>
            <a href="#" class="nav-item">
                <span>👨‍⚕️</span> Doctors
            </a>
            <a href="#" class="nav-item">
                <span>🩺</span> Services
            </a>
            <a href="{{ route('operations.index') }}" class="nav-item {{ request()->routeIs('operations.*') ? 'active' : '' }}">
                <span>🏥</span> Operation Schedule
            </a>

            <div class="nav-section-title" style="margin-top: 18px;">Administration</div>
            <a href="#" class="nav-item">
                <span>👥</span> Users
                <span class="nav-badge-admin">Admin</span>
            </a>
            <a href="#" class="nav-item">
                <span>📈</span> Analytics
            </a>
            <a href="#" class="nav-item">
                <span>📑</span> Reports
            </a>
            <a href="#" class="nav-item">
                <span>🛡️</span> Audit Logs
                <span class="nav-badge-admin">Admin</span>
            </a>
        </nav>
    </aside>

    <!-- Main Workspace -->
    <main class="main-container">
        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>
