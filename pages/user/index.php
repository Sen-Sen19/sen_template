<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirect to login if not authenticated
    header('Location: /factory_supplies/index.php');
    exit;
}
$page_title = 'User Dashboard';
$page_css   = '../../reusable/main.css';
include('../../reusable/header.php');
include('../../reusable/navbar.php');
include('../../reusable/topbar.php');
?>

<style>
/* ==================== USER DASHBOARD ==================== */

.user-dash { padding-top: 28px; padding-bottom: 40px; }

.user-dash .page-head { margin-bottom: 18px; }

.user-dash .page-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 6px 16px;
  margin-top: 6px;
  color: var(--muted);
  font-size: 12px;
}

/* ==================== LAZY SCROLL TABLE ==================== */

.user-dash .nexus-table-scroll { --table-offset: 330px; }

.user-dash .nexus-table-scroll:focus-visible { outline: 0; box-shadow: inset var(--focus-ring); }

.user-dash .nexus-state[hidden] { display: none; }

.user-dash .spinner-border { width: 14px; height: 14px; border-width: 2px; margin-right: 8px; vertical-align: -2px; }

@media (prefers-reduced-motion: reduce) {
  .user-dash .spinner-border { animation-duration: 1.5s; }
}
</style>

<!-- User Dashboard Content -->
<main class="app-content is-fluid user-dash">

    <div class="page-head">
        <h1 class="page-title">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>!</h1>
        <p class="page-subtitle">Here is a list of your recent item requests.</p>
        <div class="page-meta">
            <span>Employee ID: <?php echo htmlspecialchars($_SESSION['id_no'] ?? ''); ?></span>
            <span>Role: <?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?></span>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="nexus-toolbar">
        <div class="nexus-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" id="searchInput" class="form-control nexus-form-control"
                   placeholder="Search request no. or item" aria-label="Search requests" autocomplete="off">
        </div>

        <select id="statusFilter" class="form-select nexus-form-control" aria-label="Filter by status">
            <option value="">All statuses</option>
            <option>Pending</option>
            <option>Approved</option>
            <option>Released</option>
            <option>Rejected</option>
        </select>

        <span class="nexus-toolbar-count" id="rowCount" aria-live="polite"></span>
    </div>

    <!-- Table -->
    <div class="nexus-card">
        <div class="nexus-table-scroll" id="tableScroll" tabindex="0" role="region" aria-label="Item requests table">
            <table class="nexus-table">
                <thead>
                    <tr>
                        <th scope="col">Request No.</th>
                        <th scope="col">Item</th>
                        <th scope="col">Qty</th>
                        <th scope="col">Date Requested</th>
                        <th scope="col">Status</th>
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

const BATCH_SIZE = 15;
const TOTAL_ROWS = 100;
const LOAD_DELAY = 250; // ms, simulates a server fetch for the spinner
const BADGE_COLORS = { Pending: 'yellow', Approved: 'blue', Released: 'green', Rejected: 'red' };

const tableScroll = document.getElementById('tableScroll');
const tableBody   = document.getElementById('tableBody');
const tableState  = document.getElementById('tableState');
const rowCount    = document.getElementById('rowCount');
const searchInput = document.getElementById('searchInput');
const statusSelect = document.getElementById('statusFilter');

let allRows   = [];
let filtered  = [];
let rendered  = 0;
let isLoading = false;

/* ==================== DUMMY DATA ==================== */

