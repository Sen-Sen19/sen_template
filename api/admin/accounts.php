<?php
header('Content-Type: application/json');

session_start();

// Connections: $conn_sen (sen_template_db) and $conn_ems (emp_mgt_db)
require_once __DIR__ . '/../connection/template_conn.php';
require_once __DIR__ . '/../connection/ems_conn.php';

/* ==================== CONFIG ==================== */

const ACCOUNTS_TABLE  = '[sen_template_db].[dbo].[accounts]';
const EMPLOYEES_TABLE = '[emp_mgt_db].[dbo].[m_employees]';
const ALLOWED_ROLES   = ['Admin', 'User'];

/* ==================== UTILS ==================== */

function respond(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function dbFail(string $message = 'Database error.'): void
{
    error_log('accounts.php: ' . print_r(sqlsrv_errors(), true));
    respond(['status' => false, 'message' => $message], 500);
}

// Escape LIKE wildcards so "_" and "%" in the search are treated as plain text
function likeEscape(string $value): string
{
    return preg_replace('/[\\\\%_\[]/', '\\\\$0', $value);
}

// The employee database connection (the variable name set inside ems_conn.php)
function emsConn()
{
    // Use whichever SQL Server connection ems_conn.php created (anything except $conn_sen)
    foreach ($GLOBALS as $name => $value) {
        if ($name !== 'conn_sen' && is_resource($value) && get_resource_type($value) === 'SQL Server Connection') {
            return $value;
        }
    }
    return null;
}

function validEmpNo(string $value): bool
{
    return (bool)preg_match('/^[A-Za-z0-9_-]{1,30}$/', $value);
}

/* ==================== AUTH ==================== */

// Admins only — this endpoint can create, edit and delete accounts
if (empty($_SESSION['logged_in']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    respond(['status' => false, 'message' => 'Unauthorized.'], 403);
}

/* ==================== EMPLOYEES (m_employees) ==================== */

// Returns [lowercased emp_no => employee row], or null if the employee database can't be read
function fetchEmployees(array $empNos): ?array
{
    $conn = emsConn();
    if (!$conn) {
        error_log('accounts.php: $conn_ems is not defined by ems_conn.php');
        return null;
    }
    if (!$empNos) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($empNos), '?'));
    $sql = 'SELECT [emp_no], [full_name], [dept], [section], [position], [provider]
            FROM ' . EMPLOYEES_TABLE . "
            WHERE [emp_no] IN ($placeholders)";

    $stmt = sqlsrv_query($conn, $sql, array_values($empNos));
    if ($stmt === false) {
        error_log('accounts.php (employees): ' . print_r(sqlsrv_errors(), true));
        return null;
    }

    $map = [];
    while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $row = [];
        foreach ($r as $key => $value) {
            $row[$key] = is_string($value) ? trim($value) : $value;
        }
        $map[strtolower($row['emp_no'])] = $row;
    }
    sqlsrv_free_stmt($stmt);

    return $map;
}

function lookupEmployee($conn): void
{
    $empNo = trim($_GET['emp_no'] ?? '');
    if (!validEmpNo($empNo)) {
        respond(['status' => false, 'message' => 'Invalid ID No.'], 422);
    }

    $map = fetchEmployees([$empNo]);
    if ($map === null) {
        respond(['status' => false, 'message' => 'Could not read the employee database.'], 500);
    }

    $emp = $map[strtolower($empNo)] ?? null;
    if (!$emp) {
        respond(['status' => false, 'message' => 'Employee not found.'], 404);
    }

    // Tell the form early if this employee already has an account
    $stmt = sqlsrv_query($conn, 'SELECT 1 FROM ' . ACCOUNTS_TABLE . ' WHERE [id_no] = ?', [$emp['emp_no']]);
    if ($stmt === false) dbFail();
    $hasAccount = sqlsrv_fetch_array($stmt) !== null;
    sqlsrv_free_stmt($stmt);

    respond(['status' => true, 'data' => $emp, 'has_account' => $hasAccount]);
}

