<?php
function requireLogin() {
    if (!isset($_SESSION['user'])) {
        header("Location: ../index.html");
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        echo "Akses ditolak!";
        exit;
    }
}
