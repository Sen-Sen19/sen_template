<?php

/**
 * |--------------------------------------------------------------------------
 * | Journal Page
 * |--------------------------------------------------------------------------
 * | Journal entries search by voucher date range / transaction reference,
 * | plus .xlsx import. Data comes from ../../api/admin/fetch_journal.php
 * | and is imported through ../../api/admin/import_journal.php.
 * |--------------------------------------------------------------------------
 */

date_default_timezone_set('Asia/Manila');
$today_manila = date('Y-m-d');

$page_title = 'Journal Page';
$page_css   = '../../reusable/main.css';
include('../../reusable/header.php');
include('../../reusable/navbar.php');
include('../../reusable/topbar.php');

?>

<!-- ==================== PAGE CONTENT ==================== -->

<main class="app-content is-fluid">

    <div class="mb-3">
        <h1 class="page-title">Journal Page</h1>
        <p class="page-subtitle">Search imported journal entries by voucher date or transaction reference.</p>
    </div>

    <!-- Toolbar -->
    <div class="nexus-toolbar">

        <input type="date" id="dateFrom" class="form-control nexus-form-control journal-date" aria-label="Date from" value="<?php echo $today_manila; ?>">
        <input type="date" id="dateTo" class="form-control nexus-form-control journal-date" aria-label="Date to" value="<?php echo $today_manila; ?>">

        <div class="nexus-search">
            <i class="bi bi-file-earmark-text"></i>
            <input type="text" id="transactionRef" class="form-control nexus-form-control" placeholder="Transaction reference" autocomplete="off">
        </div>

        <button type="button" id="btnLoad" class="btn nexus-btn-primary">
            <i class="bi bi-search"></i>
            Search
        </button>

        <button type="button" id="btnClear" class="btn nexus-btn-secondary">
            <i class="bi bi-x-lg"></i>
            Clear
        </button>

        <button type="button" id="btnImport" class="btn nexus-btn-secondary">
            <i class="bi bi-file-earmark-arrow-up"></i>
            Import
        </button>

        <input type="file" id="importFileInput" accept=".xlsx" hidden>

        <span class="nexus-toolbar-count" id="journalCount"></span>

    </div>

    <!-- Table -->
    <div class="nexus-card">
        <div class="nexus-table-scroll" id="journalScroll">
            <table class="nexus-table">
                <thead id="journalHead"></thead>
                <tbody id="journalBody"></tbody>
            </table>
            <div class="nexus-state" id="journalState">Select a date range to view records.</div>
        </div>
    </div>

</main>

<style>
/* ==================== TOOLBAR ==================== */

.journal-date.form-control.nexus-form-control {
  flex: 0 0 150px;
}

/* ==================== TABLE ==================== */

.nexus-table .cell-null {
  color: var(--muted-light);
  font-style: italic;
}

.nexus-table .cell-amount,
.nexus-table thead th.cell-amount {
  text-align: right;
  font-variant-numeric: tabular-nums;
}

/* ==================== RESPONSIVE ==================== */

@media (max-width: 768px) {
  .journal-date.form-control.nexus-form-control {
    flex: 1 1 calc(50% - 4px);
  }
}
</style>

