<?php

/**
 * |--------------------------------------------------------------------------
 * | Account Management
 * |--------------------------------------------------------------------------
 * | Account table with search by ID / role, add, edit, and delete.
 * | Accounts come from sen_template_db.dbo.accounts and are joined with
 * | emp_mgt_db.dbo.m_employees (emp_no = id_no) by ../../api/admin/accounts.php.
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
            <option value="User">User</option>
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
                        <th>Department</th>
                        <th>Section</th>
                        <th>Position</th>
                        <th>Password</th>
                        <th>Role</th>
                        <th>Last Active</th>
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
                        Enter the ID No. to load the employee.
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

                <!-- Photo on the left, ID / name / details beside it -->
                <div class="acct-profile">

                    <div class="acct-photo-frame">
                        <img id="photoImg" alt="Employee photo" hidden>
                        <i class="bi bi-person" id="photoPlaceholder"></i>
                    </div>

                    <div class="acct-profile-info">

                        <label for="fId" class="form-label">ID No.</label>
                        <input
                            type="text"
                            class="form-control nexus-form-control"
                            id="fId"
                            maxlength="30"
                            autocomplete="off"
                            placeholder="Enter ID no. (example: 24-11114)">

                        <div class="acct-profile-name" id="pName">—</div>
                        <div class="acct-profile-sub" id="pSub"></div>

                        <!-- Employee details (add only) -->
                        <div class="acct-details" id="acctDetails" hidden>
                            <div>
                                <span class="acct-detail-label">Department</span>
                                <span class="acct-detail-value" id="dDept">—</span>
                            </div>
                            <div>
                                <span class="acct-detail-label">Section</span>
                                <span class="acct-detail-value" id="dSection">—</span>
                            </div>
                            <div>
                                <span class="acct-detail-label">Position</span>
                                <span class="acct-detail-value" id="dPosition">—</span>
                            </div>
                            <div>
                                <span class="acct-detail-label">Provider</span>
                                <span class="acct-detail-value" id="dProvider">—</span>
                            </div>
                        </div>

                    </div>

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
                        <option value="User">User</option>
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

<style>
/* ==================== PROFILE ==================== */

.acct-profile {
  display: flex;
  align-items: flex-start;
  gap: 18px;
  margin-bottom: 18px;
  padding-bottom: 18px;
  border-bottom: 1px solid #f0f0f0;
}

.acct-photo-frame {
  width: 96px;
  height: 96px;
  flex: 0 0 96px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border: 1px solid #e0e0e0;
  border-radius: 14px;
  background: #f5f5f7;
}

.acct-photo-frame img {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: cover;
}

.acct-photo-frame i {
  color: #9a9a9f;
  font-size: 40px;
}

/* keep the hidden attribute working on elements that set display */
.acct-photo-frame [hidden],
.acct-details[hidden] {
  display: none !important;
}

.acct-profile-info {
  flex: 1 1 auto;
  min-width: 0;
}

.acct-profile-name {
  margin-top: 12px;
  color: #1d1d1f;
  font-size: 15px;
  font-weight: 500;
  line-height: 1.3;
  overflow-wrap: anywhere;
}

.acct-profile-sub {
  margin-top: 2px;
  color: #6e6e73;
  font-size: 12px;
}

.acct-profile-sub:empty {
  display: none;
}

.acct-profile-sub.is-error {
  color: #e24b4a;
}

/* ==================== DETAILS ==================== */

.acct-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px 14px;
  margin-top: 12px;
}

