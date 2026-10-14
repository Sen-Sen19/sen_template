<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin / MP only (the topbar already blocks guests)
if (strtolower(trim($_SESSION['role'] ?? '')) === 'user') {
    header('Location: index.php');
    exit;
}

$page_title = 'Item Masterlist';
$page_css   = '../../reusable/main.css';
include('../../reusable/header.php');
include('../../reusable/navbar.php');
include('../../reusable/topbar.php');
?>

<!-- ==================== PAGE CONTENT ==================== -->

<main class="app-content is-fluid">

    <div class="mb-3">
        <h1 class="page-title">Item Masterlist</h1>
        <p class="page-subtitle">
            Product codes with their unit costs. To add items, download the template, fill it in, then import it.
            Import adds new items and updates the costs of existing ones (same product code + name). Identical rows are skipped as duplicates, and nothing is ever deleted.
        </p>
    </div>

    <!-- Toolbar -->
    <div class="nexus-toolbar">

    <div class="nexus-search">
        <i class="bi bi-search"></i>
        <input type="text" id="searchItem" class="form-control nexus-form-control" placeholder="Search product code or name" autocomplete="off">
    </div>

    <button type="button" id="btnSearch" class="btn nexus-btn-primary">
        <i class="bi bi-search"></i>
        Search
    </button>

    <button type="button" id="btnClear" class="btn nexus-btn-secondary">
        <i class="bi bi-x-lg"></i>
        Clear
    </button>
<div class="nexus-toolbar-actions">
    <button type="button" id="btnTemplate" class="btn nexus-btn-secondary">
        <i class="bi bi-file-earmark-spreadsheet"></i>
        Export Template
    </button>
    <button type="button" id="btnImport" class="btn nexus-btn-secondary">
        <i class="bi bi-file-earmark-arrow-up"></i>
        Import CSV
    </button>
    <button type="button" id="btnExport" class="btn nexus-btn-secondary">
        <i class="bi bi-filetype-csv"></i>
        Export CSV
    </button>
</div>

<input type="file" id="importFile" accept=".csv" hidden>

    <span class="nexus-toolbar-count" id="itemCount"></span>

</div>

    <!-- Table -->
    <div class="nexus-card">
        <div class="nexus-table-scroll" id="itemScroll">
            <table class="nexus-table">
                <thead>
                    <tr>
                        <th>Product Code</th>
                        <th>Product Name</th>
                        <th class="text-end">Unit Cost (JPY)</th>
                        <th class="text-end">Unit Cost (PHP)</th>
                        <th>Added By</th>
                        <th>Date Added</th>
                    </tr>
                </thead>
                <tbody id="itemBody"></tbody>
            </table>
            <div class="nexus-state" id="itemState"></div>
            <div id="itemSentinel" aria-hidden="true"></div>
        </div>
    </div>

</main>

