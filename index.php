<style>
/* ==================== FONT ==================== */

@font-face {
  font-family: "Mona Sans";
  src: url("dist/font/monasans/MonaSans-Regular.ttf") format("truetype");
  font-display: swap;
}

/* ==================== THEME TOKENS ==================== */

:root {
  --login-bg: #fff;
  --login-surface: #ffffff;
  --login-surface-soft: #f6f8fa;

  --login-border: #d0d7de;
  --login-border-soft: #e6e8eb;

  --login-text: #1f2328;
  --login-muted: #656d76;
  --login-subtle: #8c959f;

  --login-blue: #0969da;
  --login-blue-hover: #0550ae;

  --login-danger: #cf222e;
  --login-danger-bg: #fff8f7;
  --login-danger-border: #ff818266;

  --login-focus: rgba(9, 105, 218, 0.18);
}

html[data-theme="dark"] {
  --login-bg: #0d1117;
  --login-surface: #161b22;
  --login-surface-soft: #21262d;

  --login-border: #30363d;
  --login-border-soft: #21262d;

  --login-text: #f0f6fc;
  --login-muted: #8b949e;
  --login-subtle: #6e7681;

  --login-blue: #58a6ff;
  --login-blue-hover: #79c0ff;

  --login-danger: #f85149;
  --login-danger-bg: #2d1117;
  --login-danger-border: #f8514966;

  --login-focus: rgba(88, 166, 255, 0.2);
}

/* ==================== PAGE ==================== */

.login-page {
  position: fixed;
  inset: 0;
  display: flex;
  overflow: hidden;
  width: 100%;
  height: 100dvh;
  overflow: hidden;
  background: var(--login-bg);
  color: var(--login-text);
  font-family: "Mona Sans", sans-serif;
  -webkit-font-smoothing: antialiased;
  text-rendering: optimizeLegibility;
  transition:
    background-color 0.2s ease,
    color 0.2s ease;
}

html,
body {
  margin: 0;
  padding: 0;
  background: var(--login-bg);
}

/* ==================== THEME TOGGLE ==================== */

.theme-toggle {
  position: absolute;
  top: 20px;
  right: 20px;
  z-index: 20;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  border: 1px solid var(--login-border);
  border-radius: 50%;
  background: var(--login-surface);
  color: var(--login-text);
  font-size: 13px;
  cursor: pointer;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
  transition:
    border-color 0.15s ease,
    background-color 0.15s ease,
    transform 0.15s ease,
    box-shadow 0.15s ease;
}

.theme-toggle:hover {
  border-color: var(--login-text);
  background: var(--login-surface-soft);
  transform: translateY(-1px);
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
}

.theme-toggle:active {
  transform: translateY(0);
}

.theme-toggle:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px var(--login-focus);
}

.theme-toggle i {
  transition: transform 0.2s ease;
}

.theme-toggle:hover i {
  transform: rotate(12deg);
}

/* ==================== SYSTEM PANEL ==================== */

.system-panel {
  --panel-text: #fafbfc;
  --panel-muted: rgba(250, 251, 252, 0.72);
  --panel-border: rgba(255, 255, 255, 0.24);
  --panel-surface: rgba(255, 255, 255, 0.08);
  --panel-surface-hover: rgba(255, 255, 255, 0.16);
  --panel-accent: #ffffff;
  --panel-primary: #0969da;
  --panel-primary-hover: #0860ca;
  --panel-photo-opacity: 0.16; /* raise to show more photo */

  position: relative;
  isolation: isolate;
  display: flex;
  flex-direction: column;
  width: 46%;
  height: 100dvh;
  padding: clamp(32px, 5vw, 72px);
  color: var(--panel-text);
  background: linear-gradient(
    145deg,
    rgba(0, 43, 77, 0.97) 0%,
    rgba(0, 59, 103, 0.94) 48%,
    rgba(0, 45, 80, 0.97) 100%
  );
  border-right: 1px solid var(--login-border-soft);
  overflow: hidden;
}