.acct-detail-label {
  display: block;
  color: #9a9a9f;
  font-size: 10px;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.acct-detail-value {
  display: block;
  margin-top: 2px;
  color: #1d1d1f;
  font-size: 13px;
  overflow-wrap: anywhere;
}

/* ==================== RESPONSIVE ==================== */

@media (max-width: 575.98px) {
  .acct-profile {
    gap: 14px;
  }

  .acct-photo-frame {
    width: 72px;
    height: 72px;
    flex-basis: 72px;
  }

  .acct-photo-frame i {
    font-size: 30px;
  }

  .acct-details {
    grid-template-columns: 1fr;
  }
}
</style>

<script>
    /* ==================== CONFIG ==================== */

    const API_URL = '../../api/admin/accounts.php';
    const IMG_URL = '../../api/admin/employee_image.php';
    const BATCH_SIZE = 15;
    const ROLE_COLOR = { Admin: 'blue', User: 'green' };
    const ADD_HINT = 'Enter the ID No. above and press Enter.';

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
        fPassword: document.getElementById('fPassword'),
        fPasswordHint: document.getElementById('fPasswordHint'),
        fRole: document.getElementById('fRole'),
        fError: document.getElementById('fError'),
        btnSave: document.getElementById('btnSave'),
        btnDelete: document.getElementById('btnDelete'),
        photoImg: document.getElementById('photoImg'),
        photoPlaceholder: document.getElementById('photoPlaceholder'),
        pName: document.getElementById('pName'),
        pSub: document.getElementById('pSub'),
        details: document.getElementById('acctDetails'),
        dDept: document.getElementById('dDept'),
        dSection: document.getElementById('dSection'),
        dPosition: document.getElementById('dPosition'),
        dProvider: document.getElementById('dProvider')
    };

    let modal = null;
    let rows = [];        // rows loaded so far (used by the edit modal)
    let total = null;     // total matching rows on the server (null = not loaded yet)
    let rendered = 0;
    let isLoading = false;
    let loadToken = 0;    // ignores late responses after the filters change
    let editingId = null; // null = adding

    let photoToken = 0;       // ignores late photo responses
    let lookupToken = 0;      // ignores late employee lookups
    let lookupId = null;      // last ID that finished a lookup
    let lookupPending = null; // ID currently being looked up
    let lookupOk = false;     // employee found for lookupId
    let lookupHasAccount = false;

    /* ==================== FETCH / API ==================== */

    async function fetchAccounts(offset, limit) {
        const params = new URLSearchParams({
            action: 'list',
            offset: offset,
            limit: limit,
            q: els.searchId.value.trim(),
            role: els.searchRole.value
        });
        const res = await fetch(API_URL + '?' + params.toString());
        return res.json();
    }

    async function fetchEmployee(empNo) {
        try {
            const res = await fetch(API_URL + '?' + new URLSearchParams({ action: 'employee', emp_no: empNo }).toString());
            return await res.json();
        } catch (e) {
            return { status: false, message: 'Could not load employee details.' };
        }
    }

    async function postApi(action, data) {
        const fd = new FormData();
        fd.append('action', action);
        Object.entries(data).forEach(([k, v]) => fd.append(k, v));
        try {
            const res = await fetch(API_URL, { method: 'POST', body: fd });
            return await res.json();
        } catch (e) {
            return { status: false, message: 'Request failed. Please try again.' };
        }
    }

    /* ==================== RENDER ==================== */

    function rowHtml(a) {
        return `<tr data-id="${esc(a.id_no)}">
            <td class="cell-strong">${esc(a.id_no)}</td>
            <td>${esc(a.full_name)}</td>
            <td>${esc(a.dept || '—')}</td>
            <td>${esc(a.section || '—')}</td>
            <td>${esc(a.position || '—')}</td>
            <td class="cell-mono">••••••••</td>
            <td><span class="nexus-badge ${ROLE_COLOR[a.role] || 'gray'}">${esc(a.role)}</span></td>
            <td title="${esc(a.last_active || '')}">${fmtAgo(a.last_active_ago)}</td>
        </tr>`;
    }

    async function loadNextBatch() {
        if (isLoading || (total !== null && rendered >= total)) return;
        const token = loadToken;
        isLoading = true;
        els.state.innerHTML = '<div class="spinner-border spinner-border-sm text-primary"></div>';

        let res;
        try {
            res = await fetchAccounts(rendered, BATCH_SIZE);
        } catch (e) {
            res = { status: false, message: 'Could not load accounts.' };
        }
        if (token !== loadToken) return; // filters changed while loading

        if (!res.status) {
            isLoading = false;
            els.state.textContent = res.message || 'Could not load accounts.';
            return;
        }

        total = res.total;
        rows.push(...res.data);
        els.body.insertAdjacentHTML('beforeend', res.data.map(rowHtml).join(''));
        rendered += res.data.length;

        isLoading = false;
        updateState();

        // Fill the container if the first batch doesn't overflow it
        if (res.data.length && els.scroll.scrollHeight <= els.scroll.clientHeight && rendered < total) {
            loadNextBatch();
        }
    }

    function updateState() {
        els.count.textContent = `Showing ${rendered} of ${total}`;
        els.state.textContent = total === 0 ? 'No accounts found.' : '';
    }

    function resetTable() {
        loadToken++;
        isLoading = false;
        rows = [];
        total = null;
        rendered = 0;
        els.body.innerHTML = '';
        els.scroll.scrollTop = 0;
        loadNextBatch();
    }

    /* ==================== SEARCH / FILTER ==================== */

    function applyFilters() {
        resetTable();
    }

    /* ==================== PHOTO ==================== */

    function resetPhoto() {
        photoToken++;
        els.photoImg.hidden = true;
        els.photoImg.removeAttribute('src');
        els.photoPlaceholder.hidden = false;
    }

    function loadPhoto(id) {
        if (!id) return resetPhoto();

        const token = ++photoToken;
        const probe = new Image();
        probe.onload = () => {
            if (token !== photoToken) return;
            els.photoImg.src = probe.src;
            els.photoImg.hidden = false;
            els.photoPlaceholder.hidden = true;
        };
        probe.onerror = () => {
            if (token !== photoToken) return;
            els.photoImg.hidden = true;
            els.photoPlaceholder.hidden = false;
        };
        probe.src = IMG_URL + '?id_no=' + encodeURIComponent(id);
    }

    /* ==================== EMPLOYEE LOOKUP ==================== */

    function setProfile(name, sub, isError) {
        els.pName.textContent = name;
        els.pSub.textContent = sub || '';
        els.pSub.classList.toggle('is-error', !!isError);
    }

    function showDetails(emp) {
        els.dDept.textContent = emp.dept || '—';
        els.dSection.textContent = emp.section || '—';
        els.dPosition.textContent = emp.position || '—';
        els.dProvider.textContent = emp.provider || '—';
        els.details.hidden = false;
    }

    function resetProfile(sub) {
        resetPhoto();
        els.details.hidden = true;
        setProfile('—', sub || '');
        lookupToken++;
        lookupId = null;
        lookupPending = null;
        lookupOk = false;
        lookupHasAccount = false;
    }

    async function lookupEmployee(id) {
        if (!id) return resetProfile(ADD_HINT);

        const token = ++lookupToken;
        lookupPending = id;
        setProfile('Loading…', '');
        els.details.hidden = true;
        loadPhoto(id);

        const res = await fetchEmployee(id);
        if (token !== lookupToken) return;

        lookupPending = null;
        lookupId = id;

        if (!res.status) {
            lookupOk = false;
            lookupHasAccount = false;
            resetPhoto();
            els.details.hidden = true;
            setProfile('—', res.message || 'Employee not found.', true);
            return;
        }

        lookupOk = true;
        lookupHasAccount = !!res.has_account;
        setProfile(
            res.data.full_name || '—',
            lookupHasAccount ? 'This ID already has an account.' : '',
            lookupHasAccount
        );
        showDetails(res.data);
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
            timer = setTimeout(applyFilters, 250);
        });
        els.searchRole.addEventListener('change', applyFilters);

        els.btnAdd.addEventListener('click', () => openModal(null));

        // Add mode: load the employee when the ID is entered (Enter key or leaving the field)
        const lookupFromField = () => {
            if (els.fId.readOnly) return;
            const id = els.fId.value.trim();
            if (id && (id === lookupId || id === lookupPending)) return;
            lookupEmployee(id);
        };
        els.fId.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                lookupFromField();
            }
        });
        els.fId.addEventListener('blur', lookupFromField);

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
            els.subtitle.textContent = 'Enter the ID No. to load the employee.';
            els.fId.value = '';
            els.fId.readOnly = false;
            els.fRole.value = 'User';
            els.fPasswordHint.textContent = '';
            els.btnDelete.classList.add('invisible');
            resetProfile(ADD_HINT);
        } else {
            const a = rows.find((x) => x.id_no === id);
            if (!a) return;
            els.title.textContent = 'Edit Account';
            els.subtitle.textContent = 'You can change the password and role.';
            els.fId.value = a.id_no;
            els.fId.readOnly = true;
            els.fRole.value = a.role;
            if (els.fRole.value !== a.role) els.fRole.value = 'User'; // old role not in the list
            els.fPasswordHint.textContent = 'Leave blank to keep the current password.';
            els.btnDelete.classList.remove('invisible');
            resetProfile('');
            setProfile(a.full_name || '—', '');
            loadPhoto(a.id_no);
        }
        modal.show();
    }

    async function saveAccount() {
        const adding = editingId === null;
        const id = adding ? els.fId.value.trim() : editingId;
        const pass = els.fPassword.value;
        const role = els.fRole.value;

        if (!id) return (els.fError.textContent = 'ID No. is required.');
        if (adding && !pass) return (els.fError.textContent = 'Password is required for new accounts.');

        els.fError.textContent = '';
        els.btnSave.disabled = true;

        if (adding) {
            // The lookup may still be running if Save was clicked right after typing
            if (lookupId !== id) await lookupEmployee(id);
            if (!lookupOk) {
                els.fError.textContent = 'Employee not found. Check the ID No.';
                els.btnSave.disabled = false;
                return;
            }
            if (lookupHasAccount) {
                els.fError.textContent = 'This ID already has an account.';
                els.btnSave.disabled = false;
                return;
            }
        }

        const res = await postApi(adding ? 'create' : 'update', {
            id_no: id,
            password: pass,
            role: role
        });
        els.btnSave.disabled = false;

        if (!res.status) {
            els.fError.textContent = res.message || 'Could not save the account.';
            return;
        }

        modal.hide();
        applyFilters();
        toast(res.message);
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

        const res = await postApi('delete', { id_no: id });

        if (!res.status) {
            els.fError.textContent = res.message || 'Could not delete the account.';
            return;
        }

        modal.hide();
        applyFilters();
        toast(res.message);
    }

    /* ==================== UTILS ==================== */

    function esc(v) {
        return String(v).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    // "Active now" within 2 minutes, then "3 mins ago", "1 hour ago", "3 days ago"...
    function fmtAgo(seconds) {
        if (seconds === null || seconds === undefined) return '—';

        const mins = Math.floor(seconds / 60);
        if (mins <= 2) return '<span class="text-success fw-semibold">Active now</span>';
        if (mins < 60) return `${mins} mins ago`;

        const hours = Math.floor(mins / 60);
        if (hours < 24) return `${hours} ${hours === 1 ? 'hour' : 'hours'} ago`;

        const days = Math.floor(hours / 24);
        if (days < 30) return `${days} ${days === 1 ? 'day' : 'days'} ago`;

        const months = Math.floor(days / 30);
        if (months < 12) return `${months} ${months === 1 ? 'month' : 'months'} ago`;

        const years = Math.floor(days / 365);
        return `${years} ${years === 1 ? 'year' : 'years'} ago`;
    }

    function toast(msg) {
        if (window.Swal) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: msg, showConfirmButton: false, timer: 1800 });
        }
    }

    /* ==================== INIT ==================== */

    document.addEventListener('DOMContentLoaded', function() {
        modal = new bootstrap.Modal(document.getElementById('acctModal'));
        bindEvents();
        applyFilters();
    });
</script>

<?php include('../../reusable/footer.php'); ?>