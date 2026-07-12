<?php
$conn = new mysqli("localhost", "agwitran_user", "Agwitrans123", "agwitran_db");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$cek = $conn->query("SELECT id FROM users WHERE role='admin' LIMIT 1");

if($cek->num_rows == 0){

    $username = "admin";
    $email    = "admin@gmail.com";
    $password = password_hash("12345", PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username,email,password,role,status) 
    VALUES (?,?,?,?,?)");

    $role = "admin";
    $status = "approved";

    $stmt->bind_param("sssss", $username,$email,$password,$role,$status);
    $stmt->execute();
}
?>