.system-panel::before {
  content: "";
  position: absolute;
  z-index: -2;
  inset: 0;
  background: url("dist/img/furukawa-bg.JPG") 100% center / cover no-repeat;
  opacity: var(--panel-photo-opacity);
  filter: grayscale(100%) blur(1px);
  pointer-events: none;
}

.system-panel::after {
  content: "";
  position: absolute;
  z-index: -1;
  inset: 0;
  background:
    radial-gradient(circle at 15% 20%, rgba(69, 157, 224, 0.2), transparent 34%),
    radial-gradient(circle at 85% 80%, rgba(0, 113, 227, 0.16), transparent 40%),
    linear-gradient(
      145deg,
      rgba(0, 43, 77, 0.38),
      rgba(0, 59, 103, 0.18),
      rgba(0, 45, 80, 0.42)
    );
  pointer-events: none;
}

/* ==================== BRAND ==================== */

.brand {
  display: flex;
  align-items: center;
  gap: 13px;
}

.brand-icon {
  width: 52px;
  height: 52px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  overflow: hidden;
}

.brand-icon img,
.system-icon img {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: contain;
  object-position: center;
}

.brand-name {
  margin: 0;
  color: var(--panel-text);
  font-size: 13px;
  line-height: 1.35;
  font-weight: 600;
  letter-spacing: -0.2px;
}

.brand-subtitle {
  margin: 3px 0 0;
  color: var(--panel-muted);
  font-size: 12px;
  line-height: 1.4;
}

/* ==================== SYSTEM IDENTITY ==================== */

.system-identity {
  position: relative;
  z-index: 1;
  width: 100%;
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: center;
  padding-bottom: 140px; /* raise = bigger number */
}

.system-icon {
  width: 76px;   /* was 56px */
  height: 76px;  /* was 56px */
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 18px;
  overflow: hidden;
}

.system-title {
  max-width: 540px;
  margin: 0 0 16px;
  color: var(--panel-text);
  font-size: clamp(38px, 3.5vw, 52px);
  line-height: 1.04;
  font-weight: 650;
  letter-spacing: -2.6px;
}

.system-departments {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 6px;
  margin-bottom: 13px;
}

.system-department {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 25px;
  padding: 0 9px;
  border: 1px solid var(--panel-border);
  border-radius: 999px;
  background: var(--panel-surface);
  color: var(--panel-accent);
  font-size: 11px;
  font-weight: 650;
  line-height: 1;
  white-space: nowrap;
}

.system-description {
  max-width: 455px;
  margin: 0;
  color: var(--panel-muted);
  font-size: 14px;
  line-height: 1.65;
  letter-spacing: -0.05px;
}

/* ==================== SYSTEM ACTIONS ==================== */

.system-actions {
  margin-top: 40px;
}

.nexus-dropdown {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px;
}

.system-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  min-height: 38px;
  padding: 0 15px;
  border: 0;
  border-radius: 7px;
  background: var(--panel-surface);
  color: var(--panel-text);
  font-size: 12px;
  font-weight: 600;
  line-height: 1;
  text-decoration: none;
  transition:
    background-color 0.18s ease,
    transform 0.18s ease;
}

.system-action svg {
  width: 15px;
  height: 15px;
  flex-shrink: 0;
}

.system-action:hover {
  background: var(--panel-surface-hover);
  color: var(--panel-text);
  transform: translateY(-1px);
}

.system-action:active {
  transform: translateY(0);
}

.system-action.primary {
  background: var(--panel-surface);
  color: var(--panel-text);
}

.system-action.primary:hover {
  background: var(--panel-surface-hover);
}

/* ==================== LOGIN PANEL ==================== */

.login-panel {
  width: 54%;
  height: 100dvh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: clamp(24px, 4vw, 56px);
  overflow-x: hidden;
  overflow-y: auto;
  background: var(--login-bg);
}

.login-content {
  width: 100%;
  max-width: 410px;
  margin: auto;
}

