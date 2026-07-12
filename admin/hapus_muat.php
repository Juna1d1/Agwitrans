<?php
session_start();
require "../config/database.php";

/* ================= CEK LOGIN ================= */

if(!isset($_SESSION['user']) || $_SESSION['role'] != 'admin'){
    echo "Akses ditolak!";
    exit;
}

/* ================= CEK ID ================= */

if(!isset($_GET['id'])){

    echo "
    <script>
    alert('ID tidak ditemukan!');
    window.location='muat.php';
    </script>
    ";

    exit;
}

$id = $_GET['id'];

/* ================= CEK DATA ================= */

$cek = $conn->prepare("
SELECT * FROM muat
WHERE id=?
");

$cek->bind_param("i",$id);
$cek->execute();

$result = $cek->get_result();

if($result->num_rows == 0){

    echo "
    <script>
    alert('Data tidak ditemukan!');
    window.location='muat.php';
    </script>
    ";

    exit;
}

/* ================= HAPUS DATA ================= */

/*
Karena detail_muat pakai:
FOREIGN KEY ... ON DELETE CASCADE

maka detail_muat otomatis ikut kehapus
*/

$hapus = $conn->prepare("
DELETE FROM muat
WHERE id=?
");

$hapus->bind_param("i",$id);
$hapus->execute();

/* ================= REDIRECT ================= */

echo "
<script>
alert('Data berhasil dihapus!');
window.location='muat.php';
</script>
";
?>
