<?php

/**
 * |--------------------------------------------------------------------------
 * | Working Paper (Purchases)
 * |--------------------------------------------------------------------------
 * | PO lines (V_FALP_PO_LIST) matched with WF data (V_WFSEARCH) and journal
 * | entries by invoice / department / amount. Data comes from
 * | ../../api/admin/fetch_purchase.php and fetch_journal_match.php.
 * |--------------------------------------------------------------------------
 */

date_default_timezone_set('Asia/Manila');
$today_manila = date('Y-m-d');

$page_title = 'Working Paper';
$page_css   = '../../reusable/main.css';
include('../../reusable/header.php');
include('../../reusable/navbar.php');
include('../../reusable/topbar.php');

?>

<!-- ==================== PAGE CONTENT ==================== -->

<main class="app-content is-fluid">

    <div class="mb-3">
        <h1 class="page-title">Working Paper</h1>
        <p class="page-subtitle">Purchase lines matched with WF data and journal entries.</p>
    </div>

    <!-- Toolbar -->
    <div class="nexus-toolbar">

        <input type="date" id="dateFrom" class="form-control nexus-form-control wp-date" aria-label="Date from" value="<?php echo $today_manila; ?>">
        <input type="date" id="dateTo" class="form-control nexus-form-control wp-date" aria-label="Date to" value="<?php echo $today_manila; ?>">

        <div class="nexus-search">
            <i class="bi bi-file-earmark-text"></i>
            <input type="text" id="invoiceNo" class="form-control nexus-form-control" placeholder="Invoice No." autocomplete="off">
        </div>

        <div class="nexus-search">
            <i class="bi bi-box-seam"></i>
            <input type="text" id="itemName" class="form-control nexus-form-control" placeholder="Item name" autocomplete="off">
        </div>

        <button type="button" id="btnLoad" class="btn nexus-btn-primary">
            <i class="bi bi-search"></i>
            Search
        </button>

        <button type="button" id="btnClear" class="btn nexus-btn-secondary">
            <i class="bi bi-x-lg"></i>
            Clear
        </button>

        <button type="button" id="btnExport" class="btn nexus-btn-secondary">
            <i class="bi bi-filetype-csv"></i>
            Export CSV
        </button>

        <span class="nexus-toolbar-count" id="wpCount"></span>

    </div>

    <!-- Source legend -->
    <div class="wp-legend">
        <span><span class="wp-dot is-po"></span>PO (V_FALP_PO_LIST)</span>
        <span><span class="wp-dot is-wf"></span>WFSEARCH (V_WFSEARCH)</span>
        <span><span class="wp-dot is-journal"></span>Journal</span>
    </div>

    <!-- Table -->
    <div class="nexus-card">
        <div class="nexus-table-scroll" id="wpScroll" style="--table-offset: 300px;">
            <table class="nexus-table">
                <thead id="wpHead"></thead>
                <tbody id="wpBody"></tbody>
            </table>
            <div class="nexus-state" id="wpState">Select a date range to view records.</div>
        </div>
    </div>

</main>

<style>
/* ==================== TOOLBAR ==================== */

.wp-date.form-control.nexus-form-control {
  flex: 0 0 150px;
}

/* ==================== LEGEND ==================== */

.wp-legend {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 16px;
  margin-bottom: 10px;
  color: var(--muted);
  font-size: 12px;
}

.wp-dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  margin-right: 6px;
  border-radius: 50%;
}

.wp-dot.is-po { background: var(--danger-text); }
.wp-dot.is-wf { background: var(--blue); }
.wp-dot.is-journal { background: var(--muted); }

/* ==================== TABLE ==================== */

.nexus-table thead th.col-po { color: var(--danger-text); }
.nexus-table thead th.col-wf { color: var(--blue); }
.nexus-table thead th.col-journal { color: var(--muted); }

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
  .wp-date.form-control.nexus-form-control {
    flex: 1 1 calc(50% - 4px);
  }
}
</style>