.login-header {
  margin-bottom: 18px;
}

.login-title {
  margin: 0 0 5px;
  color: var(--login-text);
  font-size: clamp(23px, 2vw, 28px);
  line-height: 1.2;
  font-weight: 600;
  letter-spacing: -0.8px;
}

.login-subtitle {
  margin: 0;
  color: var(--login-muted);
  font-size: 13px;
  line-height: 1.5;
}

/* ==================== LOGIN ERROR ==================== */

.login-error {
  display: flex;
  align-items: flex-start;
  gap: 9px;
  margin-bottom: 17px;
  padding: 10px 12px;
  border: 1px solid var(--login-danger-border);
  border-radius: 7px;
  background: var(--login-danger-bg);
  color: var(--login-danger);
  font-size: 12px;
  line-height: 1.45;
}

.login-error i {
  flex-shrink: 0;
  margin-top: 1px;
  font-size: 14px;
}

/* ==================== FORM ==================== */

.login-form-group {
  margin-bottom: 18px;
}

.login-form-group.password-group {
  margin-bottom: 20px;
}

.login-label {
  display: block;
  margin: 0 0 6px;
  color: var(--login-text);
  font-size: 12px;
  font-weight: 600;
  line-height: 1.4;
}

.login-input {
  width: 100%;
  height: 40px;
  padding: 8px 11px;
  border: 1px solid var(--login-border);
  border-radius: 6px;
  background: var(--login-surface);
  color: var(--login-text);
  font-family: inherit;
  font-size: 13px;
  line-height: 1.5;
  box-shadow: inset 0 1px 0 rgba(0, 0, 0, 0.03);
  transition:
    border-color 0.15s ease,
    box-shadow 0.15s ease,
    background-color 0.15s ease;
}

.login-input:hover {
  border-color: var(--login-subtle);
}

.login-input::placeholder {
  color: var(--login-subtle);
}

.login-input:focus {
  outline: none;
  border-color: var(--login-blue);
  box-shadow: 0 0 0 3px var(--login-focus);
}

html[data-theme="dark"] .login-input:-webkit-autofill,
html[data-theme="dark"] .login-input:-webkit-autofill:hover,
html[data-theme="dark"] .login-input:-webkit-autofill:focus {
  -webkit-text-fill-color: var(--login-text);
  -webkit-box-shadow: 0 0 0 1000px var(--login-surface) inset;
  transition: background-color 5000s ease-in-out 0s;
}

/* ==================== BUTTONS ==================== */

.login-button {
  width: 100%;
  min-height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  padding: 8px 16px;
  border: 1px solid rgba(31, 35, 40, 0.15);
  border-radius: 6px;
  background: var(--login-blue);
  color: #ffffff;
  font-family: inherit;
  font-size: 13px;
  font-weight: 600;
  line-height: 1.5;
  cursor: pointer;
  box-shadow: 0 1px 0 rgba(31, 35, 40, 0.1);
  transition:
    background-color 0.15s ease,
    box-shadow 0.15s ease,
    transform 0.05s ease;
}

.login-button:hover {
  background: var(--login-blue-hover);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.login-button:active {
  transform: translateY(1px);
  box-shadow: none;
}

.login-button:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px var(--login-focus);
}

/* ==================== DIVIDER / SUPPORT / REGISTER ==================== */

.login-divider {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 20px 0 15px;
  color: var(--login-subtle);
  font-size: 9px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.8px;
}

.login-divider::before,
.login-divider::after {
  content: "";
  flex: 1;
  height: 1px;
  background: var(--login-border-soft);
}

.support-box {
  padding-top: 12px;
  color: var(--login-muted);
  font-size: 11px;
  line-height: 1.55;
}

.support-box strong {
  color: var(--login-text);
  font-weight: 600;
}

.register-box {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  margin-top: 20px;
  color: var(--login-muted);
  font-size: 11px;
  line-height: 1.4;
}

.register-link {
  color: var(--login-blue);
  font-weight: 600;
  text-decoration: none;
}

