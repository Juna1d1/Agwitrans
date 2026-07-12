<?php
session_start();
require "../config/database.php";
require "../includes/auth-check.php";
requireAdmin();

if(!isset($_GET['id'])){
    echo "ID tidak ditemukan!";
    exit;
}

$id = $_GET['id'];

$stmt = $conn->prepare("
    SELECT muat.*, detail_muat.uang_jalan_supir, detail_muat.dp
    FROM muat
    LEFT JOIN detail_muat ON detail_muat.muat_id = muat.id
    WHERE muat.id=?
");
$stmt->bind_param("i",$id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if(!$data){
    echo "Data tidak ditemukan!";
    exit;
}

if(isset($_POST['update'])){
    $plat                = $_POST['plat'];
    $supir               = $_POST['supir'];
    $type_unit           = $_POST['type_unit'];
    $lokasi_muat         = $_POST['lokasi_muat'];
    $tujuan_bongkar      = $_POST['tujuan_bongkar'];
    $tanggal             = $_POST['tanggal'];
    $jam_kedatangan      = $_POST['jam_kedatangan'];
    $jam_mulai_muat      = $_POST['jam_mulai_muat'];
    $jam_selesai_muat    = $_POST['jam_selesai_muat'];
    $jam_keberangkatan   = $_POST['jam_keberangkatan'];
    $keterangan          = $_POST['keterangan'];
    $uang_jalan_supir    = $_POST['uang_jalan_supir'];
    $dp                  = $_POST['dp'];

    $updateMuat = $conn->prepare("UPDATE muat SET plat=?, supir=?, type_unit=?, lokasi_muat=?, tujuan_bongkar=?, tanggal=?, jam_kedatangan=?, jam_mulai_muat=?, jam_selesai_muat=?, jam_keberangkatan=?, keterangan=? WHERE id=?");
    $updateMuat->bind_param("sssssssssssi", $plat, $supir, $type_unit, $lokasi_muat, $tujuan_bongkar, $tanggal, $jam_kedatangan, $jam_mulai_muat, $jam_selesai_muat, $jam_keberangkatan, $keterangan, $id);
    $updateMuat->execute();

    $cekDetail = $conn->prepare("SELECT * FROM detail_muat WHERE muat_id=?");
    $cekDetail->bind_param("i",$id);
    $cekDetail->execute();
    $detail = $cekDetail->get_result();

    if($detail->num_rows > 0){
        $updateDetail = $conn->prepare("UPDATE detail_muat SET uang_jalan_supir=?, dp=? WHERE muat_id=?");
        $updateDetail->bind_param("ssi", $uang_jalan_supir, $dp, $id);
        $updateDetail->execute();
    } else {
        $insertDetail = $conn->prepare("INSERT INTO detail_muat (muat_id, uang_jalan_supir, dp) VALUES (?,?,?)");
        $insertDetail->bind_param("iss", $id, $uang_jalan_supir, $dp);
        $insertDetail->execute();
    }

    echo "<script>alert('Data berhasil diupdate!'); window.location='muat.php';</script>";
}

$pageTitle = "Edit Muat";
$cssFile = "admin";
?>
<?php require "../includes/header.php"; ?>
<?php require "../includes/sidebar-admin.php"; ?>

<div class="content">

    <div class="card">
        <h2><i class="fa-solid fa-pen" style="color:#5c8dff;margin-right:8px;"></i>Edit Data Muat</h2>

        <form method="POST">
            <div class="grid">

                <div class="input-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" value="<?= $data['tanggal'] ?>">
                </div>

                <div class="input-group">
                    <label>Plat Nomor</label>
                    <input type="text" name="plat" placeholder="Plat" value="<?= $data['plat'] ?>">
                </div>

                <div class="input-group">
                    <label>Type Unit</label>
                    <input type="text" name="type_unit" placeholder="Type Unit" value="<?= $data['type_unit'] ?>">
                </div>

                <div class="input-group">
                    <label>Nama Supir</label>
                    <input type="text" name="supir" placeholder="Supir" value="<?= $data['supir'] ?>">
                </div>

                <div class="input-group">
                    <label>Lokasi Muat</label>
                    <input type="text" name="lokasi_muat" placeholder="Lokasi Muat" value="<?= $data['lokasi_muat'] ?>">
                </div>

                <div class="input-group">
                    <label>Tujuan Bongkar</label>
                    <input type="text" name="tujuan_bongkar" placeholder="Tujuan Bongkar" value="<?= $data['tujuan_bongkar'] ?>">
                </div>

                <div class="input-group">
                    <label>Jam Kedatangan</label>
                    <input type="text" name="jam_kedatangan" class="time-24h" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5" value="<?= substr($data['jam_kedatangan'], 0, 5) ?>">
                </div>

                <div class="input-group">
                    <label>Jam Mulai Muat</label>
                    <input type="text" name="jam_mulai_muat" class="time-24h" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5" value="<?= substr($data['jam_mulai_muat'], 0, 5) ?>">
                </div>

                <div class="input-group">
                    <label>Jam Selesai Muat</label>
                    <input type="text" name="jam_selesai_muat" class="time-24h" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5" value="<?= substr($data['jam_selesai_muat'], 0, 5) ?>">
                </div>

                <div class="input-group">
                    <label>Jam Keberangkatan</label>
                    <input type="text" name="jam_keberangkatan" class="time-24h" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5" value="<?= substr($data['jam_keberangkatan'], 0, 5) ?>">
                </div>

                <div class="input-group">
                    <label>Uang Jalan Supir</label>
                    <input type="text" name="uang_jalan_supir" placeholder="Uang Jalan" value="<?= $data['uang_jalan_supir'] ?>">
                </div>

                <div class="input-group">
                    <label>DP</label>
                    <input type="text" name="dp" placeholder="DP" value="<?= $data['dp'] ?>">
                </div>

            </div>

            <textarea name="keterangan" placeholder="Keterangan"><?= $data['keterangan'] ?></textarea>

            <br>

            <button type="submit" name="update"><i class="fa-solid fa-pen" style="margin-right:6px;"></i>Update Data</button>
            <a href="muat.php" style="color:#94a3b8;margin-left:12px;font-size:14px;">Batal</a>
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