<script>
    /* ==================== CONFIG ==================== */

    const FETCH_URL  = '../../api/admin/fetch_item_masterlist.php';
    const IMPORT_URL = '../../api/admin/import_item_masterlist.php';
    const BATCH_SIZE = 30;

    const EXPORT_HEADERS = ['Product Code', 'Product Name', 'Unit Cost (JPY)', 'Unit Cost (PHP)'];
    const REQUIRED_COLS  = ['product_code', 'product_name', 'unit_cost_jpy', 'unit_cost_php'];
    const SPINNER = '<span class="spinner-border spinner-border-sm"></span>';

    const els = {
        scroll: document.getElementById('itemScroll'),
        body: document.getElementById('itemBody'),
        state: document.getElementById('itemState'),
        sentinel: document.getElementById('itemSentinel'),
        count: document.getElementById('itemCount'),
        search: document.getElementById('searchItem'),
        btnSearch: document.getElementById('btnSearch'),
        btnClear: document.getElementById('btnClear'),
        btnImport: document.getElementById('btnImport'),
        btnExport: document.getElementById('btnExport'),
        btnTemplate: document.getElementById('btnTemplate'),
        file: document.getElementById('importFile')
    };

    const money = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 4 });

    let total = null;
    let rendered = 0;
    let isLoading = false;
    let isExporting = false;
    let isImporting = false;
    let loadToken = 0; // ignores late responses after the search changes
    let observer = null;

    /* ==================== FETCH ==================== */

    async function fetchItems(offset, limit) {
        const params = new URLSearchParams({
            q: els.search.value.trim(),
            offset: offset,
            limit: limit
        });
        const res = await fetch(FETCH_URL + '?' + params.toString());
        return res.json();
    }

    /* ==================== RENDER ==================== */

    function rowHtml(r) {
        const cost = (v) => (v === null || v === undefined ? '—' : money.format(v));
        const name = r.product_name || '';
        return `<tr>
            <td class="cell-strong">${esc(r.product_code)}</td>
            <td title="${esc(name)}">${name ? esc(truncate(name)) : '—'}</td>
            <td class="cell-num text-end">${cost(r.unit_cost_jpy)}</td>
            <td class="cell-num text-end">${cost(r.unit_cost_php)}</td>
            <td>${esc(r.added_by || '—')}</td>
            <td>${esc(r.datetime_added || '—')}</td>
        </tr>`;
    }

    async function loadNextBatch() {
        if (isLoading || (total !== null && rendered >= total)) return;
        const token = loadToken;
        isLoading = true;
        els.state.innerHTML = '<div class="spinner-border spinner-border-sm text-primary"></div>';

        let res;
        try {
            res = await fetchItems(rendered, BATCH_SIZE);
        } catch (e) {
            res = { status: false, message: 'Could not load items.' };
        }
        if (token !== loadToken) return;

        isLoading = false;

        if (!res.status) {
            els.state.textContent = res.message || 'Could not load items.';
            return;
        }

        total = res.total;
        els.body.insertAdjacentHTML('beforeend', res.data.map(rowHtml).join(''));
        rendered += res.data.length;

        els.count.textContent = `Showing ${rendered} of ${total}`;
        els.state.textContent = total === 0 ? 'No items found.' : '';

        // Re-observe so the sentinel fires again if it is still in view (short first batch)
        if (rendered < total) {
            observer.unobserve(els.sentinel);
            observer.observe(els.sentinel);
        }
    }

    function resetTable() {
        loadToken++;
        isLoading = false;
        total = null;
        rendered = 0;
        els.body.innerHTML = '';
        els.scroll.scrollTop = 0;
        loadNextBatch();
    }

    /* ==================== EXPORT (CSV) ==================== */

    function csvEscape(value) {
        if (value === null || value === undefined) return '';
        const str = String(value);
        return /[",\n\r]/.test(str) ? '"' + str.replace(/"/g, '""') + '"' : str;
    }

    function downloadCsv(content, filename) {
        // BOM so Excel reads UTF-8 correctly
        const blob = new Blob(['\ufeff' + content], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    // Headers only: fill it in, then use Import CSV
    function exportTemplate() {
        downloadCsv(EXPORT_HEADERS.map(csvEscape).join(',') + '\r\n', 'item_masterlist_template.csv');
    }

    async function exportItems() {
        if (isExporting) return;
        isExporting = true;

        const originalHtml = els.btnExport.innerHTML;
        els.btnExport.disabled = true;
        els.btnExport.innerHTML = SPINNER + ' Exporting...';

        try {
            // No "q" on purpose: export always covers the whole table, not the current search
            const res = await (await fetch(FETCH_URL + '?export=1')).json();
            if (!res.status) throw new Error(res.message || 'Export failed.');
            if (!res.data.length) return notify('info', 'Nothing to export.');

            const lines = res.data.map((r) => [
                r.product_code,
                r.product_name,
                r.unit_cost_jpy,
                r.unit_cost_php
            ].map(csvEscape).join(','));

            const csv = [EXPORT_HEADERS.map(csvEscape).join(',')].concat(lines).join('\r\n');
            downloadCsv(csv, 'item_masterlist_' + new Date().toLocaleDateString('en-CA') + '.csv');

            notify('success', 'Exported ' + res.data.length + ' item(s).');
        } catch (e) {
            notify('error', e.message || 'Export failed. Please try again.');
        } finally {
            isExporting = false;
            els.btnExport.disabled = false;
            els.btnExport.innerHTML = originalHtml;
        }
    }

    /* ==================== IMPORT (CSV) ==================== */

    // "Unit Cost (JPY)" -> "unit_cost_jpy"
    const normKey = (k) => String(k).toLowerCase().trim().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');

    // Small RFC-4180 style parser: quotes, escaped quotes, CRLF, BOM
    function parseCsv(text, delim) {
        if (text.charCodeAt(0) === 0xFEFF) text = text.slice(1);

        const rows = [];
        let row = [];
        let field = '';
        let inQuotes = false;

        for (let i = 0; i < text.length; i++) {
            const c = text[i];

            if (inQuotes) {
                if (c === '"') {
                    if (text[i + 1] === '"') { field += '"'; i++; }
                    else inQuotes = false;
                } else {
                    field += c;
                }
            } else if (c === '"') {
                inQuotes = true;
            } else if (c === delim) {
                row.push(field);
                field = '';
            } else if (c === '\n' || c === '\r') {
                if (c === '\r' && text[i + 1] === '\n') i++;
                row.push(field);
                field = '';
                rows.push(row);
                row = [];
            } else {
                field += c;
            }
        }
        if (field !== '' || row.length) {
            row.push(field);
            rows.push(row);
        }
        return rows;
    }

    function csvToRows(text) {
        const firstLine = text.replace(/^\ufeff/, '').split(/\r?\n/, 1)[0] || '';
        const delim = firstLine.includes(',') ? ',' : (firstLine.includes(';') ? ';' : ',');
        const lines = parseCsv(text, delim);

        if (!lines.length) return { error: 'The file is empty.' };

        const header = lines[0].map(normKey);
        const missing = REQUIRED_COLS.filter((c) => !header.includes(c));
        if (missing.length) {
            return { error: 'Missing column(s): ' + missing.join(', ') + '. Use the template or a file exported from this page.' };
        }

        const idx = {};
        REQUIRED_COLS.forEach((c) => { idx[c] = header.indexOf(c); });

        const rows = [];
        for (let i = 1; i < lines.length; i++) {
            const row = { row: i + 1 }; // matches the line number in the file
            REQUIRED_COLS.forEach((c) => { row[c] = String(lines[i][idx[c]] ?? '').trim(); });
            if (REQUIRED_COLS.every((c) => row[c] === '')) continue; // blank line
            rows.push(row);
        }
        return { rows };
    }

    async function postImport(rows, dryRun) {
        try {
            const res = await fetch(IMPORT_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ rows: rows, dry_run: dryRun })
            });
            return await res.json();
        } catch (e) {
            return { status: false, message: 'Request failed. Please try again.' };
        }
    }

    async function confirmImport(s) {
        const warnings = s.warnings || [];
        const warnHtml = warnings.length
            ? '<div class="text-warning-emphasis small mt-2"><b>' + warnings.length + ' warning(s):</b><br>' +
              warnings.slice(0, 5).map((w) => `Row ${w.row}: ${esc(w.reason)}`).join('<br>') +
              (warnings.length > 5 ? `<br>… and ${warnings.length - 5} more` : '') +
              '</div>'
            : '';

        const skippedRows = s.skipped_rows || [];
        const skipHtml = skippedRows.length
            ? '<div class="text-danger small mt-2"><b>' + skippedRows.length + ' row(s) will be skipped (invalid):</b><br>' +
              skippedRows.slice(0, 5).map((x) => `Row ${x.row}: ${esc(x.reason)}`).join('<br>') +
              (skippedRows.length > 5 ? `<br>… and ${skippedRows.length - 5} more` : '') +
              '</div>'
            : '';

        const html = `<div class="text-start">
            <div><b>${s.added}</b> to add</div>
            <div><b>${s.updated}</b> to update</div>
            <div class="text-muted mt-1">${s.duplicates} duplicate(s) skipped${s.repeated ? ', ' + s.repeated + ' repeated row(s) in the file ignored' : ''}</div>
            ${skipHtml}
            ${warnHtml}
        </div>`;

        if (!window.Swal) {
            return confirm(`Add ${s.added}, update ${s.updated}, skip ${s.duplicates} duplicate(s). Apply this import?`);
        }

        const res = await Swal.fire({
            title: 'Apply this import?',
            html: html,
            icon: (warnings.length || skippedRows.length) ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonText: 'Apply import'
        });
        return res.isConfirmed;
    }

    function showImportErrors(res) {
        const list = res.invalid || [];
        if (!list.length || !window.Swal) return notify('error', res.message || 'Import failed.');

        Swal.fire({
            title: 'Import blocked',
            icon: 'error',
            html: '<div class="mb-2">' + esc(res.message || '') + '</div>' +
                '<div class="text-start small">' +
                list.slice(0, 10).map((x) => `Row ${x.row}: ${esc(x.reason)}`).join('<br>') +
                (list.length > 10 ? `<br>… and ${list.length - 10} more` : '') +
                '</div>'
        });
    }

    async function importItems(file) {
        if (isImporting) return;
        isImporting = true;

        const originalHtml = els.btnImport.innerHTML;
        els.btnImport.disabled = true;
        els.btnImport.innerHTML = SPINNER + ' Checking...';

        try {
            const parsed = csvToRows(await file.text());
            if (parsed.error) return notify('error', parsed.error);
            if (!parsed.rows.length) return notify('info', 'The file has no data rows.');

            // Step 1: dry run, nothing is saved
            const check = await postImport(parsed.rows, true);
            if (!check.status) return showImportErrors(check);

            if (!check.added && !check.updated) {
                return notify('info', 'Nothing to import. ' + check.duplicates + ' duplicate(s) and ' + check.skipped + ' invalid row(s) skipped.');
            }

            if (!(await confirmImport(check))) return;

            // Step 2: apply
            els.btnImport.innerHTML = SPINNER + ' Importing...';
            const res = await postImport(parsed.rows, false);
            if (!res.status) return showImportErrors(res);

            notify('success', `Import done: ${res.added} added, ${res.updated} updated, ${res.duplicates} duplicate(s) and ${res.skipped} invalid row(s) skipped.`);
            resetTable();
        } catch (e) {
            notify('error', 'Could not read that file.');
        } finally {
            isImporting = false;
            els.btnImport.disabled = false;
            els.btnImport.innerHTML = originalHtml;
        }
    }

    /* ==================== EVENTS ==================== */

    function bindEvents() {
        observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) loadNextBatch();
        }, { root: els.scroll, rootMargin: '100px' });
        observer.observe(els.sentinel);

        els.btnSearch.addEventListener('click', resetTable);

        els.search.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') resetTable();
        });

        els.btnClear.addEventListener('click', () => {
            els.search.value = '';
            resetTable();
        });

        els.btnTemplate.addEventListener('click', exportTemplate);
        els.btnExport.addEventListener('click', exportItems);

        els.btnImport.addEventListener('click', () => els.file.click());
        els.file.addEventListener('change', () => {
            const file = els.file.files[0];
            els.file.value = ''; // lets the same file be picked again
            if (file) importItems(file);
        });
    }

    /* ==================== UTILS ==================== */

    function esc(v) {
        return String(v).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function truncate(v, max = 20) {
        const s = String(v ?? '');
        return s.length > max ? s.slice(0, max).trimEnd() + '...' : s;
    }

    function notify(icon, msg) {
        if (window.Swal) Swal.fire({ toast: true, position: 'top-end', icon, title: msg, showConfirmButton: false, timer: 2600 });
        else alert(msg);
    }

    /* ==================== INIT ==================== */

    document.addEventListener('DOMContentLoaded', function() {
        bindEvents();
        resetTable();
    });
</script>
<?php include('../../reusable/footer.php'); ?>