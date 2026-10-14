<?php

$page_title = 'Dashboard';
$page_css   = '../../reusable/main.css';
include('../../reusable/header.php');
include('../../reusable/navbar.php');
include('../../reusable/topbar.php');

?>

<main class="app-content is-fluid">

    <div class="mb-3">
        <h1 class="page-title">Withdrawals</h1>
        <p class="page-subtitle">Daily withdrawal count per account.</p>
    </div>

    <!-- Toolbar -->
    <div class="nexus-toolbar">

        <div class="wd-month">
            <button type="button" id="btnPrev" class="btn nexus-btn-secondary" aria-label="Previous month">
                <i class="bi bi-chevron-left"></i>
            </button>
            <span id="monthLabel" class="wd-month-label">—</span>
            <button type="button" id="btnNext" class="btn nexus-btn-secondary" aria-label="Next month">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>

    

        <span class="nexus-toolbar-count" id="wdSummary"></span>

    </div>

    <!-- Grid -->
    <div class="nexus-card">
        <div class="nexus-table-scroll wd-scroll">
            <table class="nexus-table wd-table">
                <thead id="wdHead"></thead>
                <tbody id="wdBody"></tbody>
            </table>
            <div class="nexus-state" id="wdState"></div>
        </div>
    </div>

    <div class="wd-legend">
        <span><i class="wd-swatch l1"></i> 1</span>
        <span><i class="wd-swatch l2"></i> 2–3</span>
        <span><i class="wd-swatch l3"></i> 4–6</span>
        <span><i class="wd-swatch l4"></i> 7+</span>
        <span><i class="wd-swatch wk"></i> Weekend</span>
        <span>· none</span>
    </div>

</main>