/* ==================== LIST ==================== */

function listAccounts($conn): void
{
    $q      = trim($_GET['q'] ?? '');
    $role   = trim($_GET['role'] ?? '');
    $offset = max(0, (int)($_GET['offset'] ?? 0));
    $limit  = min(100, max(1, (int)($_GET['limit'] ?? 15)));

    $where  = [];
    $params = [];

    if ($q !== '') {
        $where[]  = "[id_no] LIKE ? ESCAPE '\\'";
        $params[] = '%' . likeEscape($q) . '%';
    }
    if ($role !== '' && in_array($role, ALLOWED_ROLES, true)) {
        $where[]  = '[role] = ?';
        $params[] = $role;
    }
    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Total for the "Showing X of Y" counter
    $stmt = sqlsrv_query($conn, 'SELECT COUNT(*) AS total FROM ' . ACCOUNTS_TABLE . " $whereSql", $params);
    if ($stmt === false) dbFail();
    $total = (int)sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)['total'];
    sqlsrv_free_stmt($stmt);

    // One batch (the password is never sent to the browser)
    $sql = 'SELECT [id], [id_no], [full_name], [role], [last_active]
            FROM ' . ACCOUNTS_TABLE . "
            $whereSql
            ORDER BY [last_active] DESC, [id] DESC
            OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";

    $stmt = sqlsrv_query($conn, $sql, array_merge($params, [$offset, $limit]));
    if ($stmt === false) dbFail();

    // last_active is saved in Manila time, so measure "how long ago" in Manila time too
    $tz  = new DateTimeZone('Asia/Manila');
    $now = new DateTime('now', $tz);

    $data = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $ago = null;
        if ($row['last_active'] instanceof DateTime) {
            $seen = new DateTime($row['last_active']->format('Y-m-d H:i:s'), $tz);
            $ago  = max(0, $now->getTimestamp() - $seen->getTimestamp());
        }

        $data[] = [
            'id'          => $row['id'],
            'id_no'       => trim($row['id_no']),
            'full_name'   => $row['full_name'],
            'role'        => $row['role'],
            'last_active' => $row['last_active'] instanceof DateTime ? $row['last_active']->format('Y-m-d H:i') : null,
            'last_active_ago' => $ago, // seconds
            'dept'        => null,
            'section'     => null,
            'position'    => null,
            'provider'    => null,
        ];
    }
    sqlsrv_free_stmt($stmt);

    // Join with m_employees on emp_no (done in PHP because it is a separate connection)
    $employees = fetchEmployees(array_column($data, 'id_no')) ?? [];
    foreach ($data as &$item) {
        $emp = $employees[strtolower($item['id_no'])] ?? null;
        if ($emp) {
            $item['full_name'] = $emp['full_name'] ?: $item['full_name'];
            $item['dept']      = $emp['dept'];
            $item['section']   = $emp['section'];
            $item['position']  = $emp['position'];
            $item['provider']  = $emp['provider'];
        }
    }
    unset($item);

    respond(['status' => true, 'total' => $total, 'data' => $data]);
}

/* ==================== CREATE ==================== */

