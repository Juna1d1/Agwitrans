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
    SELECT bongkar.*, detail_bongkar.uang_pelunasan, detail_bongkar.surat_jalan
    FROM bongkar
    LEFT JOIN detail_bongkar ON detail_bongkar.bongkar_id = bongkar.id
    WHERE bongkar.id=?
");
$stmt->bind_param("i",$id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if(!$data){
    echo "Data tidak ditemukan!";
    exit;
}

if(isset($_POST['update'])){
    $plat                 = $_POST['plat'];
    $supir                = $_POST['supir'];
    $lokasi_bongkar       = $_POST['lokasi_bongkar'];
    $tanggal              = $_POST['tanggal'];
    $jam_tiba             = $_POST['jam_tiba'];
    $jam_mulai_bongkar    = $_POST['jam_mulai_bongkar'];
    $jam_selesai_bongkar  = $_POST['jam_selesai_bongkar'];
    $jam_keluar           = $_POST['jam_keluar'];
    $keterangan           = $_POST['keterangan'];
    $uang_pelunasan       = $_POST['uang_pelunasan'];

    $q = $conn->prepare("SELECT type_unit FROM kendaraan WHERE plat=?");
    $q->bind_param("s",$plat);
    $q->execute();
    $type = $q->get_result()->fetch_assoc();
    $type_unit = $type['type_unit'];

    $update = $conn->prepare("UPDATE bongkar SET plat=?, supir=?, type_unit=?, lokasi_bongkar=?, tanggal=?, jam_tiba=?, jam_mulai_bongkar=?, jam_selesai_bongkar=?, jam_keluar=?, keterangan=? WHERE id=?");
    $update->bind_param("ssssssssssi",$plat,$supir,$type_unit,$lokasi_bongkar,$tanggal,$jam_tiba,$jam_mulai_bongkar,$jam_selesai_bongkar,$jam_keluar,$keterangan,$id);
    $update->execute();

    $surat_jalan = $data['surat_jalan'];

    if(isset($_FILES['surat_jalan']) && $_FILES['surat_jalan']['name'] != ''){
        if($surat_jalan != ''){
            $old = "../upload/surat_jalan/".$surat_jalan;
            if(file_exists($old)){
                unlink($old);
            }
        }
        $surat_jalan = time().'_'.$_FILES['surat_jalan']['name'];
        move_uploaded_file($_FILES['surat_jalan']['tmp_name'],"../upload/surat_jalan/".$surat_jalan);
    }

    $detail = $conn->prepare("UPDATE detail_bongkar SET uang_pelunasan=?, surat_jalan=? WHERE bongkar_id=?");
    $detail->bind_param("ssi",$uang_pelunasan,$surat_jalan,$id);
    $detail->execute();

    echo "<script>alert('Data berhasil diupdate!'); window.location='bongkar.php';</script>";
}

$kendaraan = $conn->query("SELECT * FROM kendaraan");

$pageTitle = "Edit Bongkar";
$cssFile = "admin";
?>
<?php require "../includes/header.php"; ?>
<?php require "../includes/sidebar-admin.php"; ?>

<div class="content">

    <div class="card">
        <h2><i class="fa-solid fa-warehouse" style="color:#5c8dff;margin-right:8px;"></i>Edit Data Bongkar</h2>

        <form method="POST" enctype="multipart/form-data">
            <div class="grid">

                <div class="input-group">
                    <label>Plat Nomor</label>
                    <select name="plat" required>
                        <option value="">-- Pilih Plat --</option>
                        <?php while($k = $kendaraan->fetch_assoc()){ ?>
                        <option value="<?= $k['plat'] ?>" <?= $data['plat']==$k['plat'] ? 'selected' : '' ?>><?= $k['plat'] ?> - <?= $k['type_unit'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="input-group">
                    <label>Nama Supir</label>
                    <input type="text" name="supir" placeholder="Nama Supir" value="<?= $data['supir'] ?>">
                </div>

                <div class="input-group">
                    <label>Lokasi Bongkar</label>
                    <input type="text" name="lokasi_bongkar" placeholder="Lokasi Bongkar" value="<?= $data['lokasi_bongkar'] ?>">
                </div>

                <div class="input-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" value="<?= $data['tanggal'] ?>">
                </div>

                <div class="input-group">
                    <label>Jam Tiba</label>
                    <input type="text" name="jam_tiba" class="time-24h" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5" value="<?= substr($data['jam_tiba'], 0, 5) ?>">
                </div>

                <div class="input-group">
                    <label>Jam Mulai Bongkar</label>
                    <input type="text" name="jam_mulai_bongkar" class="time-24h" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5" value="<?= substr($data['jam_mulai_bongkar'], 0, 5) ?>">
                </div>

                <div class="input-group">
                    <label>Jam Selesai Bongkar</label>
                    <input type="text" name="jam_selesai_bongkar" class="time-24h" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5" value="<?= substr($data['jam_selesai_bongkar'], 0, 5) ?>">
                </div>

                <div class="input-group">
                    <label>Jam Keluar</label>
                    <input type="text" name="jam_keluar" class="time-24h" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5" value="<?= substr($data['jam_keluar'], 0, 5) ?>">
                </div>

                <div class="input-group">
                    <label>Uang Pelunasan</label>
                    <input type="text" name="uang_pelunasan" placeholder="Rp ..." value="<?= $data['uang_pelunasan'] ?>">
                </div>

                <div class="input-group">
                    <label>Surat Jalan</label>
                    <input type="file" name="surat_jalan">
                    <?php if ($data['surat_jalan']): ?>
                    <small style="color:#94a3b8;margin-top:4px;display:block;">File saat ini: <?= $data['surat_jalan'] ?></small>
                    <?php endif; ?>
                </div>

            </div>

            <textarea name="keterangan" placeholder="Keterangan"><?= $data['keterangan'] ?></textarea>

            <br><br>

            <button type="submit" name="update"><i class="fa-solid fa-pen" style="margin-right:6px;"></i>Update Data</button>
            <a href="bongkar.php" style="color:#94a3b8;margin-left:12px;font-size:14px;">Batal</a>
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
