<?php

$page_title = 'Withdrawals';
$page_css   = '../../reusable/main.css';
include('../../reusable/header.php');
include('../../reusable/navbar.php');
include('../../reusable/topbar.php');

?>

<main class="app-content is-fluid">

    <div class="mb-3">
        <h1 class="page-title">Withdrawals</h1>
        <p class="page-subtitle">Daily withdrawals per account.</p>
    </div>

    <!-- Toolbar -->
    <div class="nexus-toolbar">

        <div class="wd-field">
            <label for="dateFrom" class="wd-field-label">From</label>
            <input type="date" id="dateFrom" class="form-control nexus-form-control">
        </div>

        <div class="wd-field">
            <label for="dateTo" class="wd-field-label">To</label>
            <input type="date" id="dateTo" class="form-control nexus-form-control">
        </div>

        <select id="searchAccount" class="form-select nexus-form-control wd-account">
            <option value="">All accounts</option>
        </select>

        <button type="button" id="btnSearch" class="btn nexus-btn-primary">
            <i class="bi bi-search"></i>
            Search
        </button>

        <button type="button" id="btnClear" class="btn nexus-btn-secondary">
            <i class="bi bi-x-lg"></i>
            Clear
        </button>

        <button type="button" id="btnExport" class="btn nexus-btn-secondary">
            <i class="bi bi-download"></i>
            Export
        </button>

        <span class="nexus-toolbar-count" id="wdCount"></span>
    </div>

    <!-- Table -->
    <div class="nexus-card">
        <div class="nexus-table-scroll">
            <table class="nexus-table">
                <thead id="wdHead"></thead>
                <tbody id="wdBody"></tbody>
            </table>
            <div class="nexus-state" id="wdState">Choose a date range and press Search.</div>
        </div>
    </div>

</main>