<script>
    /* ==================== CONFIG ==================== */

    const API_BASE = '../../api/admin/';
    const PAGE_LIMIT = 100;
    const EXPORT_PAGE_LIMIT = 1000; // larger page size when pulling everything for export
    const MIN_MATCHED_PER_LOAD = 50;
    const EMPTY_HINT = 'Select a date range to view records.';

    const PO_COLUMNS = ['INVOICE_NO', 'ITEM_ID', 'ITEM_NAME', 'PURCHQTY', 'LINE_AMOUNT_MST', 'LINE_AMOUNT'];
    const WF_COLUMNS = [
        'C_WFKEY', 'C_KIGYONO', 'C_BUMONNM', 'C_SINSEISHANM', 'C_FUTANBUMONNM',
        'C_TANICD', 'C_KOUNYUNM', 'C_SHOBUNRUINMSTD', 'C_SEIKYUNO'
    ];
    const JOURNAL_COLUMNS = [
        'match_status', 'voucher_date', 'ledger_journal_name', 'journal_batch_number', 'original_system',
        'voucher_remark', 'prepared_by_name', 'approved_by_name', 'department_name', 'account_name',
        'business_partner_name', 'transaction_currency', 'transaction_amount', 'exchange_rate',
        'accounting_amount', 'reporting_currency', 'reporting_rate', 'reporting_amount',
        'unit_price_jpy', 'unit_price_php', 'transaction_reference'
    ];
    const DISPLAY_COLUMNS = PO_COLUMNS.concat(WF_COLUMNS).concat(JOURNAL_COLUMNS);

    const COLUMN_LABELS = {
        match_status:          'Match Status',
        INVOICE_NO:            'Invoice No. (PO)',
        ITEM_ID:               'Item ID',
        ITEM_NAME:             'Item Name',
        PURCHQTY:              'Purchase Qty',
        LINE_AMOUNT_MST:       'Line Amount (MST)',
        LINE_AMOUNT:           'Line Amount',
        C_WFKEY:               'WF KEY',
        C_KIGYONO:             'Kigyo No',
        C_BUMONNM:             'Requested Section name',
        C_SINSEISHANM:         'Requested User Name',
        C_FUTANBUMONNM:        'End use Section',
        C_TANICD:              'Unit code',
        C_KOUNYUNM:            'Supplier Name',
        C_SHOBUNRUINMSTD:      'Use Purpose(Small)',
        C_SEIKYUNO:            'Invoice No.',
        voucher_date:          'Voucher Date',
        ledger_journal_name:   'Journal Name',
        journal_batch_number:  'Journal Batch No.',
        original_system:       'Original System',
        voucher_remark:        'Voucher Remark',
        prepared_by_name:      'Prepared By',
        approved_by_name:      'Approved By',
        department_name:       'Department (Journal)',
        account_name:          'Account Name',
        business_partner_name: 'Business Partner',
        transaction_currency:  'Currency',
        transaction_amount:    'Transaction Amount',
        exchange_rate:         'Exchange Rate',
        accounting_amount:     'Accounting Amount',
        reporting_currency:    'Reporting Currency',
        reporting_rate:        'Reporting Rate',
        reporting_amount:      'Reporting Amount',
        unit_price_jpy:        'Unit Price (JPY)',
        unit_price_php:        'Unit Price (PHP)',
        transaction_reference: 'Transaction Reference'
    };

    const BADGE_NEUTRAL_COLS = [
        'INVOICE_NO', 'ITEM_ID', 'C_SEIKYUNO', 'C_WFKEY', 'C_KIGYONO',
        'transaction_reference', 'journal_batch_number'
    ];

    const AMOUNT_COLS = [
        'LINE_AMOUNT_MST', 'LINE_AMOUNT',
        'transaction_amount', 'exchange_rate', 'accounting_amount',
        'reporting_rate', 'reporting_amount', 'unit_price_jpy', 'unit_price_php'
    ];

    const MATCH_COLOR = { Exact: 'green', Combined: 'yellow', Unresolved: 'red' };

    const AMOUNT_EPS = 0.01; // exact match; raise to 1 if JPY rounding causes misses

    const els = {
        scroll: document.getElementById('wpScroll'),
        head: document.getElementById('wpHead'),
        body: document.getElementById('wpBody'),
        state: document.getElementById('wpState'),
        count: document.getElementById('wpCount'),
        dateFrom: document.getElementById('dateFrom'),
        dateTo: document.getElementById('dateTo'),
        invoiceNo: document.getElementById('invoiceNo'),
        itemName: document.getElementById('itemName'),
        btnLoad: document.getElementById('btnLoad'),
        btnClear: document.getElementById('btnClear'),
        btnExport: document.getElementById('btnExport')
    };

    let currentOffset = 0;
    let currentTotal = 0;
    let displayedCount = 0;
    let isLoading = false;
    let hasSearched = false;
    let isExporting = false;
    let searchToken = 0; // ignores late responses after a new search / clear

    /* ==================== FETCH / API ==================== */

    function buildQuery(params) {
        const qs = Object.keys(params)
            .filter((k) => params[k] !== '' && params[k] !== null && params[k] !== undefined)
            .map((k) => encodeURIComponent(k) + '=' + encodeURIComponent(params[k]))
            .join('&');
        return qs ? ('?' + qs) : '';
    }

    async function fetchJournalMatches(rows) {
        const invoiceSet = new Set();
        rows.forEach((r) => {
            const invoice = r['INVOICE_NO'];
            if (invoice) invoiceSet.add(invoice);
        });
        const invoices = Array.from(invoiceSet);
        if (invoices.length === 0) return {};

        const res = await fetch(API_BASE + 'fetch_journal_match.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ invoices: invoices })
        });
        const data = await res.json();
        if (!data.success) {
            throw new Error(data.message || 'Journal match fetch failed.');
        }
        return data.matches || {};
    }

    async function fetchMatchedPage(dateFrom, dateTo, invoiceNo, itemName, offset, limit) {
        const res = await fetch(API_BASE + 'fetch_purchase.php' + buildQuery({
            date_from: dateFrom,
            date_to: dateTo,
            invoice_no: invoiceNo,
            item_name: itemName,
            offset: offset,
            limit: limit
        }));
        const data = await res.json();
        if (!data.success) {
            throw new Error(data.message || 'Failed to load records.');
        }

        // Don't let a page cut an invoice in half: hold back the trailing invoice
        // (it will be re-fetched whole at the start of the next page).
        let rawRows = data.data;
        const isLastPage = offset + rawRows.length >= data.total;
        if (!isLastPage && rawRows.length > 0) {
            const lastInv = rawRows[rawRows.length - 1]['INVOICE_NO'];
            let cut = rawRows.length;
            while (cut > 0 && rawRows[cut - 1]['INVOICE_NO'] === lastInv) cut--;
            if (cut > 0) rawRows = rawRows.slice(0, cut); // if the whole page is one invoice, keep it
        }

        const journalMatches = await fetchJournalMatches(rawRows);
        let rows = mergeJournalMatches(rawRows, journalMatches);

        if (itemName) {
            const q = itemName.toUpperCase();
            rows = rows.filter((r) => String(r['ITEM_NAME'] || '').toUpperCase().indexOf(q) !== -1);
        }

        return { total: data.total, rawCount: rawRows.length, rows: rows };
    }

    /* ==================== MATCHING / COMPUTE ==================== */

    function computeTransactionAmount(row, currency, rate) {
        if (currency === 'PHP' || currency === 'USD') {
            return parseFloat(row['LINE_AMOUNT']);
        }
        if (currency === 'JPY') {
            const mst = parseFloat(row['LINE_AMOUNT_MST']);
            const r   = parseFloat(rate);
            if (!isNaN(mst) && !isNaN(r)) return Math.round(mst * r);
            return NaN;
        }
        return NaN;
    }

    function computeTotalJPY(row, currency, rate) {
        const mst = parseFloat(row['LINE_AMOUNT_MST']);
        if (currency === 'PHP' || currency === 'USD') {
            if (isNaN(mst)) return NaN;
            return Math.round(mst);
        }
        if (currency === 'JPY') {
            const r = parseFloat(rate);
            if (isNaN(mst) || isNaN(r)) return NaN;
            return mst * r;
        }
        return NaN;
    }

    function computeTotalPHP(row, currency, reportingRate) {
        if (currency === 'PHP') {
            const lineAmount = parseFloat(row['LINE_AMOUNT']);
            if (isNaN(lineAmount)) return NaN;
            return lineAmount;
        }
        if (currency === 'USD') {
            const txnAmount = parseFloat(row['transaction_amount']);
            const r = parseFloat(reportingRate);
            if (isNaN(txnAmount) || isNaN(r)) return NaN;
            return txnAmount * r;
        }
        if (currency === 'JPY') {
            const mst = parseFloat(row['LINE_AMOUNT_MST']);
            const r = parseFloat(reportingRate);
            if (isNaN(mst) || isNaN(r)) return NaN;
            return mst * r;
        }
        return NaN;
    }

    function computeUnitPriceJPY(row, accountingAmount) {
        const qty = parseFloat(row['PURCHQTY']);
        if (isNaN(qty) || qty === 0 || isNaN(accountingAmount)) return NaN;
        return accountingAmount / qty;
    }

    function computeUnitPricePHP(row, reportingAmount) {
        const qty = parseFloat(row['PURCHQTY']);
        if (isNaN(qty) || qty === 0 || isNaN(reportingAmount)) return NaN;
        return reportingAmount / qty;
    }

    // Finds a subset of `items` (array of {value, origIndex}) summing exactly
    // to `target` (integer — JPY has no decimals, so this is safe as-is;
    // for PHP/USD you'd scale everything to centavos first).
    function subsetSumExact(items, target) {
        if (target < 0) return null;
        if (target === 0) return [];

        const n = items.length;
        const dp = new Int32Array(target + 1).fill(-1);
        const prevSum = new Int32Array(target + 1).fill(-1);
        dp[0] = -2;

        for (let i = 0; i < n; i++) {
            const v = Math.round(items[i].value);
            if (v <= 0 || v > target) continue;
            for (let s = target; s >= v; s--) {
                if (dp[s] === -1 && dp[s - v] !== -1) {
                    dp[s] = i;
                    prevSum[s] = s - v;
                }
            }
        }

        if (dp[target] === -1) return null;

        const used = [];
        let s = target;
        while (s > 0) {
            const i = dp[s];
            used.push(items[i].origIndex);
            s = prevSum[s];
        }
        return used;
    }

    // Tries to partition ALL items across ALL targets exactly, by solving
    // targets one at a time (subset-sum DP) and removing used items as it goes.
    // Returns the FIRST valid assignment found.
    function findUniquePartition(items, targets) {
        const EPS = 0.01;
        const totalItems = items.reduce((a, b) => a + b.value, 0);
        const totalTargets = targets.reduce((a, b) => a + b, 0);
        if (Math.abs(totalItems - totalTargets) > EPS) return null;

        function permutations(arr) {
            if (arr.length <= 1) return [arr];
            const result = [];
            for (let i = 0; i < arr.length; i++) {
                const rest = arr.slice(0, i).concat(arr.slice(i + 1));
                permutations(rest).forEach((p) => result.push([arr[i]].concat(p)));
            }
            return result;
        }

        const targetIndices = targets.map((_, i) => i);
        const orders = targets.length <= 6
            ? permutations(targetIndices)
            : [targetIndices.slice().sort((a, b) => targets[b] - targets[a])];

        const indexedItems = items.map((it, i) => ({ value: it.value, origIndex: i }));

        for (const order of orders) {
            let remaining = indexedItems.slice();
            const assignment = new Array(items.length);
            let ok = true;

            for (const targetIdx of order) {
                const targetValue = Math.round(targets[targetIdx]);
                const used = subsetSumExact(remaining, targetValue);
                if (used === null) { ok = false; break; }

                const usedSet = new Set(used);
                used.forEach((origIndex) => { assignment[origIndex] = targetIdx; });
                remaining = remaining.filter((it) => !usedSet.has(it.origIndex));
            }

            if (ok && remaining.length === 0) return assignment;
        }

        return null;
    }

    // Blank journal columns + a visible marker, used when a group of PO lines
    // can't be uniquely matched to a journal voucher by amount — surfaced
    // instead of silently dropped or guessed.
    function unresolvedJournalRow() {
        const blank = {};
        JOURNAL_COLUMNS.forEach((col) => { blank[col] = null; });
        blank['ledger_journal_name'] = '⚠ Unresolved (multiple journal matches)';
        return blank;
    }

    function oneToOneMatch(items, targets) {
        const assign = new Array(items.length).fill(-1);
        items.forEach((it, i) => {
            const hits = [];
            targets.forEach((t, k) => {
                if (Math.abs(it.value - t) <= AMOUNT_EPS) hits.push(k);
            });
            if (hits.length !== 1) return;                 // line matches 0 or 2+ vouchers
            const k = hits[0];
            const claimants = items.filter((o) => Math.abs(o.value - targets[k]) <= AMOUNT_EPS);
            if (claimants.length !== 1) return;            // another line claims the same voucher
            assign[i] = k;
        });
        return assign;
    }

    function applyJournal(row, j, currency, rate, reportingRate, value) {
        JOURNAL_COLUMNS.forEach((col) => { row[col] = j[col]; });
        row['transaction_amount'] = value; // must be set before computeTotalPHP (USD uses it)
        row['accounting_amount']  = computeTotalJPY(row, currency, rate);
        row['reporting_amount']   = computeTotalPHP(row, currency, reportingRate);
        row['unit_price_jpy']     = computeUnitPriceJPY(row, row['accounting_amount']);
        row['unit_price_php']     = computeUnitPricePHP(row, row['reporting_amount']);
    }

    // Returns all group rows (matched or marked unresolved), or null if the
    // pre-pass can't decide, in which case the full partition logic runs.
    function tryOneToOnePass(groupRows, journalRows) {
        const currencies = Array.from(new Set(journalRows.map((j) => j['transaction_currency'])));
        let best = null, tie = false;

        currencies.forEach((currency) => {
            const jr = journalRows.filter((j) => j['transaction_currency'] === currency);
            const rate = jr[0]['exchange_rate'];
            const reportingRate = jr[0]['reporting_rate'];
            const items = groupRows.map((r) => ({ row: r, value: computeTransactionAmount(r, currency, rate) }));
            if (items.some((it) => isNaN(it.value))) return;

            const targets = jr.map((j) => parseFloat(j['transaction_amount']));
            const assign = oneToOneMatch(items, targets);
            const count = assign.filter((a) => a >= 0).length;
            if (count === 0) return;

            if (!best || count > best.count) {
                best = { count: count, currency: currency, jr: jr, rate: rate,
                         reportingRate: reportingRate, items: items, targets: targets, assign: assign };
                tie = false;
            } else if (count === best.count) {
                tie = true; // two currencies fit equally well, so don't guess
            }
        });

        if (!best || tie) return null;

        // Whatever wasn't paired 1-to-1 goes through the exact partition on the leftovers only.
        const usedTargets = new Set(best.assign.filter((a) => a >= 0));
        const remItems = [], remTargets = [];
        best.assign.forEach((a, i) => { if (a < 0) remItems.push(i); });
        best.targets.forEach((_, k) => { if (!usedTargets.has(k)) remTargets.push(k); });

        let remAssign = null;
        if (remItems.length > 0 && remTargets.length > 0 && remTargets.length <= 15) {
            remAssign = findUniquePartition(
                remItems.map((i) => best.items[i]),
                remTargets.map((k) => best.targets[k])
            );
        }

        return best.items.map((it, i) => {
            let k = best.assign[i];
            if (k < 0 && remAssign) k = remTargets[remAssign[remItems.indexOf(i)]];
            if (k >= 0) {
                applyJournal(it.row, best.jr[k], best.currency, best.rate, best.reportingRate, it.value);
                it.row['match_status'] = (best.assign[i] >= 0) ? 'Exact' : 'Combined';
            } else {
                const blank = unresolvedJournalRow();
                JOURNAL_COLUMNS.forEach((col) => { it.row[col] = blank[col]; });
                it.row['match_status'] = 'Unresolved';
            }
            return it.row;
        });
    }

    // Merges journal columns onto each row and returns ONLY the matched rows.
    // `matches` is keyed by INVOICE_NO -> ARRAY of journal rows (invoice-only
    // lookup). Grouping for the partition step stays invoice+department so
    // currency/rate resolution stays scoped correctly; a department with no
    // journal rows of its own falls back to the whole invoice's rows, which
    // can span multiple currencies — each currency present is tried as a
    // separate partition candidate rather than assuming the first one.
    function mergeJournalMatches(rows, matches) {
        const groups = new Map();
        rows.forEach((r) => {
            const key = r['INVOICE_NO'] + '||' + r['C_FUTANBUMONNM'];
            if (!groups.has(key)) groups.set(key, []);
            groups.get(key).push(r);
        });

        const matched = [];

        groups.forEach((groupRows) => {
            const invoice = groupRows[0]['INVOICE_NO'];
            const department = groupRows[0]['C_FUTANBUMONNM'];
            const allInvoiceRows = matches[invoice] || [];

            // 1) Exact department code (PO LINE_DEPARTMENT = journal department)
            const poDeptCode = String(groupRows[0]['LINE_DEPARTMENT'] || '').trim();
            let journalRows = poDeptCode === '' ? [] : allInvoiceRows.filter((j) => {
                return String(j['department'] || '').trim() === poDeptCode;
            });

            // 2) Fall back to the department name prefix match
            if (journalRows.length === 0) {
                journalRows = allInvoiceRows.filter((j) => {
                    const jd = (j['department_name'] || '').trim();
                    const d = (department || '').trim();
                    if (jd === '' || d === '') return false;
                    return d === jd || d.indexOf(jd) === 0 || jd.indexOf(d) === 0;
                });
            }

            // 3) Fall back to the whole invoice
            if (journalRows.length === 0) {
                journalRows = allInvoiceRows;
            }

            if (journalRows.length === 0) return; // no journal match at all — drop

            if (journalRows.length === 1) {
                const j = journalRows[0];
                groupRows.forEach((r) => {
                    JOURNAL_COLUMNS.forEach((col) => { r[col] = j[col]; });
                    r['transaction_amount'] = computeTransactionAmount(r, j['transaction_currency'], j['exchange_rate']);
                    r['accounting_amount']  = computeTotalJPY(r, j['transaction_currency'], j['exchange_rate']);
                    r['reporting_amount']   = computeTotalPHP(r, j['transaction_currency'], j['reporting_rate']);
                    r['unit_price_jpy']     = computeUnitPriceJPY(r, r['accounting_amount']);
                    r['unit_price_php']     = computeUnitPricePHP(r, r['reporting_amount']);
                    r['match_status']       = 'Exact';
                    matched.push(r);
                });
                return;
            }

            const pre = tryOneToOnePass(groupRows, journalRows);
            if (pre) {
                pre.forEach((r) => matched.push(r));
                return;
            }

            // Multiple journal rows for this group — may span multiple
            // currencies (especially via the invoice-wide fallback). Try
            // each currency present as a separate candidate instead of
            // assuming journalRows[0]'s currency for everything.
            const currenciesPresent = Array.from(new Set(journalRows.map((j) => j['transaction_currency'])));

            let winner = null;
            let ambiguousAcrossCurrencies = false;

            currenciesPresent.forEach((currency) => {
                const currencyJournalRows = journalRows.filter((j) => j['transaction_currency'] === currency);
                const rate = currencyJournalRows[0]['exchange_rate'];
                const reportingRate = currencyJournalRows[0]['reporting_rate'];

                const items = groupRows.map((r) => ({ row: r, value: computeTransactionAmount(r, currency, rate) }));
                const targets = currencyJournalRows.map((j) => parseFloat(j['transaction_amount']));
                const hasBadValue = items.some((it) => isNaN(it.value));
                const tooLargeToSolve = currencyJournalRows.length > 15; // targets side still capped — that's the real cost driver

                const assignment = (tooLargeToSolve || hasBadValue) ? null : findUniquePartition(items, targets);
                if (assignment) {
                    if (winner) {
                        ambiguousAcrossCurrencies = true; // more than one currency produces a valid split
                    } else {
                        winner = {
                            assignment: assignment,
                            items: items,
                            journalRows: currencyJournalRows,
                            currency: currency,
                            rate: rate,
                            reportingRate: reportingRate
                        };
                    }
                }
            });

            if (winner && !ambiguousAcrossCurrencies) {
                winner.items.forEach((it, i) => {
                    const j = winner.journalRows[winner.assignment[i]];
                    JOURNAL_COLUMNS.forEach((col) => { it.row[col] = j[col]; });
                    it.row['transaction_amount'] = it.value;
                    it.row['accounting_amount']  = computeTotalJPY(it.row, winner.currency, winner.rate);
                    it.row['reporting_amount']   = computeTotalPHP(it.row, winner.currency, winner.reportingRate);
                    it.row['unit_price_jpy']     = computeUnitPriceJPY(it.row, it.row['accounting_amount']);
                    it.row['unit_price_php']     = computeUnitPricePHP(it.row, it.row['reporting_amount']);
                    it.row['match_status']       = 'Combined';
                    matched.push(it.row);
                });
            } else {
                if (window.console && console.warn) {
                    console.warn('Unresolved journal match for invoice ' + invoice + ' / department ' + department, {
                        groupRows: groupRows,
                        journalRows: journalRows,
                        ambiguousAcrossCurrencies: ambiguousAcrossCurrencies
                    });
                }
                groupRows.forEach((r) => {
                    const blank = unresolvedJournalRow();
                    JOURNAL_COLUMNS.forEach((col) => { r[col] = blank[col]; });
                    r['match_status'] = 'Unresolved';
                    matched.push(r);
                });
            }
        });

        return matched;
    }

    /* ==================== RENDER ==================== */

    function getColumnLabel(col) {
        return COLUMN_LABELS[col] || col;
    }

    function getColumnSourceClass(col) {
        if (PO_COLUMNS.includes(col)) return 'col-po';
        if (WF_COLUMNS.includes(col)) return 'col-wf';
        return 'col-journal';
    }

    function formatMoney(value) {
        if (value === null || value === undefined || value === '' || isNaN(value)) return value;
        return Number(value).toLocaleString('en-US', { maximumFractionDigits: 2 });
    }

    function renderHead() {
        els.head.innerHTML = '<tr>' + DISPLAY_COLUMNS.map((c) => {
            const cls = getColumnSourceClass(c) + (AMOUNT_COLS.includes(c) ? ' cell-amount' : '');
            return `<th class="${cls}">${esc(getColumnLabel(c))}</th>`;
        }).join('') + '</tr>';
    }

    function cellHtml(col, value) {
        if (value === null || value === undefined || value === '') {
            return '<td><span class="cell-null">NULL</span></td>';
        }
        if (col === 'match_status') {
            return `<td><span class="nexus-badge ${MATCH_COLOR[value] || 'gray'}">${esc(value)}</span></td>`;
        }
        if (BADGE_NEUTRAL_COLS.includes(col)) {
            return `<td><span class="nexus-badge gray">${esc(value)}</span></td>`;
        }
        if (AMOUNT_COLS.includes(col)) {
            const strong = (col === 'LINE_AMOUNT') ? ' cell-strong' : '';
            return `<td class="cell-amount${strong}">${esc(formatMoney(value))}</td>`;
        }
        return `<td>${esc(value)}</td>`;
    }

    function rowHtml(row) {
        return '<tr>' + DISPLAY_COLUMNS.map((col) => cellHtml(col, row[col])).join('') + '</tr>';
    }

    function setStatus(text, color) {
        els.count.style.color = color || '';
        els.count.textContent = text;
    }

    function setState(html) {
        els.state.innerHTML = html;
    }

    const SPINNER = '<div class="spinner-border spinner-border-sm text-primary"></div>';

    function updateLoadStatus() {
        const done = currentOffset >= currentTotal;
        setStatus(
            done
                ? displayedCount + ' matched record(s)'
                : 'Showing ' + displayedCount + ' matched record(s) — scanned ' + currentOffset + ' of ' + currentTotal,
            'var(--success-text)'
        );
    }

    function renderEmptyState() {
        els.head.innerHTML = '';
        els.body.innerHTML = '';
        setState(EMPTY_HINT);
    }

    /* ==================== SEARCH / FILTER ==================== */

    // Pulls pages until enough matched rows are collected for one "load".
    async function fetchPO(isFreshSearch) {
        if (isLoading) return;

        const dateFrom = els.dateFrom.value;
        const dateTo = els.dateTo.value;
        const invoiceNo = els.invoiceNo.value;
        const itemName = els.itemName.value;

        if (!dateFrom || !dateTo) {
            setStatus('Please select both a date from and date to.', 'var(--danger-text)');
            return;
        }

        isLoading = true;
        const token = searchToken;
        let keepFilling = false;

        if (isFreshSearch) {
            setStatus('Loading...');
            els.body.innerHTML = '';
        }
        setState(SPINNER);

        let collected = 0;

        try {
            while (true) {
                const page = await fetchMatchedPage(dateFrom, dateTo, invoiceNo, itemName, currentOffset, PAGE_LIMIT);
                if (token !== searchToken) return;

                currentTotal = page.total;
                currentOffset += page.rawCount;

                if (page.rows.length > 0) {
                    els.body.insertAdjacentHTML('beforeend', page.rows.map(rowHtml).join(''));
                    displayedCount += page.rows.length;
                    collected += page.rows.length;
                }

                updateLoadStatus();

                if (page.rawCount === 0 || currentOffset >= currentTotal) break;
                if (collected >= MIN_MATCHED_PER_LOAD) break;
            }

            if (displayedCount === 0) {
                setStatus('No matching records found.');
                setState('No matching records found.');
                return;
            }

            setState(currentOffset >= currentTotal ? 'All records loaded' : '');
            keepFilling = true;
        } catch (err) {
            console.error('Failed to load PO records:', err);
            setStatus(err.message || 'Failed to load records. Please try again.', 'var(--danger-text)');
            setState('Failed to load records.');
        } finally {
            if (token === searchToken) isLoading = false;
        }

        // Fill the container if the loaded rows don't overflow it
        if (keepFilling && currentOffset < currentTotal && els.scroll.scrollHeight <= els.scroll.clientHeight) {
            fetchPO(false);
        }
    }

    function startSearch() {
        searchToken++;
        isLoading = false;
        currentOffset = 0;
        currentTotal = 0;
        displayedCount = 0;
        hasSearched = true;
        els.scroll.scrollTop = 0;
        renderHead();
        fetchPO(true);
    }

    function clearSearch() {
        searchToken++;
        isLoading = false;
        els.dateFrom.value = '';
        els.dateTo.value = '';
        els.invoiceNo.value = '';
        els.itemName.value = '';
        currentOffset = 0;
        currentTotal = 0;
        displayedCount = 0;
        hasSearched = false;
        setStatus('');
        renderEmptyState();
    }

    /* ==================== EXPORT ==================== */

    function csvEscape(value) {
        if (value === null || value === undefined) return '';
        const str = String(value);
        if (/[",\n\r]/.test(str)) {
            return '"' + str.replace(/"/g, '""') + '"';
        }
        return str;
    }

    function rowsToCsv(columns, rows) {
        const header = columns.map((c) => csvEscape(getColumnLabel(c))).join(',');
        const lines = rows.map((row) => columns.map((col) => csvEscape(row[col])).join(','));
        return [header].concat(lines).join('\r\n');
    }

    function downloadCsv(csvContent, filename) {
        const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    async function exportToCsv() {
        if (isExporting) return;

        const dateFrom = els.dateFrom.value;
        const dateTo = els.dateTo.value;
        const invoiceNo = els.invoiceNo.value;
        const itemName = els.itemName.value;

        if (!dateFrom || !dateTo) {
            setStatus('Please select both a date from and date to.', 'var(--danger-text)');
            return;
        }

        isExporting = true;
        const originalHtml = els.btnExport.innerHTML;
        els.btnExport.disabled = true;
        els.btnExport.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Exporting...';
        setStatus('Preparing export...');

        try {
            let offset = 0;
            let total = Infinity;
            const allRows = [];

            while (offset < total) {
                const page = await fetchMatchedPage(dateFrom, dateTo, invoiceNo, itemName, offset, EXPORT_PAGE_LIMIT);

                total = page.total;
                allRows.push.apply(allRows, page.rows);
                offset += page.rawCount;

                setStatus('Fetching records for export... (scanned ' + offset + ' of ' + total + ', ' + allRows.length + ' matched)');

                if (page.rawCount === 0) break;
            }

            if (allRows.length === 0) {
                setStatus('No matching records to export.');
                return;
            }

            const csv = rowsToCsv(DISPLAY_COLUMNS, allRows);
            const stamp = new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-');
            downloadCsv(csv, 'working_paper_export_' + stamp + '.csv');

            setStatus('Exported ' + allRows.length + ' record(s) to CSV.', 'var(--success-text)');
        } catch (err) {
            console.error('Failed to export PO records:', err);
            setStatus(err.message || 'Failed to export records. Please try again.', 'var(--danger-text)');
        } finally {
            isExporting = false;
            els.btnExport.disabled = false;
            els.btnExport.innerHTML = originalHtml;
        }
    }

    /* ==================== EVENTS ==================== */

    function bindEvents() {
        els.scroll.addEventListener('scroll', () => {
            const nearBottom = els.scroll.scrollTop + els.scroll.clientHeight >= els.scroll.scrollHeight - 100;
            if (nearBottom && hasSearched && !isLoading && currentOffset < currentTotal) fetchPO(false);
        });

        els.btnLoad.addEventListener('click', startSearch);
        els.btnClear.addEventListener('click', clearSearch);
        els.btnExport.addEventListener('click', exportToCsv);

        [els.dateFrom, els.dateTo, els.invoiceNo, els.itemName].forEach((el) => {
            el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') startSearch();
            });
        });
    }

    /* ==================== UTILS ==================== */

    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    /* ==================== INIT ==================== */

    document.addEventListener('DOMContentLoaded', function() {
        bindEvents();
    });
</script>

<?php include('../../reusable/footer.php'); ?>