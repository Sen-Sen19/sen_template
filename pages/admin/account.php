<?php

/**
 * |--------------------------------------------------------------------------
 * | Account Management
 * |--------------------------------------------------------------------------
 * | Account table with search by ID / role, add, edit, and delete.
 * | Uses mock data for now — replace fetchAccounts() and the save/delete
 * | handlers with real API calls later.
 * |--------------------------------------------------------------------------
 */

$page_title = 'Account Management';
$page_css   = '../../reusable/main.css';
include('../../reusable/header.php');
include('../../reusable/navbar.php');
include('../../reusable/topbar.php');

?>

<!-- ==================== PAGE CONTENT ==================== -->

<main class="app-content is-fluid">

    <div class="mb-3">
        <h1 class="page-title">Account Management</h1>
        <p class="page-subtitle">Manage system users, roles, and access.</p>
    </div>

    <!-- Toolbar -->
    <div class="nexus-toolbar">

        <div class="nexus-search">
            <i class="bi bi-search"></i>
            <input type="text" id="searchId" class="form-control nexus-form-control" placeholder="Search ID no." autocomplete="off">
        </div>

        <select id="searchRole" class="form-select nexus-form-control">
            <option value="">All roles</option>
            <option value="Admin">Admin</option>
            <option value="Supervisor">Supervisor</option>
            <option value="Employee">Employee</option>
            <option value="Guest">Guest</option>
        </select>

        <button type="button" id="btnAdd" class="btn nexus-btn-primary">
            <i class="bi bi-plus-lg"></i>
            Add Account
        </button>

        <span class="nexus-toolbar-count" id="acctCount"></span>

    </div>

    <!-- Table -->
    <div class="nexus-card">
        <div class="nexus-table-scroll" id="acctScroll">
            <table class="nexus-table is-clickable">
                <thead>
                    <tr>
                        <th>ID No.</th>
                        <th>Full Name</th>
                        <th>Password</th>
                        <th>Role</th>
                        <th>Last Active</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody id="acctBody"></tbody>
            </table>
            <div class="nexus-state" id="acctState"></div>
        </div>
    </div>

</main>

<!-- ==================== ADD / EDIT MODAL ==================== -->

<div
    class="modal fade nexus-modal"
    id="acctModal"
    tabindex="-1"
    aria-labelledby="acctModalTitle"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <div>
                    <h5 class="modal-title" id="acctModalTitle">
                        Add Account
                    </h5>

                    <p class="nexus-modal-subtitle" id="acctModalSubtitle">
                        Create a new system account.
                    </p>
                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close">
                </button>

            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label for="fId" class="form-label">ID No.</label>
                    <input
                        type="text"
                        class="form-control nexus-form-control"
                        id="fId"
                        maxlength="20"
                        placeholder="Enter ID no.">
                </div>

                <div class="mb-3">
                    <label for="fName" class="form-label">Full Name</label>
                    <input
                        type="text"
                        class="form-control nexus-form-control"
                        id="fName"
                        maxlength="100"
                        placeholder="Enter full name">
                </div>

                <div class="mb-3">
                    <label for="fPassword" class="form-label">Password</label>
                    <input
                        type="password"
                        class="form-control nexus-form-control"
                        id="fPassword"
                        autocomplete="new-password"
                        placeholder="Enter password">
                    <div class="form-text" id="fPasswordHint"></div>
                </div>

                <div>
                    <label for="fRole" class="form-label">Role</label>
                    <select class="form-select nexus-form-control" id="fRole">
                        <option value="Admin">Admin</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Employee">Employee</option>
                        <option value="Guest">Guest</option>
                    </select>
                </div>

                <div class="nexus-form-error" id="fError"></div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn nexus-btn-danger me-auto"
                    id="btnDelete">
                    <i class="bi bi-trash"></i>
                    Delete
                </button>

                <button
                    type="button"
                    class="btn nexus-btn-secondary"
                    data-bs-dismiss="modal">
                    Cancel
                </button>

                <button
                    type="button"
                    class="btn nexus-btn-primary"
                    id="btnSave">
                    <i class="bi bi-check2"></i>
                    Save
                </button>

            </div>

        </div>

    </div>

</div>

