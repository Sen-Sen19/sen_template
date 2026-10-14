<?php
/**
 * DEV QUICK LOGIN  (delete this file when development is done)
 * The login page only includes the UI below. Accounts are loaded lazily via
 * dev_login.php?accounts=1, so the DB can never block or break the login page.
 */

/* ==================== ACCOUNTS ENDPOINT ==================== */

if (isset($_GET['accounts'])) {
    header('Content-Type: application/json; charset=utf-8');

    ob_start();
    require_once __DIR__ . '/api/connection/template_conn.php'; // defines $conn_sen
    ob_end_clean();

    $accounts = [];
    $stmt = sqlsrv_query(
        $conn_sen,
        "SELECT id_no, full_name, role, account_name, [password]
         FROM dbo.accounts
         WHERE id_no IS NOT NULL AND [password] IS NOT NULL
         ORDER BY CASE WHEN LOWER(role) = 'admin' THEN 0 ELSE 1 END, role, account_name, full_name"
    );

    if ($stmt === false) {
        echo json_encode(['status' => false, 'message' => 'Query failed', 'errors' => sqlsrv_errors()]);
        exit;
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $accounts[] = [
            'id_no'        => trim((string) $row['id_no']),
            'full_name'    => trim((string) $row['full_name']),
            'role'         => trim((string) $row['role']),
            'account_name' => trim((string) $row['account_name']),
            'password'     => (string) $row['password'],
        ];
    }

    echo json_encode(['status' => true, 'data' => $accounts], JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
?>
<style>
/* ==================== DEV LAUNCHER ==================== */

.dev-login-toggle {
  position: fixed;
  left: 20px;
  bottom: 20px;
  z-index: 2000;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  height: 36px;
  padding: 0 14px;
  border: 1px dashed var(--login-border);
  border-radius: 999px;
  background: var(--login-surface);
  color: var(--login-muted);
  font-family: inherit;
  font-size: 12px;
  font-weight: 600;
  line-height: 1;
  cursor: pointer;
  transition: border-color 0.15s ease, color 0.15s ease, transform 0.15s ease;
}

.dev-login-toggle:hover {
  border-color: var(--login-blue);
  color: var(--login-blue);
  transform: translateY(-1px);
}

.dev-login-toggle:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px var(--login-focus);
}

/* ==================== DEV PANEL ==================== */

.dev-login-panel {
  position: fixed;
  left: 20px;
  bottom: 66px;
  z-index: 2000;
  display: flex;
  flex-direction: column;
  width: min(360px, calc(100vw - 40px));
  max-height: 70vh;
  max-height: min(560px, calc(100dvh - 100px));
  overflow: hidden;
  border: 1px solid var(--login-border);
  border-radius: 12px;
  background: var(--login-surface);
  color: var(--login-text);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.14);
  opacity: 0;
  visibility: hidden;
  transform: translateY(8px);
  transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s;
}