function buildDummyRows(total) {
  const items = [
    'Safety Gloves', 'Safety Shoes', 'Face Mask', 'Ear Plugs', 'Hard Hat', 'Cutter Blade',
    'Packing Tape', 'Stretch Film', 'Marker Pen', 'Bond Paper A4', 'Cable Ties', 'Cleaning Rag',
    'Lubricant Oil', 'Hand Sanitizer', 'Safety Goggles', 'Barcode Label'
  ];
  const statuses = ['Pending', 'Approved', 'Released', 'Released', 'Approved', 'Rejected'];
  const base = new Date(2026, 9, 12); // Oct 12, 2026
  const rows = [];

  for (let i = 1; i <= total; i++) {
    const date = new Date(base);
    date.setDate(base.getDate() - Math.floor(i * 1.3)); // newest first
    rows.push({
      no: 'REQ-' + String(2026000 + (total - i + 1)),
      item: items[(i * 7) % items.length],
      qty: ((i * 3) % 20) + 1,
      date: date,
      status: statuses[(i * 5) % statuses.length]
    });
  }
  return rows;
}

/* ==================== RENDER ==================== */

function rowHtml(r) {
  return `<tr>
    <td class="cell-mono">${escapeHtml(r.no)}</td>
    <td class="cell-strong">${escapeHtml(r.item)}</td>
    <td>${r.qty}</td>
    <td>${formatDate(r.date)}</td>
    <td><span class="nexus-badge ${BADGE_COLORS[r.status] || 'gray'}">${escapeHtml(r.status)}</span></td>
  </tr>`;
}

function renderNextBatch() {
  const next = filtered.slice(rendered, rendered + BATCH_SIZE);
  tableBody.insertAdjacentHTML('beforeend', next.map(rowHtml).join(''));
  rendered += next.length;
  updateCount();
  updateState();
}

function renderFromStart() {
  tableBody.innerHTML = '';
  rendered = 0;
  tableScroll.scrollTop = 0;
  renderNextBatch();
  fillIfNotScrollable();
}

function updateCount() {
  rowCount.textContent = `Showing ${rendered} of ${filtered.length}`;
}

function updateState() {
  if (!filtered.length) {
    setState('No requests found.');
  } else if (rendered >= filtered.length) {
    setState('You have reached the end of the list.');
  } else {
    tableState.hidden = true;
  }
}

function setState(html) {
  tableState.innerHTML = html;
  tableState.hidden = false;
}

/* ==================== EVENTS ==================== */

tableScroll.addEventListener('scroll', () => {
  const nearBottom = tableScroll.scrollTop + tableScroll.clientHeight >= tableScroll.scrollHeight - 100;
  if (nearBottom) loadMore();
}, { passive: true });

searchInput.addEventListener('input', debounce(applyFilters, 200));
statusSelect.addEventListener('change', applyFilters);

/* ==================== SEARCH / FILTER ==================== */

function applyFilters() {
  const q = searchInput.value.trim().toLowerCase();
  const status = statusSelect.value;

  filtered = allRows.filter(r =>
    (!status || r.status === status) &&
    (!q || r.no.toLowerCase().includes(q) || r.item.toLowerCase().includes(q))
  );
  renderFromStart();
}

/* ==================== LAZY LOAD ==================== */

function loadMore() {
  if (isLoading || rendered >= filtered.length) return;
  isLoading = true;
  setState('<span class="spinner-border" role="status" aria-hidden="true"></span>Loading more…');

  setTimeout(() => {
    renderNextBatch();
    isLoading = false;
    fillIfNotScrollable();
  }, LOAD_DELAY);
}

// On tall screens the first batch may not overflow, so scrolling would never fire
function fillIfNotScrollable() {
  if (!isLoading && rendered < filtered.length && tableScroll.scrollHeight <= tableScroll.clientHeight) {
    loadMore();
  }
}

/* ==================== UTILS ==================== */

function escapeHtml(value) {
  return String(value).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

function formatDate(d) {
  return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: '2-digit' });
}

function debounce(fn, wait) {
  let t;
  return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), wait); };
}

/* ==================== INIT ==================== */

document.addEventListener('DOMContentLoaded', () => {
  allRows = buildDummyRows(TOTAL_ROWS);
  filtered = allRows;
  renderFromStart();
});
</script>

<?php include('../../reusable/footer.php'); ?>