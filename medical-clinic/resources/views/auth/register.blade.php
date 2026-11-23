<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Medical Clinic</title>
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
            --danger: #dc2626;
            --danger-light: #fee2e2;
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

        /* Sidebar Footer / Login Area */
        .sidebar-footer { padding: 14px 16px; border-top: 1px solid var(--border); background: #fbfdff; }
        .sidebar-login-btn { width: 100%; justify-content: center; padding: 11px 16px; }
        .sidebar-hint { text-align: center; font-size: 11px; color: var(--text-muted); margin-top: 8px; }

        /* Main Register Workspace */
        .login-container { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: var(--surface); }

        .login-topbar { background: var(--surface); border-bottom: 1px solid var(--border); padding: 18px 32px; display: flex; justify-content: space-between; align-items: center; gap: 16px; }
        .login-topbar-title { font-size: 20px; font-weight: 700; color: var(--text-main); }
        .login-topbar-sub { font-size: 13px; color: var(--text-muted); margin-top: 2px; }
        .btn { padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: background 0.15s; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary { background: #f1f5f9; color: var(--text-main); border: 1px solid var(--border); }
        .btn-secondary:hover { background: #e2e8f0; }

        /* Full-height register panel on the right */
        .login-area { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px 24px; overflow-y: auto; background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); }
        .login-card { width: 100%; max-width: 460px; background: var(--surface); border: 1px solid var(--border); border-radius: 10px; padding: 32px 34px; box-shadow: 0 10px 25px -12px rgba(15, 23, 42, 0.18); }

        .login-heading { font-size: 24px; font-weight: 800; text-align: center; letter-spacing: -0.01em; }
        .login-subheading { font-size: 13px; color: var(--text-muted); text-align: center; margin-top: 6px; }

        .alert { padding: 11px 14px; border-radius: 6px; font-size: 13px; font-weight: 500; margin-top: 20px; }
        .alert-danger { background: var(--danger-light); border: 1px solid #fecaca; color: var(--danger); }

        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; }
        .form-group input { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 14px; color: var(--text-main); background: #ffffff; outline: none; transition: border-color 0.15s, box-shadow 0.15s; }
        .form-group input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12); }
        .form-row { display: flex; gap: 12px; }
        .form-row .form-group { flex: 1; }
        .field-error { display: block; font-size: 12px; color: var(--danger); margin-top: 5px; }

        .login-form { margin-top: 22px; }
        .login-submit { width: 100%; justify-content: center; padding: 11px 16px; font-size: 15px; margin-top: 6px; }

        .register-text { text-align: center; font-size: 12px; color: var(--text-muted); margin-top: 18px; }
        .register-text a { color: var(--primary-dark); font-weight: 600; text-decoration: none; }
        .register-text a:hover { text-decoration: underline; }

        .login-footnote { text-align: center; font-size: 11px; color: #94a3b8; margin-top: 22px; }

        @media (max-width: 860px) {
            body { overflow: auto; height: auto; min-height: 100vh; }
            .sidebar { display: none; }
            .login-container { min-height: 100vh; }
            .login-topbar { padding: 16px 20px; }
            .login-area { padding: 28px 18px; }
            .login-card { padding: 28px 22px; }
        }

        @media (max-width: 480px) {
            .form-row { flex-direction: column; gap: 0; }
        }
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
            <a href="{{ route('appointments.index') }}" class="nav-item">
                <span>🗓️</span> Appointments
            </a>
            <a href="#" class="nav-item">
                <span>👨‍⚕️</span> Doctors
            </a>
            <a href="#" class="nav-item">
                <span>🩺</span> Services
            </a>
            <a href="#" class="nav-item">
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

        <!-- Log In at the bottom of the side menu -->
        <div class="sidebar-footer">
            <a href="{{ route('login') }}" class="btn btn-primary sidebar-login-btn">
                Log In
            </a>
            <p class="sidebar-hint">Already registered? Sign in here</p>
        </div>
    </aside>

    <!-- Full-height register workspace -->
    <main class="login-container">
        <header class="login-topbar">
            <div>
                <h1 class="login-topbar-title">Patient Registration</h1>
                <p class="login-topbar-sub">Create your Medical Clinic account</p>
            </div>
            <button type="button" class="btn btn-secondary" onclick="history.back()">
                <span>←</span> Back
            </button>
        </header>

        <div class="login-area">
            <div class="login-card">
                <h2 class="login-heading">Create your account</h2>
                <p class="login-subheading">Register as a patient to book and manage appointments.</p>

                @if($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('register.store') }}" method="POST" class="login-form">
                    @csrf

                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input
                                type="text"
                                id="first_name"
                                name="first_name"
                                value="{{ old('first_name') }}"
                                placeholder="Jane"
                                autocomplete="given-name"
                                required
                                autofocus
                            >
                            @error('first_name')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input
                                type="text"
                                id="last_name"
                                name="last_name"
                                value="{{ old('last_name') }}"
                                placeholder="Doe"
                                autocomplete="family-name"
                                required
                            >
                            @error('last_name')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="you@medicalclinic.com"
                            autocomplete="email"
                            required
                        >
                        @error('email')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="At least 8 characters"
                            autocomplete="new-password"
                            required
                        >
                        @error('password')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Re-enter your password"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary login-submit">
                        Create Account
                    </button>

                    <p class="register-text">
                        Already have an account? <a href="{{ route('login') }}">Log In</a>
                    </p>
                </form>

                <p class="login-footnote">Medical Clinic Hospital System &bull; Patient portal</p>
            </div>
        </div>
    </main>

</body>
</html>