<style>
.wd-month { display: flex; align-items: center; gap: 8px; }
.wd-month-label { min-width: 130px; text-align: center; font-weight: 500; color: #1d1d1f; }

.wd-scroll { max-height: calc(100vh - 290px); overflow: auto; }

.wd-table { border-collapse: separate; border-spacing: 0; width: max-content; min-width: 100%; }
.wd-table th, .wd-table td { text-align: center; padding: 8px 6px; min-width: 38px; font-size: 13px; }

/* pinned account column */
.wd-table .wd-acct {
  position: sticky; left: 0; z-index: 2;
  min-width: 230px; text-align: left; background: #fff;
  border-right: 1px solid #f0f0f0; font-weight: 500; white-space: nowrap;
}
.wd-table thead th { position: sticky; top: 0; z-index: 3; background: #fafafa; }
.wd-table thead th.wd-acct { z-index: 4; }

.wd-table .wd-total { min-width: 64px; font-weight: 600; border-left: 1px solid #f0f0f0; }

/* today / weekend / future */
.wd-table th.is-today { background: #0071e3; color: #fff; }
.wd-table td.is-today { box-shadow: inset 1px 0 0 #0071e3, inset -1px 0 0 #0071e3; }
.wd-table th.is-weekend, .wd-table td.is-weekend { background: #f5f5f7; }

.wd-zero { color: #c7c7cc; }

/* intensity (the number is always shown, so color is not the only signal) */
.wd-table td.l1 { background: #e6f4ea; color: #1e6b34; }
.wd-table td.l2 { background: #c3e6cb; color: #15532a; }
.wd-table td.l3 { background: #8fd3a1; color: #0f3d1f; }
.wd-table td.l4 { background: #4cb86b; color: #fff; font-weight: 600; }

.wd-legend { display: flex; flex-wrap: wrap; gap: 14px; margin-top: 10px; font-size: 12px; color: #6e6e73; }
.wd-swatch { display: inline-block; width: 12px; height: 12px; border-radius: 3px; vertical-align: -1px; }
.wd-swatch.l1 { background: #e6f4ea; } .wd-swatch.l2 { background: #c3e6cb; }
.wd-swatch.l3 { background: #8fd3a1; } .wd-swatch.l4 { background: #4cb86b; }
.wd-swatch.wk { background: #f5f5f7; border: 1px solid #e0e0e0; }
</style>

<script>
    /* ==================== MOCK DATA ==================== */

    const ACCOUNTS = [
        'MFAB', 'HFAB', 'Set Up (Machine Data)/SUB PC', 'MACHINERY',
        'Spare Parts-INITIAL LOCAL', 'Spare Parts-INITIAL IMPORTED', 'CALIBRATION',
        'Spare Parts-FINAL', 'FACILITY', 'PDD C/O BAE', 'COT/CMD-EE', 'IT', 'CQA',
        'PC', 'IMPEX', 'PRODUCTION', 'NF', 'MPPD', 'QA-QC', 'QP', 'TDC',
        'PACKAGING', 'WIRE PRODUCTION'
    ];

    // Seeded random so the fake numbers stay the same on every refresh
    function seeded(seed) {
        return function () {
            seed = (seed * 1664525 + 1013904223) % 4294967296;
            return seed / 4294967296;
        };
    }

    // Each account gets its own "busyness" so some are heavy and some are quiet
    function mockCounts(monthKey) {
        const [y, m] = monthKey.split('-').map(Number);
        const days = new Date(y, m, 0).getDate();
        const rand = seeded(y * 100 + m);
        const out = {};

        ACCOUNTS.forEach((a) => {
            const busy = rand();                     // 0..1
            const skipChance = 0.65 - busy * 0.5;    // quiet accounts skip more days
            out[a] = {};
            for (let d = 1; d <= days; d++) {
                const dow = new Date(y, m - 1, d).getDay();
                const weekend = dow === 0 || dow === 6;
                if (rand() < (weekend ? 0.92 : skipChance)) continue;
                out[a][d] = 1 + Math.floor(rand() * (2 + busy * 9));
            }
        });
        return out;
    }

    /* ==================== STATE ==================== */

const els = {
    head: document.getElementById('wdHead'),
    body: document.getElementById('wdBody'),
    state: document.getElementById('wdState'),
    summary: document.getElementById('wdSummary'),
    label: document.getElementById('monthLabel'),
    prev: document.getElementById('btnPrev'),
    next: document.getElementById('btnNext')
};

    const now = new Date();
    const TODAY = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
    let month = TODAY.slice(0, 7);

    /* ==================== RENDER ==================== */

    function level(n) {
        if (n >= 7) return 'l4';
        if (n >= 4) return 'l3';
        if (n >= 2) return 'l2';
        return 'l1';
    }

    function dayInfo(d) {
        const date = month + '-' + String(d).padStart(2, '0');
        const dow = new Date(date + 'T00:00:00').getDay();
        return {
            date: date,
            weekend: dow === 0 || dow === 6,
            today: date === TODAY,
            future: date > TODAY
        };
    }

    function render() {
        const [y, m] = month.split('-').map(Number);
        const days = new Date(y, m, 0).getDate();
        const counts = mockCounts(month);

        els.label.textContent = new Date(y, m - 1, 1).toLocaleString('en-US', { month: 'long', year: 'numeric' });
        els.next.disabled = month >= TODAY.slice(0, 7);

        // header
        let h = '<tr><th class="wd-acct">Account</th>';
        for (let d = 1; d <= days; d++) {
            const i = dayInfo(d);
            h += `<th class="${i.today ? 'is-today' : i.weekend ? 'is-weekend' : ''}">${d}</th>`;
        }
        els.head.innerHTML = h + '<th class="wd-total">Total</th></tr>';

        // rows
const list = ACCOUNTS;

        els.body.innerHTML = list.map((a) => {
            let total = 0;
            let tds = '';
            for (let d = 1; d <= days; d++) {
                const i = dayInfo(d);
                const n = counts[a][d] || 0;
                const base = (i.today ? 'is-today ' : '') + (i.weekend ? 'is-weekend ' : '');

                if (i.future) {
                    tds += `<td class="${base}"></td>`;
                } else if (n > 0) {
                    total += n;
                    tds += `<td class="${base}${level(n)}" title="${esc(a)} · ${i.date} · ${n} withdrawal${n === 1 ? '' : 's'}">${n}</td>`;
                } else {
                    tds += `<td class="${base}"><span class="wd-zero">·</span></td>`;
                }
            }
            return `<tr><td class="wd-acct">${esc(a)}</td>${tds}<td class="wd-total">${total}</td></tr>`;
        }).join('');



        // summary for today (only when viewing the current month)
        if (month === TODAY.slice(0, 7)) {
            const day = Number(TODAY.slice(8));
            let active = 0, sum = 0;
            ACCOUNTS.forEach((a) => {
                const n = counts[a][day] || 0;
                if (n > 0) active++;
                sum += n;
            });
            els.summary.textContent = `Today: ${active} of ${ACCOUNTS.length} accounts withdrew · ${sum} withdrawals`;
        } else {
            els.summary.textContent = '';
        }
    }

    /* ==================== EVENTS ==================== */

    function shiftMonth(delta) {
        const [y, m] = month.split('-').map(Number);
        const d = new Date(y, m - 1 + delta, 1);
        month = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
        render();
    }

    function esc(v) {
        return String(v).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    document.addEventListener('DOMContentLoaded', function () {
        els.prev.addEventListener('click', () => shiftMonth(-1));
        els.next.addEventListener('click', () => shiftMonth(1));

        render();
    });
</script>

<?php include('../../reusable/footer.php'); ?>