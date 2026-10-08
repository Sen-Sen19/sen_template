<style>
/* ==================== TOPBAR TOKENS ==================== */

:root {
  --template-topbar-height: 64px;
  --template-topbar-bg: #ffffff;
  --template-topbar-border: #d0d7de;
  --template-topbar-text: #1f2328;
  --template-topbar-text-secondary: #656d76;
  --template-topbar-text-muted: #8c959f;
  --template-topbar-hover: #f6f8fa;
  --template-topbar-active: #f1f6ff;
  --template-topbar-active-text: #0969da;
  --template-topbar-primary: #0969da;
  --template-topbar-radius: 8px;
  --template-topbar-dropdown-radius: 10px;
  --template-topbar-shadow: 0 8px 24px rgba(140, 149, 159, 0.2);
  --template-topbar-font: "Mona Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

/* ==================== TOPBAR ==================== */

.app-topbar {
  position: sticky;
  top: 0;
  /* above the mobile overlay (1035) so the slide-in panel stays clickable */
  z-index: 1045;
  width: 100%;
  height: var(--template-topbar-height);
  background: var(--template-topbar-bg);
  border-bottom: 1px solid var(--template-topbar-border);
  font-family: var(--template-topbar-font);
}

.topbar-inner {
  display: flex;
  align-items: center;
  gap: 18px;
  width: 100%;
  height: 100%;
  padding: 0 20px;
}

/* ==================== BRAND ==================== */

.topbar-brand {
  flex: 0 0 auto;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  color: var(--template-topbar-text);
  text-decoration: none;
}

.topbar-brand:hover {
  color: var(--template-topbar-text);
}

.brand-mark {
  width: 34px;
  height: 34px;
  flex: 0 0 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ffffff !important;
}

.brand-mark img {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: contain;
  filter: none !important;
  opacity: 1 !important;
  mix-blend-mode: normal !important;
  background: transparent !important;
}

.brand-text {
  display: flex;
  flex-direction: column;
  line-height: 1.15;
}

.brand-name {
  font-size: 14px;
  font-weight: 650;
  letter-spacing: -0.01em;
  white-space: nowrap;
}

.brand-version {
  margin-top: 3px;
  color: var(--template-topbar-text-muted);
  font-size: 10px;
  white-space: nowrap;
}

/* ==================== NAVIGATION ==================== */

.topbar-nav {
  flex: 1 1 auto;
  min-width: 0;
  display: flex;
  align-items: center;
  gap: 2px;
}

.top-nav-item {
  height: 36px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  padding: 0 12px;
  border-radius: var(--template-topbar-radius);
  color: var(--template-topbar-text-secondary);
  font-size: 12px;
  font-weight: 500;
  line-height: 1;
  text-decoration: none;
  white-space: nowrap;
  transition:
    background 0.15s ease,
    color 0.15s ease;
}

.top-nav-item i {
  font-size: 14px;
}

.top-nav-item:hover {
  background: var(--template-topbar-hover);
  color: var(--template-topbar-text);
}

.top-nav-item.active {
  background: var(--template-topbar-active);
  color: var(--template-topbar-active-text);
  font-weight: 600;
}

/* ==================== ACTIONS / USER ==================== */

.topbar-actions {
  flex: 0 0 auto;
  display: flex;
  align-items: center;
  gap: 4px;
}

.topbar-user {
  position: relative;
}

.app-topbar .topbar-user-toggle {
  appearance: none !important;
  -webkit-appearance: none !important;
  height: 40px !important;
  min-height: 40px !important;
  display: flex !important;
  align-items: center !important;
  gap: 8px !important;
  padding: 3px 8px 3px 4px !important;
  margin: 0 !important;
  border: 0 !important;
  border-radius: var(--template-topbar-radius) !important;
  background: transparent !important;
  background-image: none !important;
  color: var(--template-topbar-text) !important;
  font-family: inherit !important;
  font-size: inherit !important;
  font-weight: inherit !important;
  line-height: normal !important;
  text-align: left !important;
  box-shadow: none !important;
  outline: none;
  cursor: pointer;
}

.app-topbar .topbar-user-toggle:hover,
.app-topbar .topbar-user-toggle:focus,
.app-topbar .topbar-user-toggle:active,
.app-topbar .topbar-user-toggle.show {
  background: var(--template-topbar-hover) !important;
  border: 0 !important;
  box-shadow: none !important;
  color: var(--template-topbar-text) !important;
  outline: none !important;
}

.app-topbar .topbar-user-toggle:focus-visible {
  box-shadow: 0 0 0 3px #e6f1fb !important;
}

.app-topbar .user-avatar {
  width: 30px;
  height: 30px;
  flex: 0 0 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: var(--template-topbar-primary);
  color: #ffffff;
  font-size: 11px;
  font-weight: 650;
}

.app-topbar .user-details {
  min-width: 0;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  line-height: 1.15;
}

.user-name {
  max-width: 90px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: var(--template-topbar-text);
  font-size: 11px;
  font-weight: 600;
}

.user-role {
  margin-top: 2px;
  color: var(--template-topbar-text-muted);
  font-size: 9px;
}

.user-chevron {
  margin-left: 2px;
  color: var(--template-topbar-text-muted);
  font-size: 9px;
}

/* ==================== USER DROPDOWN ==================== */

.template-user-dropdown {
  min-width: 210px;
  margin-top: 8px !important;
  padding: 7px;
  border: 1px solid var(--template-topbar-border);
  border-radius: var(--template-topbar-dropdown-radius);
  box-shadow: var(--template-topbar-shadow);
}

.template-user-dropdown .dropdown-item {
  min-height: 35px;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 7px 10px;
  border-radius: 6px;
  font-size: 12px;
}

.template-user-dropdown .dropdown-item i {
  width: 16px;
  text-align: center;
}

.template-user-dropdown .dropdown-divider {
  margin: 6px 4px;
}

/* ==================== MOBILE ==================== */

.topbar-mobile-toggle,
.topbar-mobile-panel {
  display: none;
}

/* ==================== RESPONSIVE ==================== */

@media (max-width: 991.98px) {
  .topbar-inner {
    padding: 0 14px;
  }

  .brand-version,
  .topbar-nav,
  .topbar-user {
    display: none;
  }

  .topbar-actions {
    margin-left: auto;
  }

  .topbar-mobile-toggle {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: var(--template-topbar-radius);
    background: transparent;
    color: var(--template-topbar-text);
    font-size: 18px;
  }

  .topbar-mobile-toggle:hover {
    background: var(--template-topbar-hover);
  }

  .topbar-mobile-panel {
    position: fixed;
    top: var(--template-topbar-height);
    left: 0;
    z-index: 1040;
    width: min(360px, 90vw);
    height: calc(100vh - var(--template-topbar-height));
    height: calc(100dvh - var(--template-topbar-height));
    display: block;
    overflow-y: auto;
    background: #ffffff;
    border-right: 1px solid var(--template-topbar-border);
    box-shadow: 10px 0 30px rgba(31, 35, 40, 0.12);
    transform: translateX(-100%);
    visibility: hidden;
    transition:
      transform 0.22s ease,
      visibility 0.22s ease;
  }

  .topbar-mobile-panel.is-open {
    transform: translateX(0);
    visibility: visible;
  }

  .topbar-mobile-overlay {
    position: fixed;
    inset: 0;
    z-index: 1035;
    background: rgba(31, 35, 40, 0.25);
    opacity: 0;
    visibility: hidden;
    transition:
      opacity 0.2s ease,
      visibility 0.2s ease;
  }

  .topbar-mobile-overlay.is-visible {
    opacity: 1;
    visibility: visible;
  }

  .mobile-nav-inner {
    padding: 14px;
  }

  .mobile-nav-item {
    min-height: 42px;
    display: flex;
    align-items: center;
    gap: 11px;
    margin-bottom: 2px;
    padding: 8px 10px;
    border-radius: 7px;
    color: var(--template-topbar-text-secondary);
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
  }

  .mobile-nav-item i {
    width: 18px;
    font-size: 15px;
    text-align: center;
  }

  .mobile-nav-item:hover {
    background: var(--template-topbar-hover);
    color: var(--template-topbar-text);
  }

  .mobile-nav-item.active {
    background: var(--template-topbar-active);
    color: var(--template-topbar-active-text);
    font-weight: 600;
  }

  .mobile-account-card {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 22px;
    padding: 12px;
    border: 1px solid #eaeef2;
    border-radius: 9px;
    background: #f6f8fa;
  }

  .mobile-account-card .user-details {
    flex: 1;
  }
}

@media (max-width: 420px) {
  .brand-name {
    font-size: 13px;
  }

  .brand-mark {
    width: 32px;
    height: 32px;
    flex-basis: 32px;
  }

  .topbar-mobile-panel {
    width: 100%;
  }
}

@media (prefers-reduced-motion: reduce) {
  .top-nav-item,
  .topbar-mobile-panel,
  .topbar-mobile-overlay {
    transition: none;
  }
}
</style>
<?php

if (!function_exists('isActive')) {
    function isActive($page)
    {
        return basename($_SERVER['PHP_SELF']) === $page ? 'active' : '';
    }
}

// If $system isn't set, fall back to a relative path (a bare "/dist/..." breaks the logo in subfolders)
$logo_url = htmlspecialchars((isset($system) && $system !== '' ? rtrim($system, '/') . '/' : '') . 'dist/img/logo.png');

?>

<!-- ==================== TOPBAR ==================== -->

<header class="app-topbar" id="appTopbar">

    <div class="topbar-inner">

        <!-- Brand -->
        <a href="index.php" class="topbar-brand">
            <div class="brand-mark">
                <img src="<?= $logo_url ?>" alt="Template">
            </div>

            <div class="brand-text">
                <span class="brand-name">Template</span>
                <span class="brand-version">Internal Web System</span>
            </div>
        </a>

        <!-- Desktop navigation -->
        <nav class="topbar-nav" aria-label="Primary navigation">

            <a href="index.php" class="top-nav-item <?= isActive('index.php') ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>

            <a href="account.php" class="top-nav-item <?= isActive('account.php') ?>">
                <i class="bi bi-people"></i>
                <span>Account Management</span>
            </a>

        </nav>

        <!-- Actions -->
        <div class="topbar-actions">

            <div class="dropdown topbar-user" id="topbarUser" style="visibility:hidden">

                <button
                    type="button"
                    class="topbar-user-toggle"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">

                    <span class="user-avatar">N</span>

                    <span class="user-details">
                        <span class="user-name">newt_</span>
                        <span class="user-role">Employee</span>
                    </span>

                    <i class="bi bi-chevron-down user-chevron"></i>

                </button>

                <ul class="dropdown-menu dropdown-menu-end template-user-dropdown">

                    <li>
                        <a class="dropdown-item" href="account.php">
                            <i class="bi bi-people"></i>
                            <span>Account Management</span>
                        </a>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item text-danger" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Sign Out</span>
                        </a>
                    </li>

                </ul>

            </div>

            <button
                type="button"
                class="topbar-mobile-toggle"
                id="topbarMobileBtn"
                aria-label="Open navigation"
                aria-expanded="false">
                <i class="bi bi-list"></i>
            </button>

        </div>

    </div>

    <!-- Mobile navigation -->
    <div class="topbar-mobile-panel" id="topbarMobilePanel">

        <div class="mobile-nav-inner">

            <a href="index.php" class="mobile-nav-item <?= isActive('index.php') ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>

            <a href="account.php" class="mobile-nav-item <?= isActive('account.php') ?>">
                <i class="bi bi-people"></i>
                <span>Account Management</span>
            </a>

            <div class="mobile-account-card">

                <div class="user-avatar">N</div>

                <div class="user-details">
                    <span class="user-name">newt_</span>
                    <span class="user-role">Employee</span>
                </div>

                <a href="logout.php" class="text-danger" aria-label="Sign out">
                    <i class="bi bi-box-arrow-right"></i>
                </a>

            </div>

        </div>

    </div>

</header>

<div class="topbar-mobile-overlay" id="topbarMobileOverlay"></div>

<noscript><style>.topbar-user { visibility: visible !important; }</style></noscript>

<script>
    /* ==================== REVEAL USER MENU (prevents unstyled flash) ==================== */

    (function() {
        function reveal() {
            var el = document.getElementById('topbarUser');
            if (el) el.style.visibility = '';
        }
        if (document.readyState === 'complete') reveal();
        else window.addEventListener('load', reveal);
        setTimeout(reveal, 1500); // safety fallback
    })();

    /* ==================== MOBILE NAV ==================== */

    document.addEventListener('DOMContentLoaded', function() {
        const button = document.getElementById('topbarMobileBtn');
        const panel = document.getElementById('topbarMobilePanel');
        const overlay = document.getElementById('topbarMobileOverlay');

        if (!button || !panel || !overlay) return;

        function setOpen(open) {
            panel.classList.toggle('is-open', open);
            overlay.classList.toggle('is-visible', open);
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
            button.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
            button.querySelector('i').className = open ? 'bi bi-x-lg' : 'bi bi-list';
            document.body.style.overflow = open ? 'hidden' : '';
        }

        button.addEventListener('click', function() {
            setOpen(!panel.classList.contains('is-open'));
        });

        overlay.addEventListener('click', function() {
            setOpen(false);
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') setOpen(false);
        });

        panel.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                setOpen(false);
            });
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) setOpen(false);
        });
    });
</script>