<script>
    /* ==================== CONFIG ==================== */

    const BATCH_SIZE = 15;
    const ROLES = ['Admin', 'Supervisor', 'Employee', 'Guest'];
    const ROLE_COLOR = { Admin: 'blue', Supervisor: 'yellow', Employee: 'green', Guest: 'gray' };

    const els = {
        scroll: document.getElementById('acctScroll'),
        body: document.getElementById('acctBody'),
        state: document.getElementById('acctState'),
        count: document.getElementById('acctCount'),
        searchId: document.getElementById('searchId'),
        searchRole: document.getElementById('searchRole'),
        btnAdd: document.getElementById('btnAdd'),
        title: document.getElementById('acctModalTitle'),
        subtitle: document.getElementById('acctModalSubtitle'),
        fId: document.getElementById('fId'),
        fName: document.getElementById('fName'),
        fPassword: document.getElementById('fPassword'),
        fPasswordHint: document.getElementById('fPasswordHint'),
        fRole: document.getElementById('fRole'),
        fError: document.getElementById('fError'),
        btnSave: document.getElementById('btnSave'),
        btnDelete: document.getElementById('btnDelete')
    };

    let modal = null;
    let accounts = [];
    let filtered = [];
    let rendered = 0;
    let isLoading = false;
    let editingId = null; // null = adding

    /* ==================== MOCK DATA ==================== */

    // Imaginary data — remove when the real endpoint is ready
    function buildMockAccounts() {
        const first = ['Juan', 'Maria', 'Jose', 'Ana', 'Mark', 'Grace', 'Paolo', 'Liza', 'Carlo', 'Joy', 'Ramon', 'Bea', 'Noel', 'Cathy', 'Dante'];
        const last = ['Santos', 'Reyes', 'Cruz', 'Bautista', 'Garcia', 'Mendoza', 'Torres', 'Flores', 'Ramos', 'Aquino', 'Villanueva', 'Castillo'];
        const list = [];
        let seed = 7;
        const rnd = (n) => {
            seed = (seed * 9301 + 49297) % 233280;
            return Math.floor((seed / 233280) * n);
        };

        for (let i = 0; i < 120; i++) {
            const role = i < 3 ? 'Admin' : ROLES[rnd(4)];
            const d = new Date(2026, 9, 2, 8, 0, 0);
            d.setMinutes(d.getMinutes() - rnd(60 * 24 * 30));
            list.push({
                id_no: String(10001 + i),
                full_name: first[rnd(first.length)] + ' ' + last[rnd(last.length)],
                password: '********',
                role: role,
                last_active: d,
                ip_address: '192.168.' + (1 + rnd(5)) + '.' + (10 + rnd(200))
            });
        }
        return list.sort((a, b) => b.last_active - a.last_active);
    }

    /* ==================== FETCH / API ==================== */

    // Swap this body for a fetch() to your PHP endpoint later
    function fetchAccounts(offset, limit) {
        return new Promise((resolve) => {
            setTimeout(() => resolve(filtered.slice(offset, offset + limit)), 180);
        });
    }

    /* ==================== RENDER ==================== */

    function rowHtml(a) {
        return `<tr data-id="${esc(a.id_no)}">
            <td class="cell-strong">${esc(a.id_no)}</td>
            <td>${esc(a.full_name)}</td>
            <td class="cell-mono">••••••••</td>
            <td><span class="nexus-badge ${ROLE_COLOR[a.role] || 'gray'}">${esc(a.role)}</span></td>
            <td>${fmtDate(a.last_active)}</td>
            <td class="cell-mono">${esc(a.ip_address)}</td>
        </tr>`;
    }

    async function loadNextBatch() {
        if (isLoading || rendered >= filtered.length) return;
        isLoading = true;
        els.state.innerHTML = '<div class="spinner-border spinner-border-sm text-primary"></div>';

        const batch = await fetchAccounts(rendered, BATCH_SIZE);
        els.body.insertAdjacentHTML('beforeend', batch.map(rowHtml).join(''));
        rendered += batch.length;

        isLoading = false;
        updateState();

        // Fill the container if the first batch doesn't overflow it
        if (els.scroll.scrollHeight <= els.scroll.clientHeight && rendered < filtered.length) {
            loadNextBatch();
        }
    }

    function updateState() {
        els.count.textContent = `Showing ${rendered} of ${filtered.length}`;
        els.state.textContent = filtered.length === 0 ? 'No accounts found.' : '';
    }

    function resetTable() {
        els.body.innerHTML = '';
        rendered = 0;
        els.scroll.scrollTop = 0;
        loadNextBatch();
    }

    /* ==================== SEARCH / FILTER ==================== */

    function applyFilters() {
        const q = els.searchId.value.trim().toLowerCase();
        const role = els.searchRole.value;

        filtered = accounts.filter((a) =>
            (!q || a.id_no.toLowerCase().includes(q)) &&
            (!role || a.role === role)
        );
        resetTable();
    }

    /* ==================== EVENTS ==================== */

    function bindEvents() {
        els.scroll.addEventListener('scroll', () => {
            const nearBottom = els.scroll.scrollTop + els.scroll.clientHeight >= els.scroll.scrollHeight - 100;
            if (nearBottom) loadNextBatch();
        });

        let timer;
        els.searchId.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(applyFilters, 200);
        });
        els.searchRole.addEventListener('change', applyFilters);

        els.btnAdd.addEventListener('click', () => openModal(null));

        els.body.addEventListener('click', (e) => {
            const tr = e.target.closest('tr[data-id]');
            if (tr) openModal(tr.dataset.id);
        });

        els.btnSave.addEventListener('click', saveAccount);
        els.btnDelete.addEventListener('click', deleteAccount);
    }

    /* ==================== EDIT / UPDATE ==================== */

    function openModal(id) {
        editingId = id;
        els.fError.textContent = '';
        els.fPassword.value = '';

        if (id === null) {
            els.title.textContent = 'Add Account';
            els.subtitle.textContent = 'Create a new system account.';
            els.fId.value = '';
            els.fId.readOnly = false;
            els.fName.value = '';
            els.fRole.value = 'Employee';
            els.fPasswordHint.textContent = '';
            els.btnDelete.classList.add('invisible');
        } else {
            const a = accounts.find((x) => x.id_no === id);
            if (!a) return;
            els.title.textContent = 'Edit Account';
            els.subtitle.textContent = 'Update the account information below.';
            els.fId.value = a.id_no;
            els.fId.readOnly = true;
            els.fName.value = a.full_name;
            els.fRole.value = a.role;
            els.fPasswordHint.textContent = 'Leave blank to keep the current password.';
            els.btnDelete.classList.remove('invisible');
        }
        modal.show();
    }

    function saveAccount() {
        const id = els.fId.value.trim();
        const name = els.fName.value.trim();
        const pass = els.fPassword.value;
        const role = els.fRole.value;

        if (!id || !name) return (els.fError.textContent = 'ID No. and Full Name are required.');
        if (editingId === null && !pass) return (els.fError.textContent = 'Password is required for new accounts.');
        if (editingId === null && accounts.some((a) => a.id_no === id)) return (els.fError.textContent = 'ID No. already exists.');

        if (editingId === null) {
            accounts.unshift({
                id_no: id,
                full_name: name,
                password: pass, // real system: hash server-side, never store plain text
                role: role,
                last_active: new Date(),
                ip_address: '—'
            });
        } else {
            const a = accounts.find((x) => x.id_no === editingId);
            a.full_name = name;
            a.role = role;
            if (pass) a.password = pass;
        }

        modal.hide();
        applyFilters();
        toast(editingId === null ? 'Account added.' : 'Account updated.');
    }

    /* ==================== DELETE ==================== */

    async function deleteAccount() {
        const id = editingId;
        let ok = false;

        if (window.Swal) {
            const res = await Swal.fire({
                title: 'Delete account?',
                text: 'ID ' + id + ' will be permanently removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e24b4a',
                confirmButtonText: 'Delete'
            });
            ok = res.isConfirmed;
        } else {
            ok = confirm('Delete account ' + id + '?');
        }
        if (!ok) return;

        accounts = accounts.filter((a) => a.id_no !== id);
        modal.hide();
        applyFilters();
        toast('Account deleted.');
    }

    /* ==================== UTILS ==================== */

    function esc(v) {
        return String(v).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    function fmtDate(d) {
        const p = (n) => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}`;
    }

    function toast(msg) {
        if (window.Swal) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: msg, showConfirmButton: false, timer: 1800 });
        }
    }

    /* ==================== INIT ==================== */

    document.addEventListener('DOMContentLoaded', function() {
        modal = new bootstrap.Modal(document.getElementById('acctModal'));
        accounts = buildMockAccounts();
        bindEvents();
        applyFilters();
    });
</script>

<?php include('../../reusable/footer.php'); ?>