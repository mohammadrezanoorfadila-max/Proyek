<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';

// Redirect ke login kalau belum login
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function currentAdmin() {
    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'username' => $_SESSION['admin_username'] ?? '',
        'nama' => $_SESSION['admin_nama'] ?? ''
    ];
}