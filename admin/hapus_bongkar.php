<?php
session_start();
require "../config/database.php";

if(!isset($_SESSION['user']) || $_SESSION['role'] != 'admin'){
    exit("Akses ditolak!");
}

$id = $_GET['id'];

/* ================= HAPUS FILE ================= */

$q = $conn->prepare("

SELECT surat_jalan

FROM detail_bongkar

WHERE bongkar_id=?

");

$q->bind_param("i",$id);
$q->execute();

$file = $q->get_result()->fetch_assoc();

if($file && $file['surat_jalan'] != ''){

    $path = "../upload/surat_jalan/".$file['surat_jalan'];

    if(file_exists($path)){
        unlink($path);
    }
}

/* ================= HAPUS DATA ================= */

$stmt = $conn->prepare("
DELETE FROM bongkar
WHERE id=?
");

$stmt->bind_param("i",$id);
$stmt->execute();

header("Location: bongkar.php");
?>
