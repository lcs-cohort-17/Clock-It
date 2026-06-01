<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = require __DIR__ . '/../data/MockUsers.php';
}

$users = &$_SESSION['users'];



/* -----------------------------
   ADD USER
------------------------------*/

if (isset($_POST['add_user'])) {

    $password = generatePassword();

  $users[] = [

        "id" => uniqid(),

        "name" => $_POST['name'],

        "email" => $_POST['email'],

        "employeeId" => generateEmployeeId(
            $_POST['role'],
            $users
        ),

        "role" => $_POST['role'],

        "status" => "Active",

        "password" => $password
    ];

    $_SESSION['generated_password'] = $password;
    $_SESSION['flash_success'] = 'User has been added successfully.';

    redirect_to('/admin-dashboard/users');
}

/* -----------------------------
   EDIT USER
------------------------------*/

if (isset($_POST['edit_user'])) {

    foreach ($users as &$u) {

        if ($u['id'] === $_POST['id']) {

            $u['name'] = $_POST['name'];

            $u['email'] = $_POST['email'];

            $u['role'] = $_POST['role'];
        }
    }

    $_SESSION['flash_success'] = 'User details have been updated.';

    redirect_to('/admin-dashboard/users');
}

/* -----------------------------
   TOGGLE STATUS
------------------------------*/

if (isset($_POST['toggle_status'])) {

    foreach ($users as &$u) {

        if ($u['id'] === $_POST['id']) {

            $u['status'] =
                $u['status'] === "Active"
                ? "Inactive"
                : "Active";
        }
    }

    $_SESSION['flash_success'] = 'User status has been updated.';

    redirect_to('/admin-dashboard/users');
}

/* -----------------------------
   RESET PASSWORD
------------------------------*/

if (isset($_POST['reset_password'])) {

    foreach ($users as &$u) {

        if ($u['id'] === $_POST['id']) {

            $newPassword = generatePassword();

            $u['password'] = $newPassword;

            $_SESSION['generated_password'] = $newPassword;
        }
    }

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
