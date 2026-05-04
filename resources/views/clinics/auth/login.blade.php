<!DOCTYPE html>
<html lang="km" class="h-full">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0"/>
    <title>ចូលប្រើ — {{ currentClinic()?->name ?? 'MediFlow EMR' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"/>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hanuman:wght@300;400;700;900&family=Nunito:wght@400;500;600;700;800;900&display=swap"/>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --pri:   #4154f1;
            --pri-d: #3040d8;
            --pri-l: #eef0fd;
            --dark:  #1a1f36;
            --success: #2eca6a;
        }

        html, body { height: 100%; }

        body {
            font-family: 'Hanuman', 'Nunito', sans-serif;
            display: flex;
            min-height: 100vh;
            background: #f6f8fa;
        }

        /* ── Left panel (branding) ── */
        .auth-left {
            display: none;
            flex: 0 0 480px;
            background: linear-gradient(160deg, #0d1b3e 0%, #1a2f6a 55%, #0d1b3e 100%);
            padding: 48px 40px;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        @media (min-width: 1024px) { .auth-left { display: flex; } }

        /* decorative circles */
        .auth-left::before {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: rgba(65,84,241,.15);
            top: -100px; right: -120px;
            pointer-events: none;
        }
        .auth-left::after {
            content: '';
            position: absolute;
            width: 260px; height: 260px;
            border-radius: 50%;
            background: rgba(46,202,106,.1);
            bottom: -60px; left: -60px;
            pointer-events: none;
        }

        .brand-logo {
            width: 56px; height: 56px;
            border-radius: 14px;
            background: var(--pri);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            flex-shrink: 0;
            box-shadow: 0 8px 24px rgba(65,84,241,.5);
            margin-bottom: 16px;
        }
        .brand-name {
            font-size: 24px;
            font-weight: 900;
            color: #fff;
            line-height: 1.2;
            letter-spacing: -.4px;
        }
        .brand-tagline {
            font-size: 13px;
            color: rgba(255,255,255,.45);
            margin-top: 6px;
            line-height: 1.5;
        }

        /* feature list */
        .feat-list { list-style: none; display: flex; flex-direction: column; gap: 18px; }
        .feat-item { display: flex; align-items: flex-start; gap: 14px; }
        .feat-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
            background: rgba(255,255,255,.08);
            color: #fff;
        }
        .feat-title { font-size: 13px; font-weight: 700; color: #fff; line-height: 1.3; }
        .feat-desc  { font-size: 11.5px; color: rgba(255,255,255,.45); line-height: 1.4; margin-top: 2px; }

        .left-footer { font-size: 11px; color: rgba(255,255,255,.25); }

        /* ── Right panel (form) ── */
        .auth-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
        }

        .auth-form-wrap { width: 100%; max-width: 400px; }

        /* mobile brand (shown when left panel hidden) */
        .mobile-brand {
            text-align: center;
            margin-bottom: 28px;
        }
        @media (min-width: 1024px) { .mobile-brand { display: none; } }

        .mobile-logo {
            width: 52px; height: 52px;
            border-radius: 14px;
            background: var(--pri);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 24px;
            margin-bottom: 10px;
            box-shadow: 0 6px 18px rgba(65,84,241,.4);
        }
        .mobile-clinic { font-size: 18px; font-weight: 800; color: var(--dark); }
        .mobile-sub    { font-size: 12px; color: #6b7280; margin-top: 3px; }

        /* form card */
        .form-card {
            background: #fff;
            border-radius: 20px;
            padding: 36px 32px;
            box-shadow: 0 4px 6px rgba(17,24,39,.03), 0 20px 60px rgba(17,24,39,.08);
        }

        .form-title {
            font-size: 18px; font-weight: 900;
            color: var(--dark); margin-bottom: 4px;
        }
        .form-hint {
            font-size: 12.5px; color: #6b7280; margin-bottom: 28px;
        }

        /* field */
        .fld { margin-bottom: 16px; }
        .flbl {
            display: block;
            font-size: 11.5px; font-weight: 700;
            color: #444; margin-bottom: 5px;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            color: #c4cadd; font-size: 14px;
            pointer-events: none;
        }
        .form-input {
            width: 100%;
            border: 1.5px solid #e6eaf5;
            border-radius: 10px;
            padding: 11px 12px 11px 38px;
            font-size: 13.5px;
            font-family: inherit;
            color: #222;
            background: #f7f8ff;
            outline: none;
            transition: border-color .18s, box-shadow .18s, background .18s;
        }
        .form-input:focus {
            border-color: var(--pri);
            box-shadow: 0 0 0 3px rgba(65,84,241,.12);
            background: #fff;
        }
        .form-input.has-toggle { padding-right: 40px; }
        .pw-toggle {
            position: absolute; right: 10px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: #c4cadd; cursor: pointer;
            font-size: 15px; padding: 3px;
            transition: color .15s;
        }
        .pw-toggle:hover { color: #666; }

        /* error alert */
        .alert-err {
            display: flex; align-items: flex-start; gap: 9px;
            background: #fef2f2; border: 1px solid #fecaca;
            border-radius: 10px; padding: 11px 14px;
            font-size: 12.5px; color: #dc2626;
            margin-bottom: 18px; line-height: 1.4;
        }

        /* remember row */
        .opt-row {
            display: flex; align-items: center; justify-content: space-between;
            margin: 6px 0 20px;
        }
        .remember-lbl {
            display: flex; align-items: center; gap: 7px;
            font-size: 12px; color: #64748b; cursor: pointer;
        }
        .remember-lbl input { cursor: pointer; accent-color: var(--pri); }

        /* submit */
        .btn-sign-in {
            width: 100%;
            background: var(--pri);
            color: #fff; border: none;
            border-radius: 10px;
            padding: 13px;
            font-size: 14px; font-weight: 700;
            font-family: inherit; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: background .18s, transform .15s, box-shadow .18s;
            box-shadow: 0 4px 14px rgba(65,84,241,.35);
        }
        .btn-sign-in:hover  { background: var(--pri-d); transform: translateY(-1px); box-shadow: 0 6px 22px rgba(65,84,241,.45); }
        .btn-sign-in:active { transform: translateY(0); }
        .btn-sign-in:disabled { opacity: .7; cursor: not-allowed; transform: none; }

        .spinner {
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* footer */
        .form-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 11px; color: #cbd5e1;
        }
    </style>
</head>
<body>

{{-- Left branding panel --}}
<aside class="auth-left" aria-hidden="true">
    <div>
        <div class="brand-logo">
            @if(currentClinic()?->logo)
                <img src="{{ asset('storage/' . currentClinic()->logo) }}"
                     style="width:40px;height:40px;border-radius:8px;object-fit:cover" alt="">
            @else
                ⚕
            @endif
        </div>
        <div class="brand-name">{{ currentClinic()?->name_kh ?? currentClinic()?->name ?? 'MediFlow EMR' }}</div>
        <div class="brand-tagline">ប្រព័ន្ធព័ត៌មានគ្លីនិក<br>Clinic Health Information System</div>
    </div>

    <ul class="feat-list" role="list">
        <li class="feat-item">
            <div class="feat-icon"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="feat-title">Patient Management</div>
                <div class="feat-desc">OPD / IPD visits, admissions, triage and full patient records</div>
            </div>
        </li>
        <li class="feat-item">
            <div class="feat-icon"><i class="bi bi-capsule-pill"></i></div>
            <div>
                <div class="feat-title">Pharmacy & Inventory</div>
                <div class="feat-desc">Prescription dispensing, stock tracking and automatic reorder alerts</div>
            </div>
        </li>
        <li class="feat-item">
            <div class="feat-icon"><i class="bi bi-receipt-cutoff"></i></div>
            <div>
                <div class="feat-title">Billing & Invoicing</div>
                <div class="feat-desc">Automatic invoice generation, payments and financial reports</div>
            </div>
        </li>
        <li class="feat-item">
            <div class="feat-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div>
                <div class="feat-title">Analytics & Reports</div>
                <div class="feat-desc">Daily summaries, revenue trends and doctor performance dashboards</div>
            </div>
        </li>
    </ul>

    <div class="left-footer">&copy; {{ date('Y') }} MediFlow EMR</div>
</aside>

{{-- Right form panel --}}
<main class="auth-right">
    <div class="auth-form-wrap">

        {{-- Mobile-only brand --}}
        <div class="mobile-brand">
            <div class="mobile-logo">
                @if(currentClinic()?->logo)
                    <img src="{{ asset('storage/' . currentClinic()->logo) }}"
                         style="width:36px;height:36px;border-radius:8px;object-fit:cover" alt="">
                @else
                    ⚕
                @endif
            </div>
            <div class="mobile-clinic">{{ currentClinic()?->name_kh ?? currentClinic()?->name ?? 'MediFlow EMR' }}</div>
            <div class="mobile-sub">ប្រព័ន្ធព័ត៌មានសុខភាព / Health Information System</div>
        </div>

        <div class="form-card">
            <h1 class="form-title">ចូលប្រើប្រាស់ / Sign In</h1>
            <p class="form-hint">Enter your credentials to access the system</p>

            @if(session('error'))
            <div class="alert-err" role="alert">
                <i class="bi bi-exclamation-triangle-fill" style="margin-top:1px;flex-shrink:0"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="alert-err" role="alert">
                <i class="bi bi-exclamation-triangle-fill" style="margin-top:1px;flex-shrink:0"></i>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            <form method="POST" action="{{ url('/login') }}" id="loginForm" novalidate>
                @csrf

                {{-- Email --}}
                <div class="fld">
                    <label class="flbl" for="email">
                        អ៊ីមែល / Email <span style="color:#e74c3c">*</span>
                    </label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" id="email" name="email"
                               class="form-input"
                               placeholder="your@email.com"
                               value="{{ old('email') }}"
                               required autofocus autocomplete="email"/>
                    </div>
                </div>

                {{-- Password --}}
                <div class="fld">
                    <label class="flbl" for="password">
                        ពាក្យសម្ងាត់ / Password <span style="color:#e74c3c">*</span>
                    </label>
                    <div class="input-wrap">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" id="password" name="password"
                               class="form-input has-toggle"
                               placeholder="••••••••"
                               required autocomplete="current-password"/>
                        <button type="button" class="pw-toggle" id="pwToggle"
                                onclick="togglePw()" title="Show/hide password"
                                aria-label="Toggle password visibility">
                            <i class="bi bi-eye" id="pwIcon"></i>
                        </button>
                    </div>
                </div>

                {{-- Options row --}}
                <div class="opt-row">
                    <label class="remember-lbl">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        រំលឹកខ្ញុំ / Remember me
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-sign-in" id="submitBtn">
                    <span class="spinner" id="spinner" aria-hidden="true"></span>
                    <i class="bi bi-box-arrow-in-right" id="submitIcon"></i>
                    <span id="submitLabel">ចូល / Sign In</span>
                </button>
            </form>
        </div>

        <div class="form-footer">
            &copy; {{ date('Y') }} MediFlow EMR &mdash; {{ currentClinic()?->name ?? '' }}
        </div>
    </div>
</main>

<script>
function togglePw() {
    var inp = document.getElementById('password');
    var ico = document.getElementById('pwIcon');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ico.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

document.getElementById('loginForm').addEventListener('submit', function() {
    var btn     = document.getElementById('submitBtn');
    var spinner = document.getElementById('spinner');
    var icon    = document.getElementById('submitIcon');
    var label   = document.getElementById('submitLabel');
    btn.disabled = true;
    spinner.style.display = 'block';
    icon.style.display    = 'none';
    label.textContent     = 'Signing in…';
});
</script>

</body>
</html>
