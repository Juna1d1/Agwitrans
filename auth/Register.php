<?php
require "../config/database.php";
$username = $_POST['username'];
$no_hp    = $_POST['no_hp'];
$password_raw = $_POST['password'];

// VALIDASI SERVER
if($username == '' || $no_hp == '' || $password_raw == ''){
    echo "Semua field wajib diisi!";
    exit;
}
if(strlen($password_raw) < 5){
    echo "Password minimal 5 karakter!";
    exit;
}
if(!preg_match('/^(08|\+628)[0-9]{8,12}$/', $no_hp)){
    echo "Format nomor HP tidak valid!";
    exit;
}

$password = password_hash($password_raw, PASSWORD_DEFAULT);

// cek user/no_hp sudah ada
$stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR no_hp=?");
$stmt->bind_param("ss", $username, $no_hp);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows > 0){
    echo "Username / No HP sudah digunakan!";
    exit;
}

// insert user
$stmt = $conn->prepare("INSERT INTO users (username,no_hp,password) VALUES (?,?,?)");
$stmt->bind_param("sss", $username, $no_hp, $password);
$stmt->execute();
echo "Register berhasil, tunggu approval admin!";
?>