<script>
    /* ==================== CONFIG ==================== */

    const API_BASE = '../../api/admin/';
    const BATCH_SIZE = 100;

    const COLUMNS = [
        { key: 'voucher_date',              label: 'Voucher Date' },
        { key: 'ledger_journal_name',       label: 'Ledger Journal Name' },
        { key: 'journal_batch_number',      label: 'Journal Batch Number' },
        { key: 'line_number',               label: 'Line Number' },
        { key: 'original_system',           label: 'Original System' },
        { key: 'voucher_remark',            label: 'Voucher Remark' },
        { key: 'debit_credit',              label: 'Debit/Credit' },
        { key: 'prepared_by',               label: 'Prepared By' },
        { key: 'prepared_by_name',          label: 'Prepared By (Name)' },
        { key: 'approved_by',               label: 'Approved By' },
        { key: 'approved_by_name',          label: 'Approved By (Name)' },
        { key: 'approved_date',             label: 'Approved Date' },
        { key: 'department',                label: 'Department' },
        { key: 'department_name',           label: 'Department Name' },
        { key: 'ledger_accounts',           label: 'Ledger Accounts' },
        { key: 'account_name',              label: 'Account Name' },
        { key: 'ledger_account',            label: 'Ledger Account' },
        { key: 'detail',                    label: 'Detail' },
        { key: 'detail_name',               label: 'Detail Name' },
        { key: 'business_partner',          label: 'Business Partner' },
        { key: 'business_partner_name',     label: 'Business Partner Name' },
        { key: 'aging_code',                label: 'Aging Code' },
        { key: 'detail_remarks',            label: 'Detail Remarks' },
        { key: 'transaction_currency',      label: 'Transaction Currency' },
        { key: 'transaction_amount',        label: 'Transaction Amount' },
        { key: 'exchange_rate',             label: 'Exchange Rate' },
        { key: 'accounting_amount',         label: 'Accounting Amount' },
        { key: 'reporting_currency',        label: 'Reporting Currency' },
        { key: 'reporting_rate',            label: 'Reporting Rate' },
        { key: 'reporting_amount',          label: 'Reporting Amount' },
        { key: 'tax_code',                  label: 'Tax Code' },
        { key: 'tax_name',                  label: 'Tax Name' },
        { key: 'tax_percentage',            label: 'Tax Percentage' },
        { key: 'transaction_reference',     label: 'Transaction Reference' },
        { key: 'invoice',                   label: 'Invoice' },
        { key: 'due_date',                  label: 'Due Date' },
        { key: 'payment_method',            label: 'Payment Method' },
        { key: 'allocation_reference',      label: 'Allocation Reference' },
        { key: 'transaction_reference_no2', label: 'Transaction Reference No2' }
    ];

    const CURRENCY_COLS = ['transaction_currency', 'reporting_currency'];
    const AMOUNT_COLS = ['transaction_amount', 'accounting_amount', 'reporting_amount'];
    const BADGE_NEUTRAL_COLS = ['invoice', 'journal_batch_number'];
    const EMPTY_HINT = 'Select a date range to view records.';

    const els = {
        scroll: document.getElementById('journalScroll'),
        head: document.getElementById('journalHead'),
        body: document.getElementById('journalBody'),
        state: document.getElementById('journalState'),
        count: document.getElementById('journalCount'),
        dateFrom: document.getElementById('dateFrom'),
        dateTo: document.getElementById('dateTo'),
        transactionRef: document.getElementById('transactionRef'),
        btnLoad: document.getElementById('btnLoad'),
        btnClear: document.getElementById('btnClear'),
        btnImport: document.getElementById('btnImport'),
        importFile: document.getElementById('importFileInput')
    };

    let total = null;     // total matching rows on the server (null = not searched yet)
    let rendered = 0;
    let isLoading = false;
    let loadToken = 0;    // ignores late responses after a new search

    /* ==================== FETCH / API ==================== */

    async function fetchJournal(offset, limit) {
        const params = new URLSearchParams({
            date_from: els.dateFrom.value,
            date_to: els.dateTo.value,
            transaction_reference: els.transactionRef.value.trim(),
            offset: offset,
            limit: limit
        });
        const res = await fetch(API_BASE + 'fetch_journal.php?' + params.toString());
        return res.json();
    }

    async function postImport(file) {
        const fd = new FormData();
        fd.append('import_file', file);
        try {
            const res = await fetch(API_BASE + 'import_journal.php', { method: 'POST', body: fd });
            return await res.json();
        } catch (e) {
            return { success: false, message: 'Import failed. Please try again.' };
        }
    }

    /* ==================== RENDER ==================== */

    function renderHead() {
        els.head.innerHTML = '<tr>' + COLUMNS.map((c) => {
            const cls = AMOUNT_COLS.includes(c.key) ? ' class="cell-amount"' : '';
            return `<th${cls}>${esc(c.label)}</th>`;
        }).join('') + '</tr>';
    }

    function cellHtml(col, value) {
        if (value === null || value === undefined || value === '') {
            return '<td><span class="cell-null">NULL</span></td>';
        }
        if (CURRENCY_COLS.includes(col)) {
            const color = String(value).trim().toUpperCase() === 'JPY' ? 'purple' : 'blue';
            return `<td><span class="nexus-badge ${color}">${esc(value)}</span></td>`;
        }
        if (BADGE_NEUTRAL_COLS.includes(col)) {
            return `<td><span class="nexus-badge gray">${esc(value)}</span></td>`;
        }
        if (AMOUNT_COLS.includes(col)) {
            return `<td class="cell-amount">${esc(value)}</td>`;
        }
        return `<td>${esc(value)}</td>`;
    }

    function rowHtml(row) {
        return '<tr>' + COLUMNS.map((c) => cellHtml(c.key, row[c.key])).join('') + '</tr>';
    }

    async function loadNextBatch() {
        if (isLoading || total === null || rendered >= total) return;
        const token = loadToken;
        isLoading = true;
        els.state.innerHTML = '<div class="spinner-border spinner-border-sm text-primary"></div>';

        let res;
        try {
            res = await fetchJournal(rendered, BATCH_SIZE);
        } catch (e) {
            res = { success: false, message: 'Could not load records.' };
        }
        if (token !== loadToken) return; // a new search started while loading

        if (!res.success) {
            isLoading = false;
            els.state.textContent = res.message || 'Could not load records.';
            return;
        }

        els.body.insertAdjacentHTML('beforeend', res.data.map(rowHtml).join(''));
        rendered += res.data.length;

        isLoading = false;
        updateState();

        // Fill the container if the batch doesn't overflow it
        if (res.data.length && els.scroll.scrollHeight <= els.scroll.clientHeight && rendered < total) {
            loadNextBatch();
        }
    }

    function updateState() {
        els.count.textContent = `Showing ${rendered} of ${total}`;
        els.state.textContent = total === 0 ? 'No records found.' : (rendered >= total ? 'All records loaded' : '');
    }

    /* ==================== SEARCH / FILTER ==================== */

    async function startSearch() {
        const token = ++loadToken;
        isLoading = true;
        total = null;
        rendered = 0;
        renderHead();
        els.body.innerHTML = '';
        els.scroll.scrollTop = 0;
        els.count.textContent = '';
        els.state.innerHTML = '<div class="spinner-border spinner-border-sm text-primary"></div>';

        let res;
        try {
            res = await fetchJournal(0, BATCH_SIZE);
        } catch (e) {
            res = { success: false, message: 'Could not load records.' };
        }
        if (token !== loadToken) return;

        if (!res.success) {
            isLoading = false;
            els.state.textContent = res.message || 'Could not load records.';
            return;
        }

        total = res.total;
        els.body.innerHTML = res.data.map(rowHtml).join('');
        rendered = res.data.length;

        isLoading = false;
        updateState();

        if (res.data.length && els.scroll.scrollHeight <= els.scroll.clientHeight && rendered < total) {
            loadNextBatch();
        }
    }

    function clearSearch() {
        loadToken++;
        isLoading = false;
        total = null;
        rendered = 0;
        els.dateFrom.value = '';
        els.dateTo.value = '';
        els.transactionRef.value = '';
        els.head.innerHTML = '';
        els.body.innerHTML = '';
        els.count.textContent = '';
        els.state.textContent = EMPTY_HINT;
    }

    /* ==================== EVENTS ==================== */

    function bindEvents() {
        els.scroll.addEventListener('scroll', () => {
            const nearBottom = els.scroll.scrollTop + els.scroll.clientHeight >= els.scroll.scrollHeight - 100;
            if (nearBottom) loadNextBatch();
        });

        els.btnLoad.addEventListener('click', startSearch);
        els.btnClear.addEventListener('click', clearSearch);

        [els.dateFrom, els.dateTo, els.transactionRef].forEach((el) => {
            el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') startSearch();
            });
        });

        els.btnImport.addEventListener('click', () => els.importFile.click());
        els.importFile.addEventListener('change', importJournal);
    }

    /* ==================== IMPORT ==================== */

    async function importJournal() {
        const file = els.importFile.files[0];
        if (!file) return;

        const confirm = await Swal.fire({
            title: 'Import Journal Data?',
            text: 'This will add "' + file.name + '" to the journal table.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Import'
        });
        if (!confirm.isConfirmed) {
            els.importFile.value = '';
            return;
        }

        Swal.fire({
            title: 'Importing...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const res = await postImport(file);
        els.importFile.value = '';

        if (!res.success) {
            Swal.fire('Import Failed', res.message || 'Unknown error.', 'error');
            return;
        }

        Swal.fire('Import Complete', res.message, 'success');

        // Refresh the table only if a search was already run
        if (total !== null) startSearch();
    }

    /* ==================== UTILS ==================== */

    function esc(v) {
        return String(v).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    /* ==================== INIT ==================== */

    document.addEventListener('DOMContentLoaded', function() {
        bindEvents();
    });
</script>

<?php include('../../reusable/footer.php'); ?>