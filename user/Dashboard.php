<?php
session_start();
require "../config/database.php";
require "../includes/auth-check.php";
requireLogin();
$pageTitle = "Dashboard AGWI TRANS";
$cssFile = "dashboard";
?>
<?php require "../includes/header.php"; ?>

<!-- HEADER -->

<div class="header">

    <div class="logo">

        <div class="logo-wrap">
            <img
                src="https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEisyaJDkkmnBrh0ELgT9cc3cQw70PEiMWusc086ztgoIVEdx-vvQU-WR8XWjAlEYX4YZB_6ArF7TVBjbNREumGxVZKXz5Tl_-Dzk4VJE_ocfgfwDgf6ChOtjNOC0UZzbMrsNHcWF6W50mHVXHAfAq2xPezGpDIvchFKMqTMdrMFLUON4vBb8inPSTvro3HG/s320/WhatsApp%20Image%202026-04-06%20at%2012.07.51%20(1).png"
                alt="Logo AGWI"
                class="logo-img"
            >

            <div>

                <h1>AGWI TRANS</h1>

                <p>
                    Sistem Monitoring Transportasi
                </p>

            </div>

        </div>

    </div>

    <div class="user-box">

        <div class="username">
            👤 <?= $_SESSION['user'] ?>
        </div>

        <a href="../auth/Logout.php" class="logout">
            Logout
        </a>

    </div>

</div>

<!-- CONTENT -->

<div class="container">

    <!-- WELCOME -->

    <div class="welcome">

        <h2>Dashboard</h2>

        <p>
            Selamat datang di sistem monitoring transportasi AGWI TRANS.
            Pilih menu di bawah untuk mengelola data muat barang,
            bongkar barang, dan maintenance kendaraan.
        </p>

    </div>

    <!-- MENU -->

    <div class="dashboard-menu">

        <!-- MUAT -->

        <a href="../user/muat.php" class="menu-card">

            <div class="icon">
                📦
            </div>

            <h3>Muat Barang</h3>

            <p>
                Kelola data muatan kendaraan dan aktivitas pengiriman barang.
            </p>

        </a>

        <!-- BONGKAR -->

        <a href="../user/bongkar.php" class="menu-card">

            <div class="icon">
                🚚
            </div>

            <h3>Bongkar Barang</h3>

            <p>
                Monitoring proses bongkar barang dan data perjalanan kendaraan.
            </p>

        </a>

        <!-- MAINTENANCE -->

        <a href="../user/maintenance.php" class="menu-card">

            <div class="icon">
                🛠
            </div>

            <h3>Maintenance</h3>

            <p>
                Monitoring kondisi kendaraan dan jadwal maintenance armada.
            </p>

        </a>

    </div>

</div>

</body>
</html>