.register-link:hover {
  color: var(--login-blue-hover);
  text-decoration: underline;
}

/* ==================== LOADING OVERLAY ==================== */

.loading-overlay {
  display: none;
  position: absolute;
  inset: 0;
  z-index: 1000;
  align-items: center;
  justify-content: center;
  background: color-mix(in srgb, var(--login-bg) 85%, transparent);
}

.loading-overlay.active {
  display: flex;
}

/* ==================== RESPONSIVE ==================== */

/* Tablet: stack panels */
@media (max-width: 991.98px) {
  .login-page {
    flex-direction: column;
    overflow-y: auto;
  }

  .system-panel {
    width: 100%;
    height: auto;
    flex-shrink: 0;
    padding: 28px 32px;
    border-right: 0;
    border-bottom: 1px solid var(--login-border-soft);
    overflow: visible; /* lets the Work Instructions dropdown escape the panel */
  }

  .system-content {
    max-width: 720px;
  }

  .login-panel {
    width: 100%;
    height: auto;
    flex: 1;
    padding: 30px 32px;
    overflow: visible;
  }

  .login-content {
    max-width: 460px;
  }
}

/* Small tablet */
@media (max-width: 767.98px) {
  .system-panel {
    padding: 24px;
  }

  .system-content {
    max-width: 100%;
  }

  .brand {
    margin-bottom: 56px;
  }

  .login-panel {
    padding: 26px 24px;
  }
}

/* Mobile: system panel hidden, login only */
@media (max-width: 575.98px) {
  .theme-toggle {
    top: 14px;
    right: 14px;
    width: 34px;
    height: 34px;
  }

  .system-panel {
    display: none;
  }

  .login-panel {
    min-height: 100dvh;
    padding: 20px 16px;
  }

  .login-content {
    max-width: 400px;
  }

  .login-input {
    height: 42px;
  }

  .login-button {
    min-height: 42px;
  }
}

/* Short desktop screens */
@media (max-height: 650px) and (min-width: 576px) {
  .system-panel {
    padding-block: 24px;
  }

  .login-panel {
    padding-block: 20px;
  }

  .login-header {
    margin-bottom: 12px;
  }

  .login-form-group {
    margin-bottom: 13px;
  }

  .login-form-group.password-group {
    margin-bottom: 15px;
  }

  .login-divider {
    margin: 15px 0 11px;
  }

  .register-box {
    margin-top: 9px;
  }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .login-page *,
  .login-page *::before,
  .login-page *::after {
    transition: none !important;
    animation: none !important;
  }
}

.system-action,
.system-action.primary {
  background: transparent;
}

.system-action:hover,
.system-action.primary:hover {
  background: transparent;
  opacity: 0.75;
  transform: translateY(-1px);
}
</style>
<title>Template</title>
<link rel="icon" type="image/png" href="<?php echo htmlspecialchars(($system ?? '') . 'dist/img/logo.png'); ?>">
<main class="login-page">
    <section class="system-panel">
        <div class="system-content">
            <div class="brand">
                <div class="brand-icon">
                   <img
    src="<?php echo htmlspecialchars(($system ?? '') . 'dist/img/FALP.png'); ?>"
    alt="FALP"
    onerror="this.style.display='none'">
                </div>
                <div>
                    <p class="brand-name">
                        Furukawa Automotive Systems Lima Philippines Inc.
                    </p>

                    <p class="brand-subtitle">
                        Internal Web System
                    </p>
                </div>
            </div>
        </div>
        <div class="system-identity">
            <div class="system-icon">
                <img
    src="<?php echo htmlspecialchars(($system ?? '') . 'dist/img/logo.png'); ?>" alt="System Logo" aria-hidden="true">
            </div>
            <h1 class="system-title">Nexus Template</h1>
            <div
                class="system-departments"
                aria-label="Departments using this system">
                <div class="system-department">
                    <span>
                        System Engineering
                    </span>
                </div>
            </div>

 <p class="system-description">
    <?php echo $system_description ?? 'System description goes here.'; ?>
