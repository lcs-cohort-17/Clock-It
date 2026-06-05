<?php
session_start();

if (isset($_SESSION['generated_password'])) {
    unset($_SESSION['generated_password']);
}

header('Location: /');
exit;
