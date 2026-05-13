<?php
session_start();
if (isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AOT Homes — Sign In</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <script src="assets/js/main.js" defer></script>
</head>

<body class="auth-body">

  <div class="auth-bg">
    <div class="auth-bg__blob auth-bg__blob--1"></div>
    <div class="auth-bg__blob auth-bg__blob--2"></div>
    <div class="auth-bg__blob auth-bg__blob--3"></div>
  </div>

  <div class="auth-wrap">

    <!-- ── LEFT BRAND PANEL ── -->
    <div class="auth-brand">
      <div class="auth-brand__inner">
        <div class="auth-brand__logo">
          <div class="auth-brand__icon">🏠</div>
          <div>
            <div class="auth-brand__name">AOT Homes</div>
            <div class="auth-brand__tagline">Energy Monitor — CS251</div>
          </div>
        </div>
        <h2 class="auth-brand__headline">Smart Energy,<br>Smarter Living.</h2>
        <p class="auth-brand__desc">Monitor electricity, water and gas in real-time. Get alerts before small issues become big bills. Track 42 smart functions across your home.</p>
        <div class="auth-stats">
          <div class="auth-stat">
            <div class="auth-stat__val" style="color:var(--elec)">18.4</div>
            <div class="auth-stat__lbl">kWh Today</div>
          </div>
          <div class="auth-stat">
            <div class="auth-stat__val" style="color:var(--water)">142</div>
            <div class="auth-stat__lbl">Litres Used</div>
          </div>
          <div class="auth-stat">
            <div class="auth-stat__val" style="color:var(--gas)">3.2</div>
            <div class="auth-stat__lbl">m³ Gas</div>
          </div>
        </div>
        <div class="auth-badges">
          <span class="auth-badge">⚡ Real-time Monitoring</span>
          <span class="auth-badge">🔔 Smart Alerts</span>
          <span class="auth-badge">📊 Usage Reports</span>
          <span class="auth-badge">🤖 Automation Engine</span>
          <span class="auth-badge">🌿 Eco-Credits</span>
          <span class="auth-badge">☀️ Solar Tracking</span>
        </div>
      </div>
    </div>

    <!-- ── RIGHT FORM PANEL ── -->
    <div class="auth-panel">

      <!-- Mobile logo -->
      <div class="auth-logo-mobile">
        <span style="font-size:1.8rem">🏠</span>
        <div>
          <div style="font-family:'Rajdhani',sans-serif;font-size:1.2rem;font-weight:700">AOT Homes</div>
          <div style="font-size:.7rem;color:var(--text-3)">Energy Monitor</div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="auth-tabs">
        <button type="button" class="auth-tab active" id="tab-login" onclick="switchTab('login')">Sign In</button>
        <button class="auth-tab" id="tab-register" onclick="switchTab('register')">Create Account</button>
      </div>

      <!-- ══ LOGIN FORM ══ -->
      <form class="auth-form" id="form-login" onsubmit="handleLogin(event)" novalidate>
        <div>
          <h1 class="auth-form__title">Welcome back</h1>
          <p class="auth-form__sub">Sign in to your energy dashboard</p>
        </div>

        <div class="alert alert--info" style="margin-bottom:0;padding:10px 14px">
          <span class="alert__icon">💡</span>
          <div>
            <div class="alert__title">Demo Account</div>
            <div class="alert__msg">Register first, then sign in with your credentials.</div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="login-email">Email Address</label>
          <input class="form-input" type="email" id="login-email" placeholder="you@example.com" autocomplete="email" required />
          <span class="form-error" id="err-login-email"></span>
        </div>

        <div class="form-group">
          <label class="form-label" for="login-password">
            Password
            <a href="#" class="auth-link" onclick="showForgot(event)" style="font-size:.72rem">Forgot?</a>
          </label>
          <div class="input-wrap">
            <input class="form-input" type="password" id="login-password" placeholder="••••••••" autocomplete="current-password" required />
            <button type="button" class="input-eye" onclick="togglePw('login-password',this)">👁</button>
          </div>
          <span class="form-error" id="err-login-password"></span>
        </div>

        <label class="auth-check">
          <input type="checkbox" id="remember-me" />
          <span class="auth-check__box"></span>
          Remember me for 30 days
        </label>

        <button type="submit" class="btn btn--primary btn--full btn--lg" id="btn-login">
          <span class="btn-txt">Sign In</span>
          <span class="btn-spin" style="display:none">⏳ Signing in…</span>
        </button>

        <div class="auth-divider"><span>or</span></div>
        <div class="auth-divider"><span>or social</span></div>
        <div class="auth-socials">
          <button type="button" class="btn--social" onclick="showToast('Google OAuth — coming soon','info')">
            <svg width="16" height="16" viewBox="0 0 24 24">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
            </svg>
            Google
          </button>
          <button type="button" class="btn--social" onclick="showToast('Microsoft OAuth — coming soon','info')">
            <svg width="16" height="16" viewBox="0 0 24 24">
              <path fill="#F25022" d="M1 1h10v10H1z" />
              <path fill="#7FBA00" d="M13 1h10v10H13z" />
              <path fill="#00A4EF" d="M1 13h10v10H1z" />
              <path fill="#FFB900" d="M13 13h10v10H13z" />
            </svg>
            Microsoft
          </button>
        </div>
        <p class="auth-switch">No account? <a href="#" class="auth-link" onclick="switchTab('register');return false">Create one free</a></p>
      </form>

      <!-- ══ REGISTER FORM ══ -->
      <form class="auth-form" id="form-register" style="display:none" onsubmit="handleRegister(event)" novalidate>
        <div>
          <h1 class="auth-form__title">Create account</h1>
          <p class="auth-form__sub">Start monitoring your home's energy today</p>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="reg-first">First Name</label>
            <input class="form-input" type="text" id="reg-first" placeholder="" required />
            <span class="form-error" id="err-reg-first"></span>
          </div>
          <div class="form-group">
            <label class="form-label" for="reg-last">Last Name</label>
            <input class="form-input" type="text" id="reg-last" placeholder="" required />
            <span class="form-error" id="err-reg-last"></span>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-email">Email Address</label>
          <input class="form-input" type="email" id="reg-email" placeholder="" required />
          <span class="form-error" id="err-reg-email"></span>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-home">Home Name</label>
          <input class="form-input" type="text" id="reg-home" placeholder="e.g. My Smart Home" required />
          <span class="form-error" id="err-reg-home"></span>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-password">Password</label>
          <div class="input-wrap">
            <input class="form-input" type="password" id="reg-password" placeholder="Min. 8 characters" required />
            <button type="button" class="input-eye" onclick="togglePw('reg-password',this)">👁</button>
          </div>
          <div class="pw-strength" id="pw-strength" style="display:none">
            <div class="pw-bar">
              <div class="pw-bar__fill" id="pw-bar-fill"></div>
            </div>
            <span class="pw-lbl" id="pw-lbl">Weak</span>
          </div>
          <span class="form-error" id="err-reg-password"></span>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-confirm">Confirm Password</label>
          <div class="input-wrap">
            <input class="form-input" type="password" id="reg-confirm" placeholder="Repeat password" required />
            <button type="button" class="input-eye" onclick="togglePw('reg-confirm',this)">👁</button>
          </div>
          <span class="form-error" id="err-reg-confirm"></span>
        </div>

        <!-- Role Selection -->
        <div class="form-group">
          <label class="form-label">Account Role</label>
          <div class="role-cards" id="role-cards">
            <div class="role-card selected" data-role="owner" onclick="selectRole('owner',this)">
              <div class="role-card__icon">🏠</div>
              <div>
                <div class="role-card__name">Home Owner</div>
                <div class="role-card__desc">Full access — manage all devices, users & settings</div>
              </div>
              <div class="role-card__check"></div>
            </div>
            <div class="role-card" data-role="tenant" onclick="selectRole('tenant',this)">
              <div class="role-card__icon">👤</div>
              <div>
                <div class="role-card__name">Tenant</div>
                <div class="role-card__desc">View usage + control appliances up to 1500W</div>
              </div>
              <div class="role-card__check"></div>
            </div>
            <div class="role-card" data-role="guest" onclick="selectRole('guest',this)">
              <div class="role-card__icon">👁</div>
              <div>
                <div class="role-card__name">Guest</div>
                <div class="role-card__desc">View-only access — no control permissions</div>
              </div>
              <div class="role-card__check"></div>
            </div>
          </div>
          <input type="hidden" id="reg-role" value="owner" />
        </div>

        <!-- Home Owner — set home password -->
        <div id="owner-fields" class="form-group">
          <label class="form-label">Home Password</label>
          <div class="input-wrap">
            <input class="form-input" type="password" id="reg-home-password" placeholder="Password for your home" />
            <button type="button" class="input-eye" onclick="togglePw('reg-home-password',this)">👁</button>
          </div>
          <span class="form-error" id="err-reg-home-password"></span>
          <div id="home-code-preview" style="display:none;margin-top:8px;padding:10px 14px;background:var(--surface-2);border-radius:var(--r-sm);font-size:.85rem">
            🏠 Home Code: <strong id="home-code-val" style="color:var(--elec);letter-spacing:2px">—</strong>
            <span style="color:var(--text-3);font-size:.75rem;display:block;margin-top:4px">Share this code with tenants & guests</span>
          </div>
        </div>

        <!-- Tenant / Guest — join existing home -->
        <div id="join-fields" style="display:none">
          <div class="form-group">
            <label class="form-label">Home Code</label>
            <input class="form-input" type="text" id="reg-home-code" placeholder="e.g. A1B2C3D4" style="text-transform:uppercase;letter-spacing:2px" />
            <span class="form-error" id="err-reg-home-code"></span>
          </div>
          <div class="form-group">
            <label class="form-label">Home Password</label>
            <div class="input-wrap">
              <input class="form-input" type="password" id="reg-join-password" placeholder="Home password from owner" />
              <button type="button" class="input-eye" onclick="togglePw('reg-join-password',this)">👁</button>
            </div>
            <span class="form-error" id="err-reg-join-password"></span>
          </div>
        </div>

        <label class="auth-check">
          <input type="checkbox" id="reg-terms" required />
          <span class="auth-check__box"></span>
          I agree to the <a href="#" class="auth-link">Terms of Service</a> &amp; <a href="#" class="auth-link">Privacy Policy</a>
        </label>
        <span class="form-error" id="err-reg-terms"></span>

        <button type="submit" class="btn btn--primary btn--full btn--lg" id="btn-register">
          <span class="btn-txt">Create Account</span>
          <span class="btn-spin" style="display:none">⏳ Creating…</span>
        </button>
        <p class="auth-switch">Already have an account? <a href="#" class="auth-link" onclick="switchTab('login');return false">Sign in</a></p>
      </form>

      <!-- ══ FORGOT PASSWORD ══ -->
      <div class="auth-form" id="form-forgot" style="display:none">
        <div>
          <h1 class="auth-form__title">Reset Password</h1>
          <p class="auth-form__sub">We'll send a reset link to your email</p>
        </div>
        <div class="form-group">
          <label class="form-label" for="forgot-email">Email Address</label>
          <input class="form-input" type="email" id="forgot-email" placeholder="you@example.com" />
        </div>
        <button type="button" class="btn btn--primary btn--full btn--lg" onclick="handleForgot()">Send Reset Link</button>
        <p class="auth-switch"><a href="#" class="auth-link" onclick="switchTab('login');return false">← Back to sign in</a></p>
      </div>

      <!-- ══ SUCCESS STATE ══ -->
      <div class="auth-success" id="auth-success" style="display:none">
        <div class="auth-success__icon" id="success-icon">✅</div>
        <h2 class="auth-success__title" id="success-title">You're in!</h2>
        <p class="auth-success__msg" id="success-msg">Redirecting to your dashboard…</p>
        <a href="index.php" class="btn btn--primary btn--lg" id="success-btn">Go to Dashboard →</a>
      </div>

    </div><!-- /auth-panel -->
  </div><!-- /auth-wrap -->

  <script>
    /* ── Tab switching ── */
    function switchTab(tab) {
      ['login', 'register'].forEach(t => {
        document.getElementById(`form-${t}`).style.display = t === tab ? '' : 'none';
        document.getElementById(`tab-${t}`)?.classList.toggle('active', t === tab);
      });
      document.getElementById('form-forgot').style.display = 'none';
      document.getElementById('auth-success').style.display = 'none';
    }

    function showForgot(e) {
      e.preventDefault();
      ['login', 'register'].forEach(t => document.getElementById(`form-${t}`).style.display = 'none');
      document.getElementById('form-forgot').style.display = '';
      document.getElementById('tab-login')?.classList.remove('active');
      document.getElementById('tab-register')?.classList.remove('active');
    }

    /* ── Role cards ── */
    function selectRole(role, el) {
      document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
      el.classList.add('selected');
      document.getElementById('reg-role').value = role;

      if (role === 'owner') {
        document.getElementById('owner-fields').style.display = '';
        document.getElementById('join-fields').style.display = 'none';
        document.getElementById('reg-home').closest('.form-group').style.display = '';
        // Generate preview code
        const code = Math.random().toString(36).substring(2, 6).toUpperCase() +
          Math.random().toString(36).substring(2, 6).toUpperCase();
        document.getElementById('home-code-val').textContent = code;
        document.getElementById('home-code-preview').style.display = '';
      } else {
        document.getElementById('owner-fields').style.display = 'none';
        document.getElementById('join-fields').style.display = '';
        document.getElementById('reg-home').closest('.form-group').style.display = 'none';
      }
    }
    /* ── Password visibility ── */
    function togglePw(id, btn) {
      const inp = document.getElementById(id);
      inp.type = inp.type === 'password' ? 'text' : 'password';
      btn.textContent = inp.type === 'password' ? '👁' : '🙈';
    }

    /* ── Password strength ── */
    document.getElementById('reg-password').addEventListener('input', function() {
      const val = this.value;
      const wrap = document.getElementById('pw-strength');
      const fill = document.getElementById('pw-bar-fill');
      const lbl = document.getElementById('pw-lbl');
      if (!val) {
        wrap.style.display = 'none';
        return;
      }
      wrap.style.display = 'flex';
      let s = 0;
      if (val.length >= 8) s++;
      if (/[A-Z]/.test(val)) s++;
      if (/[0-9]/.test(val)) s++;
      if (/[^A-Za-z0-9]/.test(val)) s++;
      const lvls = [{
          pct: '25%',
          color: 'var(--danger)',
          txt: 'Weak'
        },
        {
          pct: '50%',
          color: 'var(--warn)',
          txt: 'Fair'
        },
        {
          pct: '75%',
          color: 'var(--water)',
          txt: 'Good'
        },
        {
          pct: '100%',
          color: 'var(--elec)',
          txt: 'Strong'
        },
      ];
      const l = lvls[(s || 1) - 1];
      fill.style.width = l.pct;
      fill.style.background = l.color;
      lbl.textContent = l.txt;
      lbl.style.color = l.color;
    });

    /* ── Validation helpers ── */
    function setErr(id, msg) {
      const el = document.getElementById(id);
      if (!el) return;
      el.textContent = msg;
      el.style.display = msg ? 'block' : 'none';
      const inp = el.closest('.form-group')?.querySelector?.('input');
      if (inp?.classList) inp.classList.toggle('form-input--error', !!msg);
    }

    function clearAll(...ids) {
      ids.forEach(id => setErr(id, ''));
    }

    /* ── Login Handler — Now calls PHP backend ── */
    function handleLogin(e) {
      e.preventDefault();

      clearAll('err-login-email', 'err-login-password');

      const email = document.getElementById('login-email').value.trim();
      const pass = document.getElementById('login-password').value;

      let ok = true;

      if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        setErr('err-login-email', 'Enter a valid email.');
        ok = false;
      }

      if (!pass || pass.length < 6) {
        setErr('err-login-password', 'Password must be at least 6 characters.');
        ok = false;
      }

      if (!ok) return;

      setBtnLoading('btn-login', true);

      const formData = new FormData();
      formData.append('action', 'login');
      formData.append('email', email);
      formData.append('password', pass);

      fetch('api/auth-api.php', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          setBtnLoading('btn-login', false);

          if (!data.ok) {
            setErr('err-login-email', data.msg);
            return;
          }

          localStorage.setItem('aot_session', JSON.stringify(data.user));

          showSuccess(
            'Welcome back, ' + data.user.firstName + '!',
            'Taking you to your dashboard…',
            '🏠'
          );

          setTimeout(() => {
            window.location.href = 'index.php';
          }, 1800);
        })
        .catch(err => {
          setBtnLoading('btn-login', false);
          setErr('err-login-email', 'Connection error. Please try again.');
          console.error(err);
        });
    } /* ── Register Handler — Now calls PHP backend ── */
    function handleRegister(e) {
      e.preventDefault();
      clearAll('err-reg-first', 'err-reg-last', 'err-reg-email', 'err-reg-home', 'err-reg-password', 'err-reg-confirm', 'err-reg-terms');

      const first = document.getElementById('reg-first').value.trim();
      const last = document.getElementById('reg-last').value.trim();
      const email = document.getElementById('reg-email').value.trim();
      const home = document.getElementById('reg-home').value.trim();
      const pass = document.getElementById('reg-password').value;
      const confirm = document.getElementById('reg-confirm').value;
      const role = document.getElementById('reg-role').value;
      const terms = document.getElementById('reg-terms').checked;
      let ok = true;

      if (!first) {
        setErr('err-reg-first', 'First name is required.');
        ok = false;
      }
      if (!last) {
        setErr('err-reg-last', 'Last name is required.');
        ok = false;
      }
      if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        setErr('err-reg-email', 'Enter a valid email.');
        ok = false;
      }
      if (role === 'owner' && !home) {
        setErr('err-reg-home', 'Home name is required.');
        ok = false;
      }
      if (pass.length < 8) {
        setErr('err-reg-password', 'Password must be at least 8 characters.');
        ok = false;
      }
      if (pass !== confirm) {
        setErr('err-reg-confirm', 'Passwords do not match.');
        ok = false;
      }
      if (!terms) {
        setErr('err-reg-terms', 'You must agree to the terms.');
        ok = false;
      }

      if (role === 'owner') {
        const hp = document.getElementById('reg-home-password').value;
        if (!hp || hp.length < 6) {
          setErr('err-reg-home-password', 'Home password must be at least 6 characters.');
          ok = false;
        }
      } else {
        if (!document.getElementById('reg-home-code').value.trim()) {
          setErr('err-reg-home-code', 'Home code is required.');
          ok = false;
        }
        if (!document.getElementById('reg-join-password').value) {
          setErr('err-reg-join-password', 'Home password is required.');
          ok = false;
        }
      }

      if (!ok) return;

      setBtnLoading('btn-register', true);

      const formData = new FormData();
      formData.append('action', 'register');
      formData.append('firstName', first);
      formData.append('lastName', last);
      formData.append('email', email);
      formData.append('homeName', home);
      formData.append('password', pass);
      formData.append('role', role);

      if (role === 'owner') {
        formData.append('homePassword', document.getElementById('reg-home-password').value);
      } else {
        formData.append('homeCode', document.getElementById('reg-home-code').value.trim().toUpperCase());
        formData.append('homePassword', document.getElementById('reg-join-password').value);
      }

      fetch('api/auth-api.php', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          setBtnLoading('btn-register', false);
          if (!data.ok) {
            setErr('err-reg-email', data.msg);
            return;
          }
          if (data.home_code) {
            showSuccess('Account created, ' + first + '!', '🏠 Your Home Code: ' + data.home_code + ' — share it with tenants & guests', '🎉');
          } else {
            showSuccess('Joined home successfully!', 'Redirecting to dashboard…', '🏠');
          }
          setTimeout(() => window.location.href = 'index.php', 3000);
        })
        .catch(err => {
          setBtnLoading('btn-register', false);
          setErr('err-reg-email', 'Connection error. Please try again.');
          console.error(err);
        });
    }
    /* ── Forgot Password ── */
    function handleForgot() {
      const email = document.getElementById('forgot-email').value.trim();
      if (!email) {
        showToast('Please enter your email address.', 'warn');
        return;
      }
      showSuccess('Reset link sent!', 'Check ' + email + ' for your reset link.', '📧');
      // TODO: PHP backend for forgot password
    }

    /* ── Demo Login ── */
    function demoLogin() {
      document.getElementById('login-email').value = 'demo@aothomes.com';
      document.getElementById('login-password').value = 'demo123456';
      handleLogin(new Event('submit'));
    }

    /* ── UI helpers ── */
    function setBtnLoading(id, on) {
      const btn = document.getElementById(id);
      if (!btn) return;
      btn.querySelector('.btn-txt').style.display = on ? 'none' : '';
      btn.querySelector('.btn-spin').style.display = on ? '' : 'none';
      btn.disabled = on;
    }

    function showSuccess(title, msg, icon = '✅') {
      ['login', 'register', 'forgot'].forEach(t => {
        const f = document.getElementById('form-' + t);
        if (f) f.style.display = 'none';
      });
      document.getElementById('auth-success').style.display = '';
      document.getElementById('success-icon').textContent = icon;
      document.getElementById('success-title').textContent = title;
      document.getElementById('success-msg').textContent = msg;
    }
  </script>
</body>

</html>