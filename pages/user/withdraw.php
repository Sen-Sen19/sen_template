<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirect to login if not authenticated
    header('Location: /factory_supplies/index.php');
    exit;
}
$page_title = 'History';
$page_css   = '../../reusable/main.css';
include('../../reusable/header.php');
include('../../reusable/navbar.php');
include('../../reusable/topbar.php');
?>

<style>
/* ==================== HISTORY ==================== */

.user-dash { padding-top: 28px; padding-bottom: 40px; }

.user-dash .page-head { margin-bottom: 18px; }

.user-dash .nexus-search { flex: 1 1 320px; max-width: 420px; }

.user-dash .nexus-table-scroll { --table-offset: 330px; }

.user-dash .nexus-table-scroll:focus-visible { outline: 0; box-shadow: inset var(--focus-ring); }

.user-dash .nexus-state[hidden] { display: none; }

.user-dash .nexus-state.is-error { color: var(--danger-text); }

.user-dash .spinner-border { width: 14px; height: 14px; border-width: 2px; margin-right: 8px; vertical-align: -2px; }

/* Numbers line up on the right */
.user-dash .nexus-table .num { text-align: right; font-variant-numeric: tabular-nums; }

/* Not fully withdrawn: yellow row + a bar on the left (so it is not color only) */
.user-dash .nexus-table tbody tr.is-partial { background: var(--warning-bg); }
.user-dash .nexus-table tbody tr.is-partial:hover { background: #fff0a0; }
.user-dash .nexus-table tbody tr.is-partial td:first-child { box-shadow: inset 3px 0 0 var(--warning-text); }

@media (max-width: 768px) {
  .user-dash .nexus-search { max-width: none; }
}

@media (prefers-reduced-motion: reduce) {
  .user-dash .spinner-border { animation-duration: 1.5s; }
}
</style>

<main class="app-content is-fluid user-dash">

    <div class="page-head">
        <h1 class="page-title">Withdrawal History</h1>
        <p class="page-subtitle">Items withdrawn for <?php echo htmlspecialchars($_SESSION['account_name'] ?? 'your section'); ?>, newest first.</p>
    </div>

    <!-- Toolbar -->
    <form class="nexus-toolbar" id="searchForm" role="search" autocomplete="off">
        <div class="nexus-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" id="searchInput" class="form-control nexus-form-control"
                   placeholder="Search WF KEY or invoice no." aria-label="Search WF KEY or invoice number" autocomplete="off">
        </div>

        <span class="nexus-toolbar-count" id="rowCount" aria-live="polite"></span>
    </form>

    <!-- Table -->
    <div class="nexus-card">
        <div class="nexus-table-scroll" id="tableScroll" tabindex="0" role="region" aria-label="Withdrawal history table">
            <table class="nexus-table">
                <thead>
                    <tr>
                        <th scope="col">WF KEY</th>
                        <th scope="col">Invoice No.</th>
                        <th scope="col">Requested Section</th>
                        <th scope="col">Item Name</th>
                        <th scope="col">Unit</th>
                        <th scope="col" class="num">Quantity</th>
                        <th scope="col" class="num">Actual Qty</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="num">PHP</th>
                        <th scope="col" class="num">JPY</th>
                        <th scope="col" class="num">Total PHP</th>
                        <th scope="col" class="num">Total JPY</th>
                        <th scope="col">Requested User</th>
                        <th scope="col">Withdrawn By</th>
                        <th scope="col">Withdraw Date &amp; Time</th>
                    </tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>

            <div class="nexus-state" id="tableState" hidden></div>
        </div>
    </div>

</main>

<script>
/* ==================== CONFIG ==================== */

const API_URL    = '../../api/user/history_wf.php';
const BATCH_SIZE = 15;

const tableScroll = document.getElementById('tableScroll');
const tableBody   = document.getElementById('tableBody');
const tableState  = document.getElementById('tableState');
const rowCount    = document.getElementById('rowCount');
const searchForm  = document.getElementById('searchForm');
const searchInput = document.getElementById('searchInput');

let total      = 0;
let loaded     = 0;
let isLoading  = false;
let requestId  = 0;
let controller = null;
let lastTerm   = '';

/* ==================== FETCH / API ==================== */

async function fetchBatch(reset) {
  if (!reset && (isLoading || loaded >= total)) return;

  if (reset) {
    if (controller) controller.abort();
    loaded = 0;
    total = 0;
    tableBody.innerHTML = '';
    tableScroll.scrollTop = 0;
  }

  const myId = ++requestId;
  controller = new AbortController();
  isLoading = true;
  setState('<span class="spinner-border" role="status" aria-hidden="true"></span>Loading…');

  const params = new URLSearchParams({ q: lastTerm, offset: loaded, limit: BATCH_SIZE });
  let ok = false;

  try {
    const res  = await fetch(`${API_URL}?${params}`, { signal: controller.signal, headers: { Accept: 'application/json' } });
    const json = await res.json();
    if (myId !== requestId) return; // a newer search replaced this one

    if (!json.success) throw new Error(json.message || 'Request failed.');

    total = json.total;
    renderRows(json.data || []);
    loaded += (json.data || []).length;
    updateCount();
    updateState();
    ok = true;
  } catch (err) {
    if (err.name === 'AbortError') return;
    setState(escapeHtml(err.message || 'Could not load data.'), true);
  } finally {
    if (myId === requestId) isLoading = false;
  }

  if (ok) fillIfNotScrollable();
}

/* ==================== RENDER ==================== */

function renderRows(rows) {
  tableBody.insertAdjacentHTML('beforeend', rows.map(rowHtml).join(''));
}

function rowHtml(r) {
  const partial = !!r.is_partial;

  const status = partial
    ? `<span class="nexus-badge yellow" title="${fmtQty(r.total_withdrawn)} of ${fmtQty(r.quantity)} withdrawn so far">Partial · ${fmtQty(r.total_withdrawn)}/${fmtQty(r.quantity)}</span>`
    : '<span class="nexus-badge green">Complete</span>';

  return `<tr${partial ? ' class="is-partial"' : ''}>
    <td class="cell-strong">${escapeHtml(r.wfkey)}</td>
    <td>${escapeHtml(r.invoice_no || '—')}</td>
    <td>${escapeHtml(r.requested_section_name || '—')}</td>
    <td>${escapeHtml(r.item_name)}</td>
    <td>${escapeHtml(r.unit_code || '—')}</td>
    <td class="num">${fmtQty(r.quantity)}</td>
    <td class="num cell-strong">${fmtQty(r.actual_quantity)}</td>
    <td>${status}</td>
    <td class="num">${fmtMoney(r.php)}</td>
    <td class="num">${fmtMoney(r.jpy)}</td>
    <td class="num">${fmtMoney(r.total_php)}</td>
    <td class="num">${fmtMoney(r.total_jpy)}</td>
    <td>${escapeHtml(r.requested_user_name || '—')}</td>
    <td>${escapeHtml(r.withdraw_by || '—')}</td>
    <td>${escapeHtml(r.withdraw_datetime || '—')}</td>
  </tr>`;
}

function updateCount() {
  rowCount.textContent = `Showing ${loaded} of ${total}`;
}

function updateState() {
  if (!total) {
    setState(lastTerm ? `No withdrawals found for “${escapeHtml(lastTerm)}”.` : 'No withdrawals yet.');
  } else if (loaded >= total) {
    setState('You have reached the end of the list.');
  } else {
    tableState.hidden = true;
  }
}

function setState(html, isError = false) {
  tableState.innerHTML = html;
  tableState.classList.toggle('is-error', isError);
  tableState.hidden = false;
}

/* ==================== EVENTS ==================== */

tableScroll.addEventListener('scroll', () => {
  const nearBottom = tableScroll.scrollTop + tableScroll.clientHeight >= tableScroll.scrollHeight - 100;
  if (nearBottom) fetchBatch(false);
}, { passive: true });

// Search while typing (debounced), or right away on Enter
let timer;
searchInput.addEventListener('input', () => {
  clearTimeout(timer);
  timer = setTimeout(applyFilters, 300);
});

searchForm.addEventListener('submit', e => {
  e.preventDefault();
  clearTimeout(timer);
  applyFilters();
});

/* ==================== SEARCH / FILTER ==================== */

function applyFilters() {
  const term = searchInput.value.trim();
  if (term === lastTerm && loaded > 0) return; // nothing changed
  lastTerm = term;
  fetchBatch(true);
}

// On tall screens the first batch may not overflow, so scrolling would never fire
function fillIfNotScrollable() {
  if (!isLoading && loaded < total && tableScroll.scrollHeight <= tableScroll.clientHeight) {
    fetchBatch(false);
  }
}

/* ==================== UTILS ==================== */

const qtyFmt   = new Intl.NumberFormat('en-US', { maximumFractionDigits: 3 });
const moneyFmt = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const fmtQty   = v => (v === null || v === undefined || v === '') ? '—' : qtyFmt.format(Number(v));
const fmtMoney = v => (v === null || v === undefined || v === '') ? '—' : moneyFmt.format(Number(v));

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

/* ==================== INIT ==================== */

document.addEventListener('DOMContentLoaded', () => fetchBatch(true));
</script>

<?php include('../../reusable/footer.php'); ?>