<style>
    .wd-field {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .wd-field-label {
        margin: 0;
        color: var(--muted);
        font-size: 12px;
    }

    .wd-field .form-control {
        width: 150px;
    }

    /* wider than the default 170px so long account names fit */
    .nexus-toolbar .form-select.nexus-form-control.wd-account {
        flex: 0 0 280px;
    }

    @media (max-width: 768px) {
        .nexus-toolbar .form-select.nexus-form-control.wd-account {
            flex: 1 1 100%;
        }

        .wd-field {
            flex: 1 1 100%;
        }

        .wd-field .form-control {
            width: 100%;
        }
    }
</style>

<script>
    /* ==================== CONFIG ==================== */

    const API_URL = '../../api/admin/withdrawals.php';

    const ACCOUNTS = [
        'Accounting',
        'Equipment Engineering',
        'Equipment Management',
        'Facilities, Repair and Maintenance',
        'Factory IE - NF Kaizen',
        'Finished Goods',
        'Heath and Safety',
        'Hodec Department',
        'Human Resource & General Affairs',
        'Information Technology',
        'Initial Process',
        'Kaizen',
        'Material Management',
        'NPI-MPPD',
        'Procurement',
        'Production Control',
        'Production Design ID',
        'Production Engineering Final',
        'Production Engineering Initial',
        'Quality Assurance',
        'Quality Control',
        'Quality Management',
        'Quality Preparation',
        'Recruitment & Training',
        'Safety and Health Department',
        'Set-UP & Fabrication Engineering',
        'Spare Parts & Asset Management',
        'Technical Document Creation ID',
        'WH Common ID',
        'Wire Production ID'
    ];

    // key: null = left blank on purpose. num: right-aligned.
    const COLUMNS = [{
            label: 'Requested Section Code',
            key: 'requested_section_code'
        },
        {
            label: 'Requested Section',
            key: 'requested_section_name'
        },
        {
            label: 'End Use Section Code',
            key: 'end_use_section_code'
        },
        {
            label: 'End Use Section',
            key: 'end_use_section'
        },
        {
            label: 'Item Code',
            key: 'item_code'
        },
        {
            label: 'Item Name',
            key: 'item_name'
        },
        {
            label: 'Unit',
            key: 'unit_code'
        },
        {
            label: 'Supplier Code',
            key: 'supplier_code'
        },
        {
            label: 'Supplier Name',
            key: 'supplier_name'
        },
        {
            label: 'Invoice No.',
            key: 'invoice_no'
        },
        {
            label: 'Unit Cost JPY',
            key: 'unit_cost_jpy',
            num: true
        },
        {
            label: 'Unit Cost PHP',
            key: 'unit_cost_php',
            num: true
        },
        {
            label: 'Beginning Qty',
            key: 'fsib_quantity',
            num: true
        },
        {
            label: 'Beginning Amount JPY',
            calc: (r) => mult(r.unit_cost_jpy, r.fsib_quantity),
            num: true
        },
        {
            label: 'Beginning Amount PHP',
            calc: (r) => mult(r.unit_cost_php, r.fsib_quantity),
            num: true
        },

        {
            label: 'Store In Qty',
            key: null,
            num: true
        },
        {
            label: 'Store In Amount JPY',
            key: null,
            num: true
        },
        {
            label: 'Store In Amount PHP',
            key: null,
            num: true
        },
        {
            label: 'Store Out Qty',
            key: null,
            num: true
        },
        {
            label: 'Store Out Amount JPY',
            key: null,
            num: true
        },
        {
            label: 'Store Out Amount PHP',
            key: null,
            num: true
        },
        {
            label: 'Ending Qty',
            key: 'actual_quantity',
            num: true
        },
        {
            label: 'Ending Amount JPY',
            calc: (r) => mult(r.unit_cost_jpy, r.actual_quantity),
            num: true
        },
        {
            label: 'Ending Amount PHP',
            calc: (r) => mult(r.unit_cost_php, r.actual_quantity),
            num: true
        },
        {
            label: 'Remarks',
            key: null
        },
        {
            label: 'Variance Qty',
            calc: (r) => diff(r.actual_quantity, r.fsib_quantity),
            num: true
        },
        {
            label: 'Variance JPY',
            calc: (r) => diff(mult(r.unit_cost_jpy, r.actual_quantity), mult(r.unit_cost_jpy, r.fsib_quantity)),
            num: true
        },
        {
            label: 'Variance PHP',
            calc: (r) => diff(mult(r.unit_cost_php, r.actual_quantity), mult(r.unit_cost_php, r.fsib_quantity)),
            num: true
        },
    ];

    const els = {
        from: document.getElementById('dateFrom'),
        to: document.getElementById('dateTo'),
        account: document.getElementById('searchAccount'),
        btnSearch: document.getElementById('btnSearch'),
        btnClear: document.getElementById('btnClear'),
        btnExport: document.getElementById('btnExport'),
        head: document.getElementById('wdHead'),
        body: document.getElementById('wdBody'),
        state: document.getElementById('wdState'),
        count: document.getElementById('wdCount')
    };

    let rows = []; // every column of every fetched row (the table shows only some)
    let loadToken = 0; // ignores late responses

    /* ==================== UTILS ==================== */

    function fmt(d) {
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    function esc(v) {
        return String(v ?? '').replace(/[&<>"']/g, (c) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        } [c]));
    }

    function num(v) {
        if (v === null || v === undefined || v === '') return '—';
        return Number(v).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    // Product of two values, rounded to 2 decimals. null if either is missing.
    function mult(a, b) {
        if (a === null || a === undefined || a === '' || b === null || b === undefined || b === '') return null;
        return Math.round(Number(a) * Number(b) * 100) / 100;
    }
    // Ending minus Beginning, rounded to 2 decimals. null if either side is missing.
    function diff(end, begin) {
        if (end === null || end === undefined || begin === null || begin === undefined) return null;
        return Math.round((end - begin) * 100) / 100;
    }

    // The value a column shows: calculated, taken from the row, or nothing (blank column)
    function colValue(c, r) {
        if (c.calc) return c.calc(r);
        return c.key ? r[c.key] : null;
    }

    function toast(msg, icon) {
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon || 'info',
                title: msg,
                showConfirmButton: false,
                timer: 1800
            });
        }
    }

    /* ==================== RENDER ==================== */

    function cellHtml(c, r) {
        if (!c.key && !c.calc) return `<td${c.num ? ' class="text-end"' : ''}></td>`;
        if (c.num) return `<td class="text-end">${num(colValue(c, r))}</td>`;
        return `<td>${esc(colValue(c, r)) || '—'}</td>`;
    }

    function rowHtml(r) {
        const v = diff(r.actual_quantity, r.fsib_quantity);
        const cls = v === null ? ' class="wd-unmatched"' : (v !== 0 ? ' class="wd-variance"' : '');
        return `<tr${cls}>` + COLUMNS.map((c) => cellHtml(c, r)).join('') + '</tr>';
    }

    function render() {
        els.body.innerHTML = rows.map(rowHtml).join('');
        els.count.textContent = rows.length ? `${rows.length} record${rows.length === 1 ? '' : 's'}` : '';
        els.state.textContent = rows.length ? '' : 'No withdrawals found.';
    }

    /* ==================== FILTERS ==================== */

    function setDefaultDates() {
        const now = new Date();
        els.from.value = fmt(new Date(now.getFullYear(), now.getMonth(), 1));
        els.to.value = fmt(now);
    }

    async function search() {
        const from = els.from.value;
        const to = els.to.value;

        if (!from || !to) {
            els.state.textContent = 'Pick a start and end date.';
            return;
        }
        if (from > to) {
            els.state.textContent = 'The start date must be before the end date.';
            return;
        }

        const token = ++loadToken;
        els.btnSearch.disabled = true;
        els.body.innerHTML = '';
        els.count.textContent = '';
        els.state.innerHTML = '<div class="spinner-border spinner-border-sm text-primary"></div>';

        let res;
        try {
            const params = new URLSearchParams({
                action: 'list',
                from: from,
                to: to,
                account: els.account.value
            });
            const r = await fetch(API_URL + '?' + params.toString());
            res = await r.json();
        } catch (e) {
            res = {
                status: false,
                message: 'Could not load withdrawals.'
            };
        }
        if (token !== loadToken) return;

        els.btnSearch.disabled = false;

        if (!res.status) {
            rows = [];
            els.state.textContent = res.message || 'Could not load withdrawals.';
            return;
        }

        rows = res.data;
        render();
    }

    function clearFilters() {
        loadToken++;
        rows = [];
        setDefaultDates();
        els.account.value = '';
        els.btnSearch.disabled = false;
        els.body.innerHTML = '';
        els.count.textContent = '';
        els.state.textContent = 'Choose a date range and press Search.';
    }

    /* ==================== EXPORT (CSV, same columns and order as the table) ==================== */

    function exportData() {
        if (!rows.length) {
            toast('Nothing to export yet.');
            return;
        }

        const q = (v) => '"' + String(v ?? '').replace(/"/g, '""') + '"';

        const lines = [COLUMNS.map((c) => q(c.label)).join(',')]
            .concat(rows.map((r) => COLUMNS.map((c) => q(colValue(c, r))).join(',')));

        const blob = new Blob(['\ufeff' + lines.join('\r\n')], {
            type: 'text/csv;charset=utf-8;'
        });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `withdrawals_${els.from.value}_to_${els.to.value}.csv`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(a.href);
    }

    /* ==================== INIT ==================== */

    document.addEventListener('DOMContentLoaded', function() {
        els.account.insertAdjacentHTML('beforeend', ACCOUNTS.map((a) => `<option value="${esc(a)}">${esc(a)}</option>`).join(''));

        els.head.innerHTML = '<tr>' + COLUMNS.map((c) =>
            `<th${c.num ? ' class="text-end"' : ''}>${esc(c.label)}</th>`).join('') + '</tr>';

        setDefaultDates();

        els.btnSearch.addEventListener('click', search);
        els.btnClear.addEventListener('click', clearFilters);
        els.btnExport.addEventListener('click', exportData);
    });
</script>
<?php include('../../reusable/footer.php'); ?>