.dev-login-panel.open {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.dev-login-head {
  flex-shrink: 0;
  padding: 12px 12px 10px;
  border-bottom: 1px solid var(--login-border-soft);
}

.dev-login-title {
  margin: 0 0 8px;
  font-size: 12px;
  font-weight: 600;
}

.dev-login-title small {
  margin-left: 6px;
  color: var(--login-subtle);
  font-weight: 500;
}

.dev-login-search {
  width: 100%;
  height: 34px;
  padding: 0 10px;
  border: 1px solid var(--login-border);
  border-radius: 6px;
  background: var(--login-surface);
  color: var(--login-text);
  font-family: inherit;
  font-size: 13px;
}

.dev-login-search:focus {
  outline: none;
  border-color: var(--login-blue);
  box-shadow: 0 0 0 3px var(--login-focus);
}

/* ==================== DEV LIST ==================== */

.dev-login-list {
  flex: 1 1 auto;
  min-height: 0;
  margin: 0;
  padding: 6px;
  overflow-y: auto;
  overscroll-behavior: contain;
  scrollbar-width: thin;
  list-style: none;
}

.dev-login-item {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 8px 10px;
  border: 0;
  border-radius: 8px;
  background: transparent;
  color: inherit;
  font-family: inherit;
  text-align: left;
  cursor: pointer;
  transition: background-color 0.12s ease;
}

.dev-login-item:hover,
.dev-login-item:focus-visible {
  outline: none;
  background: var(--login-surface-soft);
}

.dev-login-avatar {
  flex-shrink: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: var(--login-focus);
  color: var(--login-blue);
  font-size: 12px;
  font-weight: 600;
}

.dev-login-info {
  min-width: 0;
  flex: 1;
}

.dev-login-name {
  display: block;
  overflow: hidden;
  font-size: 13px;
  font-weight: 600;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.dev-login-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 3px;
  min-width: 0;
  color: var(--login-muted);
  font-size: 11px;
}

.dev-login-id {
  flex-shrink: 0;
  font-variant-numeric: tabular-nums;
  color: var(--login-subtle);
}

.dev-login-empty {
  padding: 18px 12px;
  color: var(--login-muted);
  font-size: 12px;
  text-align: center;
}

/* ==================== BADGES ==================== */

.dev-login-account {
  min-width: 0;
  overflow: hidden;
  padding: 2px 7px;
  border-radius: 5px;
  background: var(--login-surface-soft);
  border: 1px solid var(--login-border-soft);
  color: var(--login-text);
  font-weight: 500;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.dev-login-role {
  flex-shrink: 0;
  padding: 3px 8px;
  border-radius: 999px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.3px;
  line-height: 1;
  text-transform: uppercase;
  background: rgba(140, 149, 159, 0.18);
  color: var(--login-muted);
}

.dev-login-role.is-admin { background: rgba(9, 105, 218, 0.14);  color: var(--login-blue); }
.dev-login-role.is-mp    { background: rgba(26, 127, 55, 0.16);  color: #1a7f37; }
.dev-login-role.is-user  { background: rgba(140, 149, 159, 0.18); color: var(--login-muted); }

html[data-theme="dark"] .dev-login-role.is-mp { color: #3fb950; }

.dev-login-avatar.is-admin { background: rgba(9, 105, 218, 0.14); color: var(--login-blue); }
.dev-login-avatar.is-mp    { background: rgba(26, 127, 55, 0.16); color: #1a7f37; }
.dev-login-avatar.is-user  { background: rgba(140, 149, 159, 0.18); color: var(--login-muted); }

html[data-theme="dark"] .dev-login-avatar.is-mp { color: #3fb950; }

/* ==================== RESPONSIVE ==================== */

@media (max-width: 575.98px) {
  .dev-login-toggle { left: 14px; bottom: 14px; }
  .dev-login-panel  { left: 14px; bottom: 58px; }
  .dev-login-search { font-size: 16px; } /* stops iOS zoom on focus */
}

@media (prefers-reduced-motion: reduce) {
  .dev-login-toggle,
  .dev-login-panel,
  .dev-login-item { transition: none; }
}
</style>

<button type="button" class="dev-login-toggle" id="devLoginToggle" aria-expanded="false" aria-controls="devLoginPanel">
    <i class="fa-solid fa-user-gear" aria-hidden="true"></i> DEV Login
</button>

<div class="dev-login-panel" id="devLoginPanel" role="dialog" aria-label="Developer quick login">
    <div class="dev-login-head">
        <p class="dev-login-title">Quick login<small>dev only</small><small id="devLoginCount"></small></p>
        <input type="search" class="dev-login-search" id="devLoginSearch" placeholder="Search name, role, account, ID" autocomplete="off">
    </div>
    <ul class="dev-login-list" id="devLoginList"></ul>
</div>

<script>
/* ==================== CONFIG ==================== */

const DEV_ENDPOINT = <?php echo json_encode(basename(__FILE__) . '?accounts=1'); ?>;

const devToggle = document.getElementById('devLoginToggle');
const devPanel  = document.getElementById('devLoginPanel');
const devSearch = document.getElementById('devLoginSearch');
const devList   = document.getElementById('devLoginList');

let devAccounts = [];
let devLoaded   = false;

/* ==================== FETCH / API ==================== */

async function devLoadAccounts() {
    devMessage('Loading accounts...');

    try {
        const res  = await fetch(DEV_ENDPOINT, { cache: 'no-store', credentials: 'same-origin' });
        const text = await res.text();

        let json;
        try {
            json = JSON.parse(text);
        } catch {
            devMessage('Bad response from dev_login.php: ' + text.slice(0, 200));
            return;
        }

        if (!json.status) {
            devMessage((json.message || 'Failed to load accounts') + ' ' + JSON.stringify(json.errors || ''));
            return;
        }

        devAccounts = json.data;
        devLoaded = true;
        document.getElementById('devLoginCount').textContent = `\u00b7 ${devAccounts.length} accounts`;
        devRender(devAccounts);
    } catch (err) {
        devMessage('Request failed: ' + err.message);
    }
}

/* ==================== RENDER ==================== */

function devMessage(text) {
    devList.replaceChildren();
    const li = document.createElement('li');
    li.className = 'dev-login-empty';
    li.textContent = text;
    devList.appendChild(li);
}

function devRoleClass(role) {
    const r = (role || '').toLowerCase();
    if (r === 'admin') return 'is-admin';
    if (r === 'mp') return 'is-mp';
    return 'is-user';
}

function devRender(items) {
    if (!items.length) {
        devMessage('No matching accounts.');
        return;
    }

    const frag = document.createDocumentFragment();

    items.forEach((acc) => {
        const roleClass = devRoleClass(acc.role);

        const li  = document.createElement('li');
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'dev-login-item';
        btn.dataset.id = acc.id_no;

        const avatar = document.createElement('span');
        avatar.className = `dev-login-avatar ${roleClass}`;
        avatar.textContent = (acc.full_name[0] || '?').toUpperCase();

        const info = document.createElement('span');
        info.className = 'dev-login-info';

        const name = document.createElement('span');
        name.className = 'dev-login-name';
        name.textContent = acc.full_name;

        const meta = document.createElement('span');
        meta.className = 'dev-login-meta';

        // Admins have no account name, so the chip is only rendered when there is one
        if (acc.account_name) {
            const account = document.createElement('span');
            account.className = 'dev-login-account';
            account.textContent = acc.account_name;
            meta.appendChild(account);
        }

        const id = document.createElement('span');
        id.className = 'dev-login-id';
        id.textContent = acc.id_no;
        meta.appendChild(id);

        info.append(name, meta);

        const role = document.createElement('span');
        role.className = `dev-login-role ${roleClass}`;
        role.textContent = acc.role || 'No role';

        btn.append(avatar, info, role);
        li.appendChild(btn);
        frag.appendChild(li);
    });

    devList.replaceChildren(frag);
}

/* ==================== EVENTS ==================== */

function devSetOpen(open) {
    devPanel.classList.toggle('open', open);
    devToggle.setAttribute('aria-expanded', String(open));
    if (open) {
        devSearch.focus();
        if (!devLoaded) devLoadAccounts();
    }
}

devToggle.addEventListener('click', () => devSetOpen(!devPanel.classList.contains('open')));

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') devSetOpen(false);
});

// Close when clicking anywhere outside the panel
document.addEventListener('click', (e) => {
    if (!devPanel.contains(e.target) && !devToggle.contains(e.target)) devSetOpen(false);
});

// Pick an account: fill the form and submit it through the page's own handler
devList.addEventListener('click', (e) => {
    const btn = e.target.closest('.dev-login-item');
    if (!btn) return;

    const acc  = devAccounts.find((a) => a.id_no === btn.dataset.id);
    const form = document.getElementById('login_form');
    if (!acc || !form) return;

    form.querySelector('#login_form_username').value = acc.id_no;
    form.querySelector('#login_form_password').value = acc.password;

    devSetOpen(false);
    form.requestSubmit(); // fires the normal submit event -> login_authenticate()
});

/* ==================== SEARCH / FILTER ==================== */

devSearch.addEventListener('input', () => {
    if (!devLoaded) return;
    const q = devSearch.value.trim().toLowerCase();
    devRender(
        q
            ? devAccounts.filter((a) => `${a.full_name} ${a.role} ${a.account_name} ${a.id_no}`.toLowerCase().includes(q))
            : devAccounts
    );
});
</script>