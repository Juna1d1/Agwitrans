<?php
session_start();
require "../config/database.php";
require "../includes/auth-check.php";
requireAdmin();

$message = "";
$msgType = "info";

if (isset($_GET['update']) && $_GET['update'] == 'success') {
    $message = "Data kendaraan berhasil diperbarui!";
    $msgType = "success";
}

if (isset($_POST['update'])) {

    $id   = intval($_POST['id']);
    $plat = trim($_POST['plat']);
    $type = trim($_POST['type_unit']);

    if ($plat == '' || $type == '') {
        $message = "Semua field wajib diisi!";
        $msgType = "warning";
        
    } else {

        // cek plat selain data yang sedang diedit
        $cek = $conn->prepare("SELECT id FROM kendaraan WHERE plat=? AND id<>?");
        $cek->bind_param("si", $plat, $id);
        $cek->execute();

        if ($cek->get_result()->num_rows > 0) {

            $message = "Plat nomor sudah digunakan!";
            $msgType = "danger";

        } else {

            $update = $conn->prepare("UPDATE kendaraan SET plat=?, type_unit=? WHERE id=?");
            $update->bind_param("ssi", $plat, $type, $id);

            if ($update->execute()) {
                header("Location: kendaraan.php?update=success");
                exit;
            } else {
                $message = "Gagal memperbarui data!";
                $msgType = "danger";
            }

        }

    }

}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $hapus = $conn->prepare("DELETE FROM kendaraan WHERE id=?");
    $hapus->bind_param("i", $id);
    if ($hapus->execute()) {
        $message = "Data kendaraan berhasil dihapus!";
        $msgType = "success";
    } else {
        $message = "Gagal menghapus data!";
        $msgType = "danger";
    }
}

if (isset($_POST['simpan'])) {
    $plat = trim($_POST['plat']);
    $type = trim($_POST['type_unit']);
    if ($plat == '' || $type == '') {
        $message = "Semua field wajib diisi!";
        $msgType = "warning";
    } else {
        $cek = $conn->prepare("SELECT * FROM kendaraan WHERE plat=?");
        $cek->bind_param("s", $plat);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) {
            $message = "Plat nomor sudah ada!";
            $msgType = "danger";
        } else {
            $stmt = $conn->prepare("INSERT INTO kendaraan(plat,type_unit) VALUES (?,?)");
            $stmt->bind_param("ss", $plat, $type);
            $stmt->execute();
            $message = "Data kendaraan berhasil ditambahkan!";
            $msgType = "success";
        }
    }
}

$edit = null;

if (isset($_GET['edit'])) {

    $id = intval($_GET['edit']);

    $ambil = $conn->prepare("SELECT * FROM kendaraan WHERE id=?");
    $ambil->bind_param("i", $id);
    $ambil->execute();

    $edit = $ambil->get_result()->fetch_assoc();

}

$data = $conn->query("SELECT * FROM kendaraan ORDER BY id DESC");
$totalUnit = $data->num_rows;

$pageTitle = "Tambah Unit Kendaraan";
$cssFile = "admin";
?>
<?php require "../includes/header.php"; ?>
<div class="admin-wrap">

<?php require "../includes/sidebar-admin.php"; ?>

<div class="content">

    <div class="page-header">
        <h1><i class="fa-solid fa-truck"></i> Unit Kendaraan</h1>
        <a href="dashboard.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fa-solid fa-truck"></i></div>
            <div class="stat-info">
                <h3><?= $totalUnit ?></h3>
                <p>Total Unit</p>
            </div>
        </div>
    </div>

    <?php if ($message != ''): ?>
    <div class="alert alert-<?= $msgType ?>">
        <i class="fa-solid fa-<?= $msgType == 'success' ? 'check-circle' : ($msgType == 'danger' ? 'exclamation-circle' : 'exclamation-triangle') ?>"></i>
        <?= $message ?>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-title">
            <i class="fa-solid fa-plus-circle" style="color:#60a5fa;"></i>
            Tambah Kendaraan Baru
        </div>
        <form method="POST">
            <?php if($edit): ?>
            <input type="hidden" name="id" value="<?= $edit['id'] ?>">
            <?php endif; ?>
            <div class="form-grid">
                <div class="form-group">
                    <label><i class="fa-solid fa-id-card"></i> Plat Nomor</label>
                    <input type="text" name="plat" class="form-control" value="<?= $edit['plat'] ?? '' ?>" placeholder="Contoh: B 1234 XYZ" required>
                </div>
                <div class="form-group">
                    <label><i class="fa-solid fa-tag"></i> Type Unit</label>
                    <input type="text" name="type_unit" class="form-control" value="<?= $edit['type_unit'] ?? '' ?>" placeholder="Contoh: Fuso / Colt Diesel" required>
                </div>
            </div>
            <br>
            <?php if($edit): ?>

            <button
            type="submit" name="update" class="btn btn-warning"> <i class="fa-solid fa-pen"></i> Update Data </button>
            
            <a href="kendaraan.php" class="btn btn-outline">
            Batal
            </a>
            
            <?php else: ?>
            
            <button type="submit" name="simpan" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Data </button>
            
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <div class="card-title">
            <i class="fa-solid fa-list" style="color:#60a5fa;"></i>
            Data Kendaraan
            <span class="count-badge"><?= $totalUnit ?></span>
        </div>

        <?php if ($totalUnit > 0): ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:60px;">No</th>
                        <th>Plat Nomor</th>
                        <th>Type Unit</th>
                        <th class="text-center" style="width:180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; $data->data_seek(0); while ($row = $data->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong style="color:#f8fafc;font-size:15px;"><?= $row['plat'] ?></strong></td>
                        <td><?= $row['type_unit'] ?></td>
                        <td class="text-center" style="width:120px;">
                            <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning"> <i class="fa-solid fa-pen"></i>
                                Edit 
                            </a>
                                
                            <a href="?hapus=<?= $row['id'] ?>"
                                class="btn btn-sm btn-danger"onclick="return confirm('Yakin ingin hapus uni<?= $row['plat'] ?>?')"><i class="fa-solid fa-trash"></i>
                                Hapus
                            </a>

                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fa-solid fa-truck-moving"></i>
            <p>Belum ada kendaraan terdaftar</p>
        </div>
        <?php endif; ?>
    </div>

</div>
</div>
</body>
</html>
