<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirect to login if not authenticated
    header('Location: /factory_supplies/index.php');
    exit;
}
$page_title = 'Requested Item';
$page_css   = '../../reusable/main.css';
include('../../reusable/header.php');
include('../../reusable/navbar.php');
include('../../reusable/topbar.php');
?>

<style>
/* ==================== USER DASHBOARD ==================== */

.user-dash { padding-top: 28px; padding-bottom: 40px; }

.user-dash .page-head { margin-bottom: 18px; }

/* ==================== TOOLBAR ==================== */

.user-dash .nexus-date { flex: 0 0 150px; }

.user-dash .toolbar-sep { color: var(--muted-light); font-size: 12px; }

.user-dash .btn-search { white-space: nowrap; }

/* ==================== LAZY SCROLL TABLE ==================== */

.user-dash .nexus-table-scroll { --table-offset: 330px; }

.user-dash .nexus-table-scroll:focus-visible { outline: 0; box-shadow: inset var(--focus-ring); }

.user-dash .nexus-state[hidden] { display: none; }

.user-dash .nexus-state.is-error { color: var(--danger-text); }

.user-dash .spinner-border { width: 14px; height: 14px; border-width: 2px; margin-right: 8px; vertical-align: -2px; }

/* Clickable rows */
.user-dash .nexus-table tbody tr[data-row] { cursor: pointer; }
.user-dash .nexus-table tbody tr[data-row]:hover { background: rgba(9, 105, 218, 0.06); }
.user-dash .nexus-table tbody tr[data-row]:focus-visible { outline: 0; box-shadow: inset var(--focus-ring); }

/* ==================== RESPONSIVE ==================== */

@media (max-width: 768px) {
  .user-dash .nexus-date { flex: 1 1 calc(50% - 14px); }
  .user-dash .toolbar-sep { display: none; }
  .user-dash .btn-search { flex: 1 1 100%; }
}

@media (prefers-reduced-motion: reduce) {
  .user-dash .spinner-border { animation-duration: 1.5s; }
}
</style>

<!-- User Dashboard Content -->
<main class="app-content is-fluid user-dash">

    <div class="page-head">
        <h1 class="page-title">Requested Item</h1>
    </div>

    <!-- Toolbar -->
    <form class="nexus-toolbar" id="searchForm" role="search" autocomplete="off">
        <div class="nexus-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" id="invoiceInput" class="form-control nexus-form-control"
                   placeholder="Search invoice no." aria-label="Search invoice number" autocomplete="off">
        </div>

        <input type="date" id="dateFrom" class="form-control nexus-form-control nexus-date" aria-label="Date from">
        <span class="toolbar-sep" aria-hidden="true">to</span>
        <input type="date" id="dateTo" class="form-control nexus-form-control nexus-date" aria-label="Date to">

        <button type="submit" class="btn btn-primary btn-search" id="searchBtn">
            <i class="bi bi-search" aria-hidden="true"></i> Search
        </button>

        <span class="nexus-toolbar-count" id="rowCount" aria-live="polite"></span>
    </form>

    <!-- Table -->
    <div class="nexus-card">
        <div class="nexus-table-scroll" id="tableScroll" tabindex="0" role="region" aria-label="Requests table">
            <table class="nexus-table">
                <thead><tr id="tableHead"></tr></thead>
                <tbody id="tableBody"></tbody>
            </table>

            <div class="nexus-state" id="tableState" hidden></div>
        </div>
    </div>

</main>

<!-- ==================== ITEM DETAILS MODAL ==================== -->

<div
    class="modal fade nexus-modal"
    id="itemModal"
    tabindex="-1"
    aria-labelledby="itemModalTitle"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <div>
                    <h2 class="modal-title" id="itemModalTitle">Requested Item</h2>
                    <p class="nexus-modal-subtitle">Enter the actual quantity to withdraw this item.</p>
                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close">
                </button>

            </div>

            <div class="modal-body">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="mWfKey">WF KEY</label>
                        <input type="text" class="form-control nexus-form-control" id="mWfKey" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="mInvoice">Invoice No.</label>
                        <input type="text" class="form-control nexus-form-control" id="mInvoice" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="mItemName">Item Name</label>
                        <input type="text" class="form-control nexus-form-control" id="mItemName" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="mQuantity">Quantity</label>
                        <input type="text" class="form-control nexus-form-control" id="mQuantity" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="mActualQty">Actual Quantity</label>
                        <input type="number" class="form-control nexus-form-control" id="mActualQty"
                               min="0" step="any" inputmode="decimal" placeholder="Enter actual quantity">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="mRequester">Requested User Name</label>
                        <input type="text" class="form-control nexus-form-control" id="mRequester" readonly>
                    </div>
                </div>

                <div class="nexus-form-error" id="mError" role="alert"></div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn nexus-btn-secondary"
                    data-bs-dismiss="modal">
                    Close
                </button>

                <button
                    type="button"
                    class="btn nexus-btn-primary"
                    id="btnWithdraw">
                    <i class="bi bi-box-arrow-up" aria-hidden="true"></i>
                    Withdraw
                </button>

            </div>

        </div>

    </div>

