<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['users']) || !is_array($_SESSION['users']) || $_SESSION['users'] === []) {
    $_SESSION['users'] = require __DIR__ . '/../Data/MockUsers.php';
}

$users = &$_SESSION['users'];

require_once dirname(__DIR__, 3) . '/phpbackend/src/config/Database.php';

if ($hasLegacyDemoIds !== []) {
    $_SESSION['users'] = require __DIR__ . '/../Data/MockUsers.php';
    $users = &$_SESSION['users'];
}

// Load users from DB and map to frontend shape
if (!function_exists('getDatabaseUsers')) {
    function getDatabaseUsers($db) {
        $stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
        $dbUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($dbUsers as $row) {
            $fullName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            if ($fullName === '') {
                $fullName = 'Unnamed User';
            }
            
            $role = ucfirst(strtolower($row['role'] ?? 'staff'));
            $status = ((int)($row['is_active'] ?? 1) === 1) ? 'Active' : 'Inactive';
            
            $users[] = [
                'id' => (string)$row['user_id'],
                'name' => $fullName,
                'email' => $row['email'] ?? '',
                'employeeId' => $row['employee_id'] ?? '',
                'role' => $role,
                'status' => $status,
                'password' => $row['password'] ?? ''
            ];
        }
        return $users;
    }
}

$users = getDatabaseUsers($db);

/* -----------------------------
   ADD USER
------------------------------*/

if (isset($_POST['add_user'])) {
    $password = generatePassword();
    $role = $_POST['role'] ?? 'Staff';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';

    // Split name into first and last
    $parts = explode(' ', trim($name), 2);
    $firstName = $parts[0] ?? '';
    $lastName = $parts[1] ?? '';

    $employeeId = generateEmployeeId($role, $users);

    try {
        $insertStmt = $db->prepare("
            INSERT INTO users (first_name, last_name, employee_id, role, is_active, email, password, must_change_password)
            VALUES (:first_name, :last_name, :employee_id, :role, 1, :email, :password, 1)
        ");
        $insertStmt->execute([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'employee_id'=> $employeeId,
            'role'       => strtolower($role),
            'email'      => $email,
            'password'   => password_hash($password, PASSWORD_BCRYPT)
        ]);

        $_SESSION['generated_password'] = $password;
        $_SESSION['flash_success'] = 'User has been added successfully.';
    } catch (\PDOException $e) {
        if ($e->getCode() === '23000' || strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
            $_SESSION['flash_error'] = 'Failed to add user: A user with this email address already exists.';
        } else {
            $_SESSION['flash_error'] = 'Failed to add user: ' . $e->getMessage();
        }
    }

    redirect_to('/admin-dashboard/users');
}

/* -----------------------------
   EDIT USER
------------------------------*/

if (isset($_POST['edit_user'])) {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'Staff';

    // Split name into first and last
    $parts = explode(' ', trim($name), 2);
    $firstName = $parts[0] ?? '';
    $lastName = $parts[1] ?? '';

    try {
        $updateStmt = $db->prepare("
            UPDATE users
            SET first_name = :first_name,
                last_name = :last_name,
                email = :email,
                role = :role
            WHERE user_id = :id
        ");
        $updateStmt->execute([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'role' => strtolower($role),
            'id' => $id
        ]);

        $_SESSION['flash_success'] = 'User details have been updated.';
    } catch (\PDOException $e) {
        if ($e->getCode() === '23000' || strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
            $_SESSION['flash_error'] = 'Failed to update user: A user with this email address already exists.';
        } else {
            $_SESSION['flash_error'] = 'Failed to update user: ' . $e->getMessage();
        }
    }

    redirect_to('/admin-dashboard/users');
}

/* -----------------------------
   TOGGLE STATUS
------------------------------*/

if (isset($_POST['toggle_status'])) {
    $id = $_POST['id'] ?? '';

    $statusStmt = $db->prepare("SELECT is_active FROM users WHERE user_id = :id");
    $statusStmt->execute(['id' => $id]);
    $currentActive = $statusStmt->fetchColumn();

    if ($currentActive !== false) {
        $newActive = ((int)$currentActive === 1) ? 0 : 1;
        $updateStmt = $db->prepare("UPDATE users SET is_active = :is_active WHERE user_id = :id");
        $updateStmt->execute([
            'is_active' => $newActive,
            'id' => $id
        ]);
        $_SESSION['flash_success'] = 'User status has been updated.';
    }

    redirect_to('/admin-dashboard/users');
}

/* -----------------------------
   RESET PASSWORD
------------------------------*/

if (isset($_POST['reset_password'])) {
    $id = $_POST['id'] ?? '';
    $newPassword = generatePassword();

    // Set must_change_password = 1 so the user is forced to change on next login
    $updateStmt = $db->prepare("UPDATE users SET password = :password, must_change_password = 1 WHERE user_id = :id");
    $updateStmt->execute([
        'password' => password_hash($newPassword, PASSWORD_BCRYPT),
        'id' => $id
    ]);

    $_SESSION['generated_password'] = $newPassword;
    $_SESSION['flash_success'] = 'Password has been reset.';

    $_SESSION['flash_success'] = 'A temporary password has been generated.';

    redirect_to('/admin-dashboard/users');
}

/* -----------------------------
   SEARCH
------------------------------*/

$query = strtolower($_GET['q'] ?? '');

$filtered = array_filter($users, function ($u) use ($query) {
    return !$query ||
        str_contains(
            strtolower($u['name']),
            $query
        ) ||
        str_contains(
            strtolower($u['email']),
            $query
        ) ||
        str_contains(
            strtolower($u['employeeId']),
            $query
        );
});
