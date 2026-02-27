<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Verify Account – Aluminum Lady</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap"
    rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --gold: #b8935a;
      --gold-light: #d4aa72;
      --dark: #1a1a18;
      --surface: #1c1c1a;
      --text: #e8e4dc;
      --muted: #888880;
    }

    html, body { height: 100%; overflow: hidden; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--dark);
      color: var(--text);
      display: flex;
    }

    /* ── TRANSITION OVERLAY ── */
    .transition-overlay {
      position: fixed; inset: 0; z-index: 999;
      pointer-events: none; display: flex; flex-direction: column;
    }
    .overlay-panel {
      flex: 1; transform: translateX(100%);
      transition: transform 0.65s cubic-bezier(0.76, 0, 0.24, 1);
    }
    .overlay-panel:nth-child(1) { background: #111110; transition-delay: 0s; }
    .overlay-panel:nth-child(2) { background: #151513; transition-delay: 0.06s; }
    .overlay-panel:nth-child(3) { background: #0e0e0c; transition-delay: 0.12s; }
    .transition-overlay.active { pointer-events: all; }
    .transition-overlay.active .overlay-panel { transform: translateX(0); }
    .transition-overlay.active ~ .split { transform: translateX(-50px); opacity: 0; }

    /* ── SPLIT ── */
    .split {
      display: flex; width: 100%; height: 100vh;
      transition: transform 0.6s cubic-bezier(0.76, 0, 0.24, 1), opacity 0.4s ease;
    }

    /* ── LEFT PANEL (form) ── */
    .left-panel {
      flex: 1; background: #111110; height: 100vh;
      display: flex; align-items: center; justify-content: center;
      position: relative; overflow: hidden;
      animation: slideInLeft 0.9s cubic-bezier(0.76, 0, 0.24, 1) both;
    }
    @keyframes slideInLeft {
      from { opacity: 0; transform: translateX(-30px); }
      to   { opacity: 1; transform: translateX(0); }
    }

    .left-inner {
      width: 100%; max-width: 420px; padding: 0 2rem;
      animation: fadeUp 0.6s 0.25s ease both;
    }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(16px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ── BRAND ROW ── */
    .brand-row {
      display: flex; align-items: center; justify-content: center;
      gap: 0.6rem; margin-bottom: 2rem;
    }
    .brand-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--gold); }
    .brand-name {
      font-family: 'Cormorant Garamond', serif; font-size: 0.85rem;
      font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--gold);
    }

    /* ── ICON ── */
    .otp-icon-wrap {
      position: relative; width: 72px; height: 72px;
      margin: 0 auto 1.5rem;
    }
    .otp-icon-wrap::before {
      content: ''; position: absolute; inset: -5px;
      border: 1.5px solid transparent;
      border-top-color: var(--gold); border-right-color: rgba(184,147,90,0.3);
      border-radius: 50%; animation: spin 3s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .otp-icon {
      width: 72px; height: 72px;
      background: rgba(184, 147, 90, 0.08);
      border: 1.5px solid rgba(184, 147, 90, 0.22);
      border-radius: 50%; display: flex; align-items: center; justify-content: center;
    }
    .otp-icon svg { width: 30px; height: 30px; color: var(--gold); }

    /* ── HEADER TEXT ── */
    .otp-header { text-align: center; margin-bottom: 2rem; }
    .otp-header .tag {
      display: block; font-size: 0.66rem; letter-spacing: 0.2em;
      text-transform: uppercase; color: var(--muted); margin-bottom: 0.4rem;
    }
    .otp-header h2 {
      font-family: 'Cormorant Garamond', serif; font-size: 2rem;
      font-weight: 400; color: var(--text); line-height: 1.15;
    }
    .otp-header .subtitle {
      font-size: 0.82rem; color: var(--muted); margin-top: 0.5rem;
      font-weight: 300; line-height: 1.6;
    }
    .otp-header .email-highlight {
      color: var(--gold); font-weight: 500; display: block; margin-top: 0.2rem;
    }

    /* ── OTP INPUT ROW ── */
    .otp-inputs {
      display: flex; gap: 0.6rem; justify-content: center;
      margin-bottom: 0.6rem;
    }
    .otp-inputs input {
      width: 52px; height: 60px;
      background: var(--surface);
      border: 1px solid rgba(255,255,255,0.07);
      border-bottom: 2px solid rgba(184,147,90,0.3);
      border-radius: 6px;
      color: var(--text);
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.8rem; font-weight: 600;
      text-align: center; outline: none;
      transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
      caret-color: var(--gold);
    }
    .otp-inputs input:focus {
      border-color: var(--gold);
      border-bottom-color: var(--gold);
      background: #202018;
      box-shadow: 0 0 0 3px rgba(184, 147, 90, 0.1);
    }
    .otp-inputs input.filled {
      border-bottom-color: var(--gold);
      background: rgba(184, 147, 90, 0.05);
    }
    .otp-inputs input.input-error {
      border-color: #c0392b !important;
      box-shadow: 0 0 0 3px rgba(192, 57, 43, 0.12);
      animation: shake 0.35s ease;
    }
    @keyframes shake {
      0%,100% { transform: translateX(0); }
      20%,60%  { transform: translateX(-4px); }
      40%,80%  { transform: translateX(4px); }
    }

    /* ── TIMER ── */
    .otp-timer {
      text-align: center; font-size: 0.78rem;
      color: var(--muted); margin-bottom: 1.6rem;
    }
    .otp-timer .countdown {
      color: var(--gold); font-weight: 500; font-variant-numeric: tabular-nums;
    }
    .otp-timer .expired { color: #e05454; }

    /* ── SUBMIT BUTTON ── */
    .btn-verify {
      width: 100%; padding: 0.9rem;
      background: var(--gold); color: #0e0e0c;
      border: none; border-radius: 4px;
      font-family: 'DM Sans', sans-serif; font-size: 0.8rem;
      font-weight: 600; letter-spacing: 0.14em; text-transform: uppercase;
      cursor: pointer; transition: background 0.2s, box-shadow 0.2s, transform 0.1s;
      margin-bottom: 0.9rem;
    }
    .btn-verify:hover { background: var(--gold-light); box-shadow: 0 4px 20px rgba(184,147,90,0.35); }
    .btn-verify:active { transform: scale(0.995); }
    .btn-verify:disabled { opacity: 0.5; cursor: not-allowed; }

    /* ── RESEND ROW ── */
    .resend-row {
      text-align: center; font-size: 0.8rem; color: var(--muted);
      margin-bottom: 0.75rem;
    }
    .resend-row button {
      background: none; border: none; cursor: pointer;
      color: var(--gold); font-size: 0.8rem; font-weight: 500;
      font-family: 'DM Sans', sans-serif;
      border-bottom: 1px solid rgba(184,147,90,0.3);
      padding-bottom: 1px; transition: border-color 0.2s;
    }
    .resend-row button:hover { border-color: var(--gold); }
    .resend-row button:disabled { opacity: 0.4; cursor: not-allowed; border-color: transparent; }

<<<<<<< HEAD
if ($user['status'] === 'suspended') {  
    out(false, 'Your account has been suspended. Please contact support.', null, 403);
}
=======
    .back-row {
      text-align: center; font-size: 0.78rem; color: var(--muted);
    }
    .back-row a {
      color: rgba(136,136,128,0.7); text-decoration: none;
      display: inline-flex; align-items: center; gap: 0.3rem;
      transition: color 0.2s; cursor: pointer;
    }
    .back-row a:hover { color: var(--text); }
>>>>>>> 0bbb6053bd18e028f65a332da93dc30db426a13d

    /* ── SUCCESS STATE ── */
    .success-state {
      display: none; text-align: center;
    }
    .success-state.show { display: block; }
    .success-icon {
      width: 72px; height: 72px; border-radius: 50%;
      background: rgba(84, 176, 102, 0.12);
      border: 1.5px solid rgba(84, 176, 102, 0.35);
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 1.25rem;
    }
    .success-icon svg {
      width: 30px; height: 30px; stroke: #54b066; fill: none;
      stroke-width: 2.2; stroke-dasharray: 50; stroke-dashoffset: 50;
      transition: stroke-dashoffset 0.55s ease 0.1s;
    }
    .success-icon.animate svg { stroke-dashoffset: 0; }
    .success-state h3 {
      font-family: 'Cormorant Garamond', serif; font-size: 1.75rem;
      font-weight: 400; color: var(--text); margin-bottom: 0.5rem;
    }
    .success-state p { font-size: 0.82rem; color: var(--muted); line-height: 1.6; margin-bottom: 1.75rem; }

    /* ── SPLIT DIVIDER ── */
    .split-divider {
      width: 1px; flex-shrink: 0;
      background: linear-gradient(to bottom, transparent 0%, rgba(201,165,97,0.35) 15%, rgba(201,165,97,0.55) 50%, rgba(201,165,97,0.35) 85%, transparent 100%);
      z-index: 3;
    }

    /* ── RIGHT PANEL ── */
    .right-panel {
      flex: 1; position: relative; overflow: hidden;
      animation: slideInRight 0.9s cubic-bezier(0.76, 0, 0.24, 1) both;
    }
    @keyframes slideInRight {
      from { opacity: 0; transform: translateX(30px); }
      to   { opacity: 1; transform: translateX(0); }
    }
    .right-bg {
      position: absolute; inset: 0;
      background-image: url('https://images.unsplash.com/photo-1486325212027-8081e485255e?w=1200&q=85&fit=crop&crop=center');
      background-size: cover; background-position: center 60%;
      filter: brightness(0.6) saturate(0.65) sepia(0.3);
      transition: transform 14s ease;
    }
    .right-panel:hover .right-bg { transform: scale(1.05); }
    .right-overlay {
      position: absolute; inset: 0;
      background:
        linear-gradient(180deg, rgba(36,18,6,0.88) 0%, rgba(74,46,24,0.52) 35%, rgba(107,66,38,0.22) 55%, rgba(74,46,24,0.52) 80%, rgba(28,14,4,0.95) 100%),
        linear-gradient(90deg, rgba(201,165,97,0.06) 0%, transparent 70%);
    }
    .right-content {
      position: relative; z-index: 2; height: 100%;
      display: flex; flex-direction: column; justify-content: space-between;
      padding: 3rem 3.5rem;
    }
    .right-logo { display: flex; align-items: center; gap: 0.75rem; }
    .logo-mark {
      width: 42px; height: 42px;
      background: linear-gradient(135deg, rgba(74,46,24,0.9), var(--gold));
      border-radius: 10px; display: flex; align-items: center; justify-content: center;
      font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 700; color: #fff;
      box-shadow: 0 4px 18px rgba(201,165,97,0.4); border: 1px solid rgba(201,165,97,0.35);
    }
    .logo-text .logo-name { font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; font-weight: 600; color: #fff; line-height: 1.1; }
    .logo-text .logo-name span { color: var(--gold); font-style: italic; }
    .logo-text .logo-sub { font-size: 0.6rem; color: rgba(255,255,255,0.35); letter-spacing: 2.5px; text-transform: uppercase; margin-top: 2px; }
    .right-divider { width: 52px; height: 1px; background: linear-gradient(90deg, var(--gold), transparent); margin-bottom: 1.4rem; opacity: 0.55; }
    .right-quote {
      font-family: 'Cormorant Garamond', serif; font-size: clamp(1.5rem, 2.6vw, 2.2rem);
      font-weight: 300; font-style: italic; line-height: 1.4;
      color: rgba(232,228,220,0.9); margin-bottom: 1.5rem; max-width: 400px;
    }
    .right-quote::before {
      content: '\201C'; color: var(--gold); font-size: 3.5rem;
      line-height: 0; vertical-align: -1.1rem; margin-right: 0.15rem; opacity: 0.65;
    }
    .right-meta { font-size: 0.67rem; letter-spacing: 0.2em; text-transform: uppercase; color: rgba(201,165,97,0.6); }

    /* ── STEPS INDICATOR ── */
    .steps {
      display: flex; align-items: center; justify-content: center;
      gap: 0.5rem; margin-bottom: 1.75rem;
    }
    .step {
      display: flex; align-items: center; gap: 0.4rem;
      font-size: 0.68rem; letter-spacing: 0.1em; text-transform: uppercase;
    }
    .step-dot {
      width: 6px; height: 6px; border-radius: 50%;
      background: rgba(136,136,128,0.3);
    }
    .step.done .step-dot { background: rgba(184,147,90,0.5); }
    .step.active .step-dot { background: var(--gold); box-shadow: 0 0 6px rgba(184,147,90,0.5); }
    .step-label { color: var(--muted); }
    .step.done .step-label { color: rgba(184,147,90,0.6); }
    .step.active .step-label { color: var(--gold); }
    .step-line { width: 20px; height: 1px; background: rgba(136,136,128,0.2); }
    .step-line.done { background: rgba(184,147,90,0.4); }

    /* ── TOAST ── */
    .toast {
      position: fixed; bottom: 2rem; left: 2rem;
      background: rgba(34,34,32,0.92); backdrop-filter: blur(16px);
      border-left: 3px solid var(--gold); padding: 1rem 1.4rem;
      font-size: 0.82rem; color: var(--text); box-shadow: 0 8px 32px rgba(0,0,0,0.45);
      transform: translateX(-20px); opacity: 0;
      transition: all 0.35s ease; pointer-events: none; z-index: 99;
    }
    .toast.show { transform: translateX(0); opacity: 1; }

    @media (max-width: 768px) {
      .right-panel, .split-divider { display: none; }
      .left-panel { width: 100%; }
    }
  </style>
</head>

<body>

  <div class="transition-overlay" id="transitionOverlay">
    <div class="overlay-panel"></div>
    <div class="overlay-panel"></div>
    <div class="overlay-panel"></div>
  </div>

  <div class="split">

    <!-- ── LEFT PANEL ── -->
    <div class="left-panel">
      <div class="left-inner">

        <div class="brand-row">
          <div class="brand-dot"></div>
          <span class="brand-name">Mrs. Alu</span>
        </div>

        <!-- Step indicator -->
        <div class="steps">
          <div class="step done">
            <div class="step-dot"></div>
            <span class="step-label">Register</span>
          </div>
          <div class="step-line done"></div>
          <div class="step active">
            <div class="step-dot"></div>
            <span class="step-label">Verify</span>
          </div>
          <div class="step-line"></div>
          <div class="step">
            <div class="step-dot"></div>
            <span class="step-label">Done</span>
          </div>
        </div>

        <!-- ── VERIFY FORM STATE ── -->
        <div id="verifyState">
          <div class="otp-icon-wrap">
            <div class="otp-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2"/>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
              </svg>
            </div>
          </div>

          <div class="otp-header">
            <span class="tag">Step 2 of 2</span>
            <h2>Verify your<br>email address</h2>
            <p class="subtitle">
              We've sent a 6-digit code to
              <span class="email-highlight" id="displayEmail">your email</span>
            </p>
          </div>

          <div class="otp-inputs" id="otpInputs">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="one-time-code" />
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" />
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" />
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" />
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" />
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" />
          </div>

          <div class="otp-timer" id="otpTimer">
            Code expires in <span class="countdown" id="countdown">10:00</span>
          </div>

          <button class="btn-verify" id="verifyBtn" disabled>Verify My Account</button>

          <div class="resend-row">
            Didn't receive it?
            <button id="resendBtn" disabled>Resend code</button>
            <span id="resendTimer" style="color:var(--muted);font-size:0.78rem;"></span>
          </div>

          <div class="back-row">
            <a onclick="transitionTo('login.html'); return false;">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5M12 5l-7 7 7 7"/>
              </svg>
              Back to sign in
            </a>
          </div>
        </div>

        <!-- ── SUCCESS STATE ── -->
        <div class="success-state" id="successState">
          <div class="success-icon" id="successIcon">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <h3>Account Verified!</h3>
          <p>Your email has been confirmed.<br>Redirecting you to the homepage…</p>
        </div>

      </div>
    </div>

    <div class="split-divider"></div>

    <!-- ── RIGHT PANEL ── -->
    <div class="right-panel">
      <div class="right-bg"></div>
      <div class="right-overlay"></div>
      <div class="right-content">
        <div class="right-logo">
          <div class="logo-mark">A</div>
          <div class="logo-text">
            <div class="logo-name">Mrs.<span>Alu</span></div>
            <div class="logo-sub">Aluminum Lady</div>
          </div>
        </div>
        <div>
          <div class="right-divider"></div>
          <p class="right-quote">Build with confidence. Design with purpose.</p>
          <div class="right-meta">Est. 2024 &nbsp;&middot;&nbsp; Premium Building Materials</div>
        </div>
      </div>
    </div>

  </div>

  <div class="toast" id="toast"></div>

  <script>
    // ── Transition ────────────────────────────────────────────────────────────
    function transitionTo(url) {
      const overlay = document.getElementById('transitionOverlay');
      overlay.classList.add('active');
      setTimeout(() => { window.location.href = url; }, 800);
    }

    // ── Load user/email from sessionStorage ───────────────────────────────────
    const userData = JSON.parse(sessionStorage.getItem('user') || '{}');
    const userEmail = userData.email || '';
    const userId    = userData.user_id || '';

    if (!userEmail) {
      // No session — redirect back to login
      transitionTo('login.html');
    }

    document.getElementById('displayEmail').textContent = userEmail;

    // ── Toast ─────────────────────────────────────────────────────────────────
    function showToast(msg, error = false) {
      const t = document.getElementById('toast');
      t.textContent = msg;
      t.style.borderLeftColor = error ? '#e05454' : 'var(--gold)';
      t.classList.add('show');
      setTimeout(() => t.classList.remove('show'), 3500);
    }

    // ── OTP Input Logic ───────────────────────────────────────────────────────
    const inputs = Array.from(document.querySelectorAll('#otpInputs input'));
    const verifyBtn = document.getElementById('verifyBtn');

    inputs.forEach((input, i) => {
      input.addEventListener('input', (e) => {
        // Only allow digits
        input.value = input.value.replace(/\D/g, '');
        input.classList.toggle('filled', input.value !== '');
        input.classList.remove('input-error');

        if (input.value && i < inputs.length - 1) {
          inputs[i + 1].focus();
        }
        checkComplete();
      });

      input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace') {
          if (!input.value && i > 0) {
            inputs[i - 1].value = '';
            inputs[i - 1].classList.remove('filled');
            inputs[i - 1].focus();
          }
          input.classList.remove('input-error');
          checkComplete();
        }
        if (e.key === 'ArrowLeft' && i > 0) inputs[i - 1].focus();
        if (e.key === 'ArrowRight' && i < inputs.length - 1) inputs[i + 1].focus();
      });

      // Handle paste
      input.addEventListener('paste', (e) => {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData)
          .getData('text').replace(/\D/g, '').slice(0, 6);
        pasted.split('').forEach((ch, j) => {
          if (inputs[j]) {
            inputs[j].value = ch;
            inputs[j].classList.add('filled');
          }
        });
        const next = Math.min(pasted.length, inputs.length - 1);
        inputs[next].focus();
        checkComplete();
      });
    });

    function checkComplete() {
      const code = inputs.map(i => i.value).join('');
      verifyBtn.disabled = code.length < 6;
    }

    function getOtpCode() {
      return inputs.map(i => i.value).join('');
    }

    function markError() {
      inputs.forEach(i => i.classList.add('input-error'));
      setTimeout(() => inputs.forEach(i => i.classList.remove('input-error')), 700);
    }

    // ── Countdown Timer (10 min OTP expiry) ───────────────────────────────────
    let expirySeconds = 10 * 60;
    let countdownInterval = null;

    function startCountdown() {
      expirySeconds = 10 * 60;
      clearInterval(countdownInterval);
      const el = document.getElementById('countdown');
      const timerEl = document.getElementById('otpTimer');

      countdownInterval = setInterval(() => {
        expirySeconds--;
        if (expirySeconds <= 0) {
          clearInterval(countdownInterval);
          el.textContent = '0:00';
          el.classList.add('expired');
          timerEl.innerHTML = '<span class="expired">Code has expired — please request a new one.</span>';
          verifyBtn.disabled = true;
          return;
        }
        const m = Math.floor(expirySeconds / 60);
        const s = String(expirySeconds % 60).padStart(2, '0');
        el.textContent = `${m}:${s}`;
      }, 1000);
    }

    // ── Resend cooldown (60 sec) ──────────────────────────────────────────────
    let resendSeconds = 60;
    let resendInterval = null;
    const resendBtn   = document.getElementById('resendBtn');
    const resendTimer = document.getElementById('resendTimer');

    function startResendCooldown() {
      resendSeconds = 60;
      resendBtn.disabled = true;
      clearInterval(resendInterval);
      resendInterval = setInterval(() => {
        resendSeconds--;
        resendTimer.textContent = `(${resendSeconds}s)`;
        if (resendSeconds <= 0) {
          clearInterval(resendInterval);
          resendBtn.disabled = false;
          resendTimer.textContent = '';
        }
      }, 1000);
    }

    // ── Send OTP on page load ─────────────────────────────────────────────────
    async function sendOtp() {
      try {
        const res  = await fetch('send-otp.php', {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body:    JSON.stringify({ user_id: userId, email: userEmail }),
        });
        const json = await res.json();
        if (json.success) {
          showToast('✓ OTP sent to ' + userEmail);
          startCountdown();
          startResendCooldown();
        } else {
          showToast('⚠ ' + (json.message || 'Failed to send OTP.'), true);
        }
      } catch {
        showToast('⚠ Network error sending OTP.', true);
      }
    }

    sendOtp();

    // ── Resend button ─────────────────────────────────────────────────────────
    resendBtn.addEventListener('click', async () => {
      inputs.forEach(i => { i.value = ''; i.classList.remove('filled', 'input-error'); });
      verifyBtn.disabled = true;

      // Reset countdown label
      const timerEl = document.getElementById('otpTimer');
      const countEl = document.getElementById('countdown');
      countEl.classList.remove('expired');
      timerEl.innerHTML = 'Code expires in <span class="countdown" id="countdown">10:00</span>';

      await sendOtp();
    });

    // ── Verify OTP ────────────────────────────────────────────────────────────
    verifyBtn.addEventListener('click', async () => {
      const code = getOtpCode();
      if (code.length < 6) return;

      verifyBtn.disabled = true;
      verifyBtn.textContent = 'Verifying…';

      try {
        const res  = await fetch('verify-otp.php', {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body:    JSON.stringify({ user_id: userId, email: userEmail, otp: code }),
        });
        const json = await res.json();

        if (!json.success) {
          showToast('⚠ ' + (json.message || 'Invalid OTP. Please try again.'), true);
          markError();
          verifyBtn.disabled = false;
          verifyBtn.textContent = 'Verify My Account';
          return;
        }

        // ── Verified ──────────────────────────────────────────────────────────
        clearInterval(countdownInterval);
        clearInterval(resendInterval);

        // Update user session as verified
        const updatedUser = { ...userData, is_verified: true };
        sessionStorage.setItem('user', JSON.stringify(updatedUser));

        // Show success state
        document.getElementById('verifyState').style.display  = 'none';
        const ss = document.getElementById('successState');
        ss.classList.add('show');
        setTimeout(() => {
          document.getElementById('successIcon').classList.add('animate');
        }, 50);

        // Redirect to homepage after 2s
        setTimeout(() => transitionTo('../Homepage/index.html'), 2200);

      } catch {
        showToast('⚠ Network error. Please try again.', true);
        verifyBtn.disabled = false;
        verifyBtn.textContent = 'Verify My Account';
      }
    });

    // Focus first input on load
    inputs[0].focus();
  </script>

</body>
</html>