</div>

<script>
/* ==================== CONFIG ==================== */

const API_URL      = '../../api/user/fetch_wf.php';
const WITHDRAW_URL = '../../api/user/withdraw_wf.php';
const BATCH_SIZE = 15;
const DEFAULT_RANGE_DAYS = 30;

// Leave empty to show every column of V_WFSEARCH, or list the ones you want, in order
const VISIBLE_COLUMNS = ['C_WFKEY', 'C_BUMONNM', 'C_HINBANNM', 'C_TANICD', 'L_SURYO', 'L_TANKA', 'JPY_PRICE', 'C_SINSEISHANM'];
const COLUMN_LABELS = {
  C_WFKEY:       'WF KEY',
  C_BUMONNM:     'Requested Section name',
  C_HINBANNM:    'Item Name',
  C_TANICD:      'Unit code',
  L_SURYO:       'Quantity',
  L_TANKA:       'PHP',
  JPY_PRICE:     'JPY',
  C_SINSEISHANM: 'Requested User Name'
};

const tableScroll  = document.getElementById('tableScroll');
const tableHead    = document.getElementById('tableHead');
const tableBody    = document.getElementById('tableBody');
const tableState   = document.getElementById('tableState');
const rowCount     = document.getElementById('rowCount');
const searchForm   = document.getElementById('searchForm');
const invoiceInput = document.getElementById('invoiceInput');
const dateFrom     = document.getElementById('dateFrom');
const dateTo       = document.getElementById('dateTo');

const itemModalEl  = document.getElementById('itemModal');
const mWfKey       = document.getElementById('mWfKey');
const mInvoice     = document.getElementById('mInvoice');
const mItemName    = document.getElementById('mItemName');
const mQuantity    = document.getElementById('mQuantity');
const mActualQty = document.getElementById('mActualQty');
const mRequester   = document.getElementById('mRequester');
const mError       = document.getElementById('mError');
const btnWithdraw  = document.getElementById('btnWithdraw');

let currentRow = null; // row shown in the modal
let columns   = [];
let rowsData  = [];   // every loaded row, so a click can open its full details
let total     = 0;
let loaded    = 0;
let isLoading = false;
let requestId = 0;
let controller = null;

/* ==================== FETCH / API ==================== */

