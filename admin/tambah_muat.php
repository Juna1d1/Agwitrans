<?php
session_start();
require "../config/database.php";
require "../includes/auth-check.php";
requireAdmin();

if (isset($_POST['simpan'])) {
    $user_id            = $_POST['user_id'];
    $plat               = $_POST['plat'];
    $supir              = $_POST['supir'];
    $lokasi_muat        = $_POST['lokasi_muat'];
    $tujuan_bongkar     = $_POST['tujuan_bongkar'];
    $tanggal            = $_POST['tanggal'];
    $jam_kedatangan     = $_POST['jam_kedatangan'];
    $jam_mulai_muat     = $_POST['jam_mulai_muat'];
    $jam_selesai_muat   = $_POST['jam_selesai_muat'];
    $jam_keberangkatan  = $_POST['jam_keberangkatan'];
    $keterangan         = $_POST['keterangan'];
    $uang_jalan_supir   = $_POST['uang_jalan_supir'] ?? 0;
    $dp                 = $_POST['dp'] ?? 0;

    $q = $conn->prepare("SELECT type_unit FROM kendaraan WHERE plat=?");
    $q->bind_param("s", $plat);
    $q->execute();
    $type = $q->get_result()->fetch_assoc();
    $type_unit = $type['type_unit'];

    $stmt = $conn->prepare("INSERT INTO muat (user_id, plat, supir, type_unit, lokasi_muat, tujuan_bongkar, tanggal, jam_kedatangan, jam_mulai_muat, jam_selesai_muat, jam_keberangkatan, keterangan) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("isssssssssss", $user_id, $plat, $supir, $type_unit, $lokasi_muat, $tujuan_bongkar, $tanggal, $jam_kedatangan, $jam_mulai_muat, $jam_selesai_muat, $jam_keberangkatan, $keterangan);
    $stmt->execute();
    $muat_id = $conn->insert_id;

    $detail = $conn->prepare("INSERT INTO detail_muat (muat_id, uang_jalan_supir, dp) VALUES (?,?,?)");
    $detail->bind_param("iss", $muat_id, $uang_jalan_supir, $dp);
    $detail->execute();

    echo "<script>alert('Data muat berhasil ditambahkan!'); window.location='muat.php';</script>";
}

$users = $conn->query("SELECT * FROM users WHERE role='user' AND status='approved'");
$kendaraan = $conn->query("SELECT * FROM kendaraan");

$pageTitle = "Input Data Muat";
$cssFile = "admin";
?>
<?php require "../includes/header.php"; ?>
<?php require "../includes/sidebar-admin.php"; ?>

<!DOCTYPE html>
<html>
<head>

<title>Tambah Muat</title>

<style>

body{
    background:#081028;
    color:white;
    font-family:sans-serif;
    padding:20px;
}

.card{
    background:#0f1d48;
    padding:25px;
    border-radius:15px;
    max-width:1000px;
    margin:auto;
}

.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}

input, select, textarea{

    width:100%;
    padding:12px;

    border:none;
    border-radius:8px;

    background:#12204d;
    color:white;
}

textarea{
    height:100px;
}

button{

    background:#5c8dff;
    color:white;

    padding:12px 20px;

    border:none;
    border-radius:8px;

    cursor:pointer;
}

a{
    color:white;
    text-decoration:none;
}

.input-group{
    display:flex;
    flex-direction:column;
    gap:8px;
}

.input-group label{
    font-size:14px;
    color:#cbd5e1;
    font-weight:600;
}

</style>

</head>

<body>
<div class="content">

    <div class="card">
        <h2><i class="fa-solid fa-box" style="color:#5c8dff;margin-right:8px;"></i>Input Data Muat</h2>

        <form method="POST">
            <div class="grid">

                <div class="input-group">
                    <label>User</label>
                    <select name="user_id" required>
                        <option value="">-- Pilih User --</option>
                        <?php while($u = $users->fetch_assoc()){ ?>
                        <option value="<?= $u['id'] ?>"><?= $u['username'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="input-group">
                    <label>Plat Nomor</label>
                    <select name="plat" required>
                        <option value="">-- Pilih Plat --</option>
                        <?php while($k = $kendaraan->fetch_assoc()){ ?>
                        <option value="<?= $k['plat'] ?>"><?= $k['plat'] ?> - <?= $k['type_unit'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="input-group">
                    <label>Nama Supir</label>
                    <input type="text" name="supir" placeholder="Nama Supir" required>
                </div>

                <div class="input-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" required>
                </div>

                <div class="input-group">
                    <label>Lokasi Muat</label>
                    <input type="text" name="lokasi_muat" placeholder="Lokasi Muat" required>
                </div>

                <div class="input-group">
                    <label>Tujuan Bongkar</label>
                    <input type="text" name="tujuan_bongkar" placeholder="Tujuan Bongkar" required>
                </div>

                <div class="input-group">
                    <label>Jam Kedatangan</label>
                    <input type="text" name="jam_kedatangan" class="time-24h" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5">
                </div>

                <div class="input-group">
                    <label>Jam Mulai Muat</label>
                    <input type="text" name="jam_mulai_muat" class="time-24h" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5">
                </div>

                <div class="input-group">
                    <label>Jam Selesai Muat</label>
                    <input type="text" name="jam_selesai_muat" class="time-24h" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5">
                </div>

                <div class="input-group">
                    <label>Jam Keberangkatan</label>
                    <input type="text" name="jam_keberangkatan" class="time-24h" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5">
                </div>

                <div class="input-group">
                    <label>Uang Jalan Supir</label>
                    <input type="text" name="uang_jalan_supir" placeholder="Uang Jalan Supir">
                </div>

                <div class="input-group">
                    <label>DP</label>
                    <input type="text" name="dp" placeholder="DP">
                </div>

            </div>

            <textarea name="keterangan" placeholder="Keterangan"></textarea>

            <br><br>

            <button type="submit" name="simpan"><i class="fa-solid fa-floppy-disk" style="margin-right:6px;"></i>Simpan Data</button>
            <a href="muat.php" style="color:#94a3b8;margin-left:12px;font-size:14px;">Kembali</a>
        </form>
    </div>

</div>

<script>
document.querySelectorAll('.time-24h').forEach(function(input) {
    input.addEventListener('input', function(e) {
        let val = this.value.replace(/[^0-9]/g, '');
        if (val.length >= 3) {
            this.value = val.substring(0, 2) + ':' + val.substring(2, 4);
        }
    });
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && this.value.length === 3 && this.value[2] === ':') {
            this.value = this.value.substring(0, 2);
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>
