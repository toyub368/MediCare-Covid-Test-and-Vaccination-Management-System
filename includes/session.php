<?php
session_start();

function isLoggedIn($role) {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function requireLogin($role) {
    if (!isLoggedIn($role)) {
        header('Location: ../auth/login.php');
        exit();
    }
}

function logout() {
    session_destroy();
    header('Location: ../auth/login.php');
    exit();
}

function setUserSession($user_id, $role, $name, $email) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['role'] = $role;
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
}
?>