function createAccount($conn): void
{
    $idNo     = trim($_POST['id_no'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = trim($_POST['role'] ?? '');

    if ($idNo === '' || $password === '') {
        respond(['status' => false, 'message' => 'ID No. and Password are required.'], 422);
    }
    if (!validEmpNo($idNo)) {
        respond(['status' => false, 'message' => 'Invalid ID No.'], 422);
    }
    if (!in_array($role, ALLOWED_ROLES, true)) {
        respond(['status' => false, 'message' => 'Invalid role.'], 422);
    }

    // The employee must exist in m_employees; the name comes from there
    $map = fetchEmployees([$idNo]);
    if ($map === null) {
        respond(['status' => false, 'message' => 'Could not read the employee database.'], 500);
    }
    $emp = $map[strtolower($idNo)] ?? null;
    if (!$emp || $emp['full_name'] === '') {
        respond(['status' => false, 'message' => 'Employee not found.'], 404);
    }

    $stmt = sqlsrv_query($conn, 'SELECT 1 FROM ' . ACCOUNTS_TABLE . ' WHERE [id_no] = ?', [$emp['emp_no']]);
    if ($stmt === false) dbFail();
    $exists = sqlsrv_fetch_array($stmt) !== null;
    sqlsrv_free_stmt($stmt);
    if ($exists) {
        respond(['status' => false, 'message' => 'This ID already has an account.'], 409);
    }

    // Plain text to match how login.php compares passwords today
    $stmt = sqlsrv_query(
        $conn,
        'INSERT INTO ' . ACCOUNTS_TABLE . ' ([id_no], [full_name], [password], [role]) VALUES (?, ?, ?, ?)',
        [$emp['emp_no'], $emp['full_name'], $password, $role]
    );
    if ($stmt === false) dbFail();
    sqlsrv_free_stmt($stmt);

    respond(['status' => true, 'message' => 'Account added.']);
}

/* ==================== UPDATE ==================== */

function updateAccount($conn): void
{
    $idNo     = trim($_POST['id_no'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = trim($_POST['role'] ?? '');

    if ($idNo === '') {
        respond(['status' => false, 'message' => 'ID No. is required.'], 422);
    }
    if (!in_array($role, ALLOWED_ROLES, true)) {
        respond(['status' => false, 'message' => 'Invalid role.'], 422);
    }

    // Edit only changes the role (and the password when one is given)
    $set    = '[role] = ?';
    $params = [$role];

    // Blank password = keep the current one
    if ($password !== '') {
        $set     .= ', [password] = ?';
        $params[] = $password;
    }
    $params[] = $idNo;

    $stmt = sqlsrv_query($conn, 'UPDATE ' . ACCOUNTS_TABLE . " SET $set WHERE [id_no] = ?", $params);
    if ($stmt === false) dbFail();
    $affected = sqlsrv_rows_affected($stmt);
    sqlsrv_free_stmt($stmt);

    if ($affected === 0) {
        respond(['status' => false, 'message' => 'Account not found.'], 404);
    }
    respond(['status' => true, 'message' => 'Account updated.']);
}

/* ==================== DELETE ==================== */

function deleteAccount($conn): void
{
    $idNo = trim($_POST['id_no'] ?? '');

    if ($idNo === '') {
        respond(['status' => false, 'message' => 'ID No. is required.'], 422);
    }
    // Don't let an admin delete the account they are signed in with
    if ((string)($_SESSION['id_no'] ?? '') === $idNo) {
        respond(['status' => false, 'message' => 'You cannot delete your own account.'], 403);
    }

    $stmt = sqlsrv_query($conn, 'DELETE FROM ' . ACCOUNTS_TABLE . ' WHERE [id_no] = ?', [$idNo]);
    if ($stmt === false) dbFail();
    $affected = sqlsrv_rows_affected($stmt);
    sqlsrv_free_stmt($stmt);

    if ($affected === 0) {
        respond(['status' => false, 'message' => 'Account not found.'], 404);
    }
    respond(['status' => true, 'message' => 'Account deleted.']);
}

/* ==================== ROUTER ==================== */

$action = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['action'] ?? '') : ($_GET['action'] ?? '');

switch ($action) {
    case 'list':     listAccounts($conn_sen);   break;
    case 'employee': lookupEmployee($conn_sen); break;
    case 'create':   createAccount($conn_sen);  break;
    case 'update':   updateAccount($conn_sen);  break;
    case 'delete':   deleteAccount($conn_sen);  break;
    default:         respond(['status' => false, 'message' => 'Unknown action.'], 400);
}