async function fetchBatch(reset) {
  if (!reset && (isLoading || loaded >= total)) return;

  if (reset) {
    if (controller) controller.abort();
    loaded = 0;
    total = 0;
    rowsData = [];
    tableBody.innerHTML = '';
    tableScroll.scrollTop = 0;
  }

  const myId = ++requestId;
  controller = new AbortController();
  isLoading = true;
  setState('<span class="spinner-border" role="status" aria-hidden="true"></span>Loading…');

  const params = new URLSearchParams({
    date_from: dateFrom.value,
    date_to: dateTo.value,
    c_seikyuno: invoiceInput.value.trim(),
    offset: loaded,
    limit: BATCH_SIZE
  });

  let ok = false;

  try {
    const res  = await fetch(`${API_URL}?${params}`, { signal: controller.signal, headers: { Accept: 'application/json' } });
    const json = await res.json();
    if (myId !== requestId) return; // a newer search replaced this one

    if (!json.success) throw new Error(json.message || 'Request failed.');

    if (reset) {
      columns = pickColumns(json.columns || []);
      renderHead();
    }
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

function renderHead() {
  tableHead.innerHTML = columns
    .map(c => `<th scope="col">${escapeHtml(COLUMN_LABELS[c] || c)}</th>`)
    .join('');
}

function renderRows(rows) {
  const start = rowsData.length;
  rowsData.push(...rows);

  const html = rows.map((r, i) =>
    `<tr data-row="${start + i}" tabindex="0" title="Click to view details">${columns.map(c => `<td>${escapeHtml(r[c] ?? '')}</td>`).join('')}</tr>`
  ).join('');
  tableBody.insertAdjacentHTML('beforeend', html);
}

function updateCount() {
  rowCount.textContent = `Showing ${loaded} of ${total}`;
}

function updateState() {
  if (!total) {
    setState('No requests found for this date range.');
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

/* ==================== MODAL ==================== */

function openItemModal(index) {
  const r = rowsData[index];
  if (!r) return;
  currentRow = r;

  mWfKey.value       = r.C_WFKEY ?? '';
  mInvoice.value     = r.C_SEIKYUNO ?? '';
  mItemName.value    = r.C_HINBANNM ?? '';
  mQuantity.value    = r.L_SURYO ?? '';
  mRequester.value   = r.C_SINSEISHANM ?? '';
  mActualQty.value = '';
  mError.textContent = '';

  // Actual Quantity can never be higher than Quantity
  const qty = parseFloat(String(r.L_SURYO ?? '').replace(/,/g, ''));
  if (Number.isFinite(qty)) {
    mActualQty.max = qty;
    mActualQty.placeholder = `Max ${qty}`;
  } else {
    mActualQty.removeAttribute('max');
    mActualQty.placeholder = 'Enter actual quantity';
  }

  bootstrap.Modal.getOrCreateInstance(itemModalEl).show();
}

itemModalEl.addEventListener('shown.bs.modal', () => mActualQty.focus());

// Block characters that make no sense for a quantity (negative sign, exponent)
mActualQty.addEventListener('keydown', e => {
  if (['-', '+', 'e', 'E'].includes(e.key)) e.preventDefault();
});

// Clamp to the allowed range while typing or pasting
mActualQty.addEventListener('input', () => {
  const max = parseFloat(mActualQty.max);
  const val = parseFloat(mActualQty.value);
  if (Number.isNaN(val)) return;
  if (val < 0) mActualQty.value = 0;
  else if (Number.isFinite(max) && val > max) mActualQty.value = max;
});

btnWithdraw.addEventListener('click', async () => {
  if (!currentRow) return;

  const qty    = parseFloat(String(currentRow.L_SURYO ?? '').replace(/,/g, ''));
  const actual = parseFloat(mActualQty.value);

  if (Number.isNaN(actual))  return showModalError('Enter the actual quantity.');
  if (actual <= 0)           return showModalError('Actual quantity must be greater than 0.');
  if (Number.isFinite(qty) && actual > qty) return showModalError('Actual quantity cannot exceed the requested quantity.');

  const fd = new FormData();
  fd.append('wfkey', currentRow.C_WFKEY ?? '');
  fd.append('requested_section_name', currentRow.C_BUMONNM ?? '');
  fd.append('item_name', currentRow.C_HINBANNM ?? '');
  fd.append('unit_code', currentRow.C_TANICD ?? '');
  fd.append('quantity', currentRow.L_SURYO ?? '');
  fd.append('actual_quantity', actual);
  fd.append('php', currentRow.L_TANKA ?? '');
  fd.append('jpy', currentRow.JPY_PRICE ?? '');
  fd.append('requested_user_name', currentRow.C_SINSEISHANM ?? '');

  mError.textContent = '';
  btnWithdraw.disabled = true;

  try {
    const res  = await fetch(WITHDRAW_URL, { method: 'POST', body: fd, headers: { Accept: 'application/json' } });
    const json = await res.json();

    if (!json.success) return showModalError(json.message || 'Could not save the withdrawal.');

    bootstrap.Modal.getOrCreateInstance(itemModalEl).hide();
    toast(json.message || 'Withdrawal saved.');
  } catch (err) {
    showModalError('Request failed. Please try again.');
  } finally {
    btnWithdraw.disabled = false;
  }
});

function showModalError(msg) {
  mError.textContent = msg;
}

function toast(msg) {
  if (window.Swal) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: msg, showConfirmButton: false, timer: 1800 });
  }
}

/* ==================== EVENTS ==================== */

tableScroll.addEventListener('scroll', () => {
  const nearBottom = tableScroll.scrollTop + tableScroll.clientHeight >= tableScroll.scrollHeight - 100;
  if (nearBottom) fetchBatch(false);
}, { passive: true });

// Open the modal when a row is clicked, or when Enter / Space is pressed on a focused row
tableBody.addEventListener('click', e => {
  const tr = e.target.closest('tr[data-row]');
  if (tr) openItemModal(Number(tr.dataset.row));
});

tableBody.addEventListener('keydown', e => {
  if (e.key !== 'Enter' && e.key !== ' ') return;
  const tr = e.target.closest('tr[data-row]');
  if (!tr) return;
  e.preventDefault();
  openItemModal(Number(tr.dataset.row));
});

// Search runs on button click or Enter (form submit)
searchForm.addEventListener('submit', e => {
  e.preventDefault();
  applyFilters();
});

/* ==================== SEARCH / FILTER ==================== */

function applyFilters() {
  if (!dateFrom.value || !dateTo.value || dateFrom.value > dateTo.value) {
    if (controller) controller.abort();
    requestId++;
    isLoading = false;
    rowsData = [];
    tableBody.innerHTML = '';
    rowCount.textContent = '';
    setState('Select a valid date range.', true);
    return;
  }
  fetchBatch(true);
}

// On tall screens the first batch may not overflow, so scrolling would never fire
function fillIfNotScrollable() {
  if (!isLoading && loaded < total && tableScroll.scrollHeight <= tableScroll.clientHeight) {
    fetchBatch(false);
  }
}

/* ==================== UTILS ==================== */

function pickColumns(all) {
  if (!VISIBLE_COLUMNS.length) return all;
  return VISIBLE_COLUMNS.filter(c => all.includes(c));
}

function toInputDate(d) {
  // Local date parts, so the value does not shift a day because of the timezone
  const pad = n => String(n).padStart(2, '0');
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
}

function escapeHtml(value) {
  return String(value).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

/* ==================== INIT ==================== */

document.addEventListener('DOMContentLoaded', () => {
  const today = new Date();
  const from  = new Date();
  from.setDate(today.getDate() - DEFAULT_RANGE_DAYS);

  dateFrom.value = toInputDate(from);
  dateTo.value   = toInputDate(today);

  applyFilters();
});
</script>

<?php include('../../reusable/footer.php'); ?>