<?php
session_start();
require "../config/database.php";

if(!isset($_POST['username']) || !isset($_POST['password'])){
    header("Location: ../index.html?error=Input tidak lengkap!");
    exit;
}

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if($user){
    if($user['status'] == 'deleted'){
        header("Location: ../index.html?error=Akun ini sudah tidak aktif!");
        exit;
    }
    if($user['status'] != 'approved'){
        header("Location: ../index.html?error=Akun belum disetujui admin!");
        exit;
    }
    if(password_verify($password, $user['password'])){
        session_regenerate_id(true);
        $_SESSION['user'] = $user['username'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if($user['role'] == 'admin'){
            header("Location: ../admin/dashboard.php");
        }else{
            header("Location: ../user/Dashboard.php");
        }
        exit;
    }else{
        header("Location: ../index.html?error=Username atau password salah!");
        exit;
    }
}else{
    header("Location: ../index.html?error=Username atau password salah!");
    exit;
}
?>