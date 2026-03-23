<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0"/>
    <title>Login — {{ currentClinic()?->name ?? 'MediFlow EMR' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hanuman:wght@300;400;700&family=Nunito:wght@400;600;700;800&display=swap"/>
    <style>
        :root { --primary:#4154f1; --primary-d:#3040d8; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body {
            font-family:'Hanuman','Nunito',sans-serif;
            background:linear-gradient(135deg,#0d1b3e 0%,#1a2f6a 50%,#0d1b3e 100%);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:20px;
        }
        .login-wrap {
            width:100%;
            max-width:420px;
        }
        .login-brand {
            text-align:center;
            margin-bottom:28px;
        }
        .login-logo {
            width:60px; height:60px;
            border-radius:16px;
            background:var(--primary);
            display:inline-flex;
            align-items:center;
            justify-content:center;
            font-size:28px;
            margin-bottom:12px;
            box-shadow:0 8px 24px rgba(65,84,241,.5);
        }
        .login-clinic-name {
            font-size:20px;
            font-weight:800;
            color:#fff;
            margin-bottom:3px;
            letter-spacing:-.3px;
        }
        .login-sub {
            font-size:12px;
            color:rgba(255,255,255,.45);
        }
        .login-card {
            background:#fff;
            border-radius:18px;
            padding:32px 28px;
            box-shadow:0 24px 64px rgba(0,0,0,.4);
        }
        .login-title {
            font-size:17px;
            font-weight:800;
            color:#012970;
            margin-bottom:6px;
        }
        .login-hint {
            font-size:12px;
            color:#aaa;
            margin-bottom:24px;
        }
        .fld { margin-bottom:14px; }
        .flbl {
            display:block;
            font-size:12px;
            font-weight:700;
            color:#444;
            margin-bottom:5px;
        }
        .form-control {
            border:1.5px solid #e6eaf5;
            border-radius:9px;
            padding:10px 12px 10px 38px;
            font-size:13.5px;
            font-family:inherit;
            width:100%;
            color:#333;
            background:#f7f8ff;
            transition:border-color .18s, box-shadow .18s, background .18s;
            outline:none;
        }
        .form-control:focus {
            border-color:var(--primary);
            box-shadow:0 0 0 3px rgba(65,84,241,.12);
            background:#fff;
        }
        .input-wrap {
            position:relative;
        }
        .input-wrap i {
            position:absolute;
            left:12px;
            top:50%;
            transform:translateY(-50%);
            color:#bbb;
            font-size:14px;
            pointer-events:none;
        }
        .pw-toggle {
            position:absolute;
            right:10px;
            top:50%;
            transform:translateY(-50%);
            background:none;
            border:none;
            color:#bbb;
            cursor:pointer;
            font-size:15px;
            padding:2px;
        }
        .pw-toggle:hover { color:#666; }
        .btn-login {
            width:100%;
            background:var(--primary);
            color:#fff;
            border:none;
            border-radius:9px;
            padding:12px;
            font-size:14px;
            font-weight:700;
            font-family:inherit;
            cursor:pointer;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            transition:background .18s, transform .15s, box-shadow .18s;
            box-shadow:0 4px 14px rgba(65,84,241,.35);
            margin-top:6px;
        }
        .btn-login:hover {
            background:var(--primary-d);
            transform:translateY(-1px);
            box-shadow:0 6px 20px rgba(65,84,241,.45);
        }
        .btn-login:active { transform:translateY(0); }
        .remember-row {
            display:flex;
            align-items:center;
            gap:8px;
            margin:14px 0;
        }
        .remember-row input { cursor:pointer; }
        .remember-row label { font-size:12px; color:#666; cursor:pointer; }
        .alert-err {
            background:#fde8e8;
            border:1px solid #f5c0c0;
            border-radius:9px;
            padding:10px 14px;
            font-size:12.5px;
            color:#c0392b;
            display:flex;
            align-items:center;
            gap:8px;
            margin-bottom:16px;
        }
        .login-footer {
            text-align:center;
            margin-top:20px;
            font-size:11px;
            color:rgba(255,255,255,.3);
        }
    </style>
</head>
<body>

<div class="login-wrap">

    {{-- Brand --}}
    <div class="login-brand">
        <div class="login-logo">
            @if(currentClinic()?->logo)
                <img src="{{ asset('storage/' . currentClinic()->logo) }}"
                     style="width:44px;height:44px;border-radius:10px;object-fit:cover">
            @else
                ⚕
            @endif
        </div>
        <div class="login-clinic-name">
            {{ currentClinic()?->name_kh ?? currentClinic()?->name ?? 'MediFlow EMR' }}
        </div>
        <div class="login-sub">ប្រព័ន្ធព័ត៌មានសុខភាព / Health Information System</div>
    </div>

    {{-- Card --}}
    <div class="login-card">
        <div class="login-title">ចូលប្រើប្រាស់ / Sign In</div>
        <div class="login-hint">Enter your email and password to continue</div>

        @if(session('error'))
        <div class="alert-err">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ session('error') }}
        </div>
        @endif

        <form method="POST" action="{{ url('/login') }}">
            @csrf

            <div class="fld">
                <label class="flbl" for="email">
                    <i class="bi bi-envelope-fill" style="color:#4154f1;font-size:11px"></i>
                    អ៊ីមែល / Email
                </label>
                <div class="input-wrap">
                    <i class="bi bi-envelope"></i>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="your@email.com"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                    />
                </div>
            </div>

            <div class="fld">
                <label class="flbl" for="password">
                    <i class="bi bi-lock-fill" style="color:#4154f1;font-size:11px"></i>
                    ពាក្យសម្ងាត់ / Password
                </label>
                <div class="input-wrap">
                    <i class="bi bi-lock"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    />
                    <button type="button" class="pw-toggle" onclick="togglePw()" id="pwToggle" title="Show/hide">
                        <i class="bi bi-eye" id="pwIcon"></i>
                    </button>
                </div>
            </div>

            <div class="remember-row">
                <input type="checkbox" name="remember" id="remember"
                       {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">រំលឹកខ្ញុំ / Remember me</label>
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right"></i>
                ចូល / Sign In
            </button>
        </form>
    </div>

    <div class="login-footer">
        &copy; {{ date('Y') }} MediFlow EMR &mdash; {{ currentClinic()?->name ?? '' }}
    </div>

</div>

<script>
function togglePw() {
    var inp = document.getElementById('password');
    var ico = document.getElementById('pwIcon');
    if (inp.type === 'password') {
        inp.type = 'text';
        ico.className = 'bi bi-eye-slash';
    } else {
        inp.type = 'password';
        ico.className = 'bi bi-eye';
    }
}
</script>

</body>
</html>
