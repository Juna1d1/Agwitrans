<?php
session_start();
require "../config/database.php";
require "../includes/auth-check.php";
requireAdmin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: maintenance.php?msg=ID tidak valid!");
    exit;
}

$id = (int) $_GET['id'];

$stmt = $conn->prepare("DELETE FROM maintenance WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: maintenance.php?msg=Data maintenance berhasil dihapus!");
} else {
    header("Location: maintenance.php?msg=Gagal menghapus data!");
}
exit;
?>