<?php
session_start();
require "../config/database.php";
require "../includes/auth-check.php";
requireAdmin();

$pageTitle = "Admin Dashboard";
$cssFile = "admin";
?>
<?php require "../includes/header.php"; ?>
<div class="admin-wrap">

<?php require "../includes/sidebar-admin.php"; ?>

<div class="content">

    <div class="page-header">
        <h1><i class="fa-solid fa-home"></i> Dashboard Admin</h1>
        <div class="admin-box" style="background:#12204d;padding:12px 18px;border-radius:12px;">
            <i class="fa-solid fa-user"></i> <?= $_SESSION['user'] ?>
        </div>
    </div>

    <div class="card-grid">
        <a href="users.php" class="admin-card">
            <i class="fa-solid fa-user-check"></i>
            <h2>User</h2>
            <p>Kelola approval user</p>
        </a>
        <a href="muat.php" class="admin-card">
            <i class="fa-solid fa-box"></i>
            <h2>Muat</h2>
            <p>Kelola data muat barang</p>
        </a>
        <a href="bongkar.php" class="admin-card">
            <i class="fa-solid fa-warehouse"></i>
            <h2>Bongkar</h2>
            <p>Kelola data bongkar barang</p>
        </a>
        <a href="maintenance.php" class="admin-card">
            <i class="fa-solid fa-screwdriver-wrench"></i>
            <h2>Maintenance</h2>
            <p>Kelola data maintenance kendaraan</p>
        </a>
    </div>

    <div class="welcome-box">
        <h2>Selamat Datang Admin 👋</h2>
        <p>Sistem management AGWI TRANS siap digunakan. Kelola user, data muat, bongkar, maintenance, dan kendaraan dengan mudah.</p>
    </div>

</div>
</div>
</body>
</html>