</p>
       <div class="system-actions">
    <div class="nexus-dropdown">
        <a href="#" id="viewer_btn" class="system-action primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
            <span>Viewer</span>
        </a>

        <a href="#" class="system-action primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
            </svg>
            <span>Work Instructions</span>
        </a>
    </div>
</div>

        </div>
    </div>



        </div>
    </section>

    <!-- =====================================================
         LOGIN PANEL
    ====================================================== -->
    <section class="login-panel">
        <div class="login-content position-relative">

            <div class="login-header">
                <h1 class="login-title">Sign in to your account</h1>
                <p class="login-subtitle">Kindly enter your details</p>
            </div>

            <div class="login-card position-relative">

                <div class="loading-overlay" aria-hidden="true">
                    <div class="d-flex flex-row align-items-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="animate__animated animate__flash animate__infinite infinite">&nbsp;Loading...</span>
                    </div>
                </div>

                <div id="loginError" class="login-error d-none" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                  <span id="loginErrorText">Invalid Emp No or password.</span>
                </div>

                <form id="login_form" class="needs-validation" data-submit-callback="login_authenticate" novalidate>

           <div class="login-form-group">
    <label for="login_form_username" class="login-label">Emp No</label>
    <input type="text" class="login-input" id="login_form_username" name="username"
        placeholder="Enter your employee number" autocomplete="username" required>
    <div class="invalid-feedback">Required</div>
</div>

                    <div class="login-form-group password-group">
                        <label for="login_form_password" class="login-label">Password</label>
                        <input type="password" class="login-input" id="login_form_password" name="password"
                            placeholder="Enter your password" autocomplete="current-password" required>
                        <div class="invalid-feedback">Required</div>
                    </div>

                    <div class="d-flex gap-2">

                        <button type="submit" class="login-button">
                            <i class="fa-solid fa-right-to-bracket"></i>&emsp;Sign in
                        </button>
                    </div>
                </form>

                <div class="login-divider">System Access</div>

                <div class="support-box">
                    <strong>Need assistance?</strong><br>
                    If you're having trouble signing in, please contact your system administrator or System Engineering Section.
                </div>
            </div>


        </div>
    </section>

</main>
<script>
    const SYSTEM_URL = <?php echo json_encode(trim($system ?? '')); ?>;

    async function login_authenticate(form) {
        event.preventDefault();
        const formdata = new FormData(form);
        const loader = form.closest('.login-card').querySelector('.loading-overlay');
        const loginError = document.getElementById('loginError');
        const loginErrorText = document.getElementById('loginErrorText');

        $.ajax({
            url: SYSTEM_URL + '/api/common/login.php',
            type: 'POST',
            data: formdata,
            processData: false,
            contentType: false,
            cache: false,
            dataType: 'json',
            beforeSend: function() {
                loader.classList.add('active');
                loginError.classList.add('d-none');
            },
            success: function(response) {
                if (response.status == true && response.registered == false) {
                    Swal.fire({
                        title: "Not yet activated",
                        text: 'Confirm activation via registered email',
                        icon: "warning",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Confirm",
                        customClass: {
                            container: 'blur'
                        }
                    });
                    return 0;
                }
                if (response.status == false) {
                   loginErrorText.textContent = 'Invalid Emp No and/or Password';
                    loginError.classList.remove('d-none');
                    form.reset();
                    return 0;
                }
                window.location = SYSTEM_URL + '/pages/process_design';
            },
            error: function() {
                loginErrorText.textContent = 'Something went wrong. Please try again.';
                loginError.classList.remove('d-none');
            },
            complete: function() {
                loader.classList.remove('active');
            }
        });
    }

    // Viewer Page button: go to process_design as a viewer (no login required)
    document.getElementById('viewer_btn').addEventListener('click', function() {
        window.location = SYSTEM_URL + '/pages/process_design?mode=viewer';
    });
</script>