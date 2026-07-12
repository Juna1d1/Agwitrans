<?php
session_start();
require "../config/database.php";
require "../includes/auth-check.php";
requireLogin();

$pageTitle = "Bongkar Barang";
$cssFile = "user";
$activePage = "bongkar";

$user_id = $_SESSION['user_id'];

if (!file_exists("../upload/surat_jalan")) {
    mkdir("../upload/surat_jalan", 0777, true);
}

$edit = null;
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $stmt = $conn->prepare("
        SELECT bongkar.*, detail_bongkar.surat_jalan
        FROM bongkar
        LEFT JOIN detail_bongkar ON detail_bongkar.bongkar_id = bongkar.id
        WHERE bongkar.id=? AND bongkar.user_id=?
    ");
    $stmt->bind_param("ii", $id_edit, $user_id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $plat = $_POST['plat'];
    $supir = $_POST['supir'];
    $lokasi_bongkar = $_POST['lokasi_bongkar'];
    $tanggal = $_POST['tanggal'];
    $jam_tiba = $_POST['jam_tiba'];
    $jam_mulai_bongkar = $_POST['jam_mulai_bongkar'];
    $jam_selesai_bongkar = $_POST['jam_selesai_bongkar'];
    $jam_keluar = $_POST['jam_keluar'];
    $keterangan = $_POST['keterangan'];

    $q = $conn->prepare("SELECT type_unit FROM kendaraan WHERE plat=?");
    $q->bind_param("s", $plat);
    $q->execute();
    $type = $q->get_result()->fetch_assoc();
    $type_unit = $type['type_unit'];

    $stmt = $conn->prepare("
        UPDATE bongkar SET
            plat=?, supir=?, type_unit=?, lokasi_bongkar=?,
            tanggal=?, jam_tiba=?, jam_mulai_bongkar=?,
            jam_selesai_bongkar=?, jam_keluar=?, keterangan=?
        WHERE id=? AND user_id=?
    ");
    $stmt->bind_param("ssssssssssii",
        $plat, $supir, $type_unit, $lokasi_bongkar,
        $tanggal, $jam_tiba, $jam_mulai_bongkar,
        $jam_selesai_bongkar, $jam_keluar, $keterangan,
        $id, $user_id
    );
    $stmt->execute();

    $surat_jalan = $edit['surat_jalan'] ?? '';
    if (isset($_FILES['surat_jalan']) && $_FILES['surat_jalan']['name'] != '') {
        if ($surat_jalan != '' && file_exists("../upload/surat_jalan/" . $surat_jalan)) {
            unlink("../upload/surat_jalan/" . $surat_jalan);
        }
        $surat_jalan = time() . '_' . $_FILES['surat_jalan']['name'];
        move_uploaded_file($_FILES['surat_jalan']['tmp_name'], "../upload/surat_jalan/" . $surat_jalan);
    }

    $cek_detail = $conn->prepare("SELECT id FROM detail_bongkar WHERE bongkar_id=?");
    $cek_detail->bind_param("i", $id);
    $cek_detail->execute();
    if ($cek_detail->get_result()->num_rows > 0) {
        $detail = $conn->prepare("UPDATE detail_bongkar SET surat_jalan=? WHERE bongkar_id=?");
        $detail->bind_param("si", $surat_jalan, $id);
    } else {
        $detail = $conn->prepare("INSERT INTO detail_bongkar (bongkar_id, surat_jalan) VALUES (?,?)");
        $detail->bind_param("is", $id, $surat_jalan);
    }
    $detail->execute();

    echo "<script>alert('Data bongkar berhasil diupdate!'); window.location='bongkar.php';</script>";
}

if (isset($_POST['simpan'])) {
    $plat = $_POST['plat'];
    $supir = $_POST['supir'];
    $lokasi_bongkar = $_POST['lokasi_bongkar'];
    $tanggal = $_POST['tanggal'];
    $jam_tiba = $_POST['jam_tiba'];
    $jam_mulai_bongkar = $_POST['jam_mulai_bongkar'];
    $jam_selesai_bongkar = $_POST['jam_selesai_bongkar'];
    $jam_keluar = $_POST['jam_keluar'];
    $keterangan = $_POST['keterangan'];

    $q = $conn->prepare("SELECT type_unit FROM kendaraan WHERE plat=?");
    $q->bind_param("s", $plat);
    $q->execute();
    $type = $q->get_result()->fetch_assoc();
    $type_unit = $type['type_unit'];

    $stmt = $conn->prepare("
        INSERT INTO bongkar (user_id, plat, supir, type_unit, lokasi_bongkar, tanggal, jam_tiba, jam_mulai_bongkar, jam_selesai_bongkar, jam_keluar, keterangan)
        VALUES (?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->bind_param("issssssssss",
        $user_id, $plat, $supir, $type_unit, $lokasi_bongkar,
        $tanggal, $jam_tiba, $jam_mulai_bongkar,
        $jam_selesai_bongkar, $jam_keluar, $keterangan
    );
    $stmt->execute();

    $bongkar_id = $conn->insert_id;

    $surat_jalan = "";
    if (isset($_FILES['surat_jalan']) && $_FILES['surat_jalan']['name'] != '') {
        $surat_jalan = time() . '_' . $_FILES['surat_jalan']['name'];
        move_uploaded_file($_FILES['surat_jalan']['tmp_name'], "../upload/surat_jalan/" . $surat_jalan);
    }

    $detail = $conn->prepare("INSERT INTO detail_bongkar (bongkar_id, surat_jalan) VALUES (?,?)");
    $detail->bind_param("is", $bongkar_id, $surat_jalan);
    $detail->execute();

    echo "<script>alert('Data bongkar berhasil disimpan!'); window.location='bongkar.php';</script>";
}

$data = $conn->prepare("
    SELECT bongkar.*, detail_bongkar.surat_jalan
    FROM bongkar
    LEFT JOIN detail_bongkar ON detail_bongkar.bongkar_id = bongkar.id
    WHERE bongkar.user_id=?
    ORDER BY bongkar.id DESC
");
$data->bind_param("i", $user_id);
$data->execute();
$result = $data->get_result();

$kendaraan = $conn->query("SELECT * FROM kendaraan");
?>
<?php require "../includes/header.php"; ?>
<?php require "../includes/sidebar-user.php"; ?>

<div class="content">

    <div class="card">
        <h2><i class="fa-solid fa-truck-ramp-box" style="color:#5c8dff;margin-right:8px;"></i><?= $edit ? 'Edit Data Bongkar' : 'Input Data Bongkar' ?></h2>

        <form method="POST" enctype="multipart/form-data">
            <div class="grid">
                <div class="input-group">
                    <label>Plat Nomor</label>
                    <select name="plat" required>
                        <option value="">-- Pilih Plat --</option>
                        <?php
                        $kendaraan->data_seek(0);
                        while ($k = $kendaraan->fetch_assoc()):
                        ?>
                        <option value="<?= $k['plat'] ?>" <?= ($edit && $edit['plat'] == $k['plat']) ? 'selected' : '' ?>>
                            <?= $k['plat'] ?> - <?= $k['type_unit'] ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="input-group">
                    <label>Nama Supir</label>
                    <input type="text" name="supir" placeholder="Nama Supir" value="<?= $edit['supir'] ?? '' ?>" required>
                </div>

                <div class="input-group">
                    <label>Lokasi Bongkar</label>
                    <input type="text" name="lokasi_bongkar" placeholder="Lokasi Bongkar" value="<?= $edit['lokasi_bongkar'] ?? '' ?>" required>
                </div>

                <div class="input-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" value="<?= $edit['tanggal'] ?? '' ?>" required>
                </div>

                <div class="input-group">
                    <label>Jam Tiba</label>
                    <input type="text" name="jam_tiba" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5" class="time-24h" value="<?= !empty($edit['jam_tiba']) ? substr($edit['jam_tiba'], 0, 5) : '' ?>">
                </div>

                <div class="input-group">
                    <label>Jam Mulai Bongkar</label>
                    <input type="text" name="jam_mulai_bongkar" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5" class="time-24h" value="<?= !empty($edit['jam_mulai_bongkar']) ? substr($edit['jam_mulai_bongkar'], 0, 5) : '' ?>">
                </div>

                <div class="input-group">
                    <label>Jam Selesai Bongkar</label>
                    <input type="text" name="jam_selesai_bongkar" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5" class="time-24h" value="<?= !empty($edit['jam_selesai_bongkar']) ? substr($edit['jam_selesai_bongkar'], 0, 5) : '' ?>">
                </div>

                <div class="input-group">
                    <label>Jam Keluar</label>
                    <input type="text" name="jam_keluar" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5" class="time-24h" value="<?= !empty($edit['jam_keluar']) ? substr($edit['jam_keluar'], 0, 5) : '' ?>">
                </div>

                <div class="input-group">
                    <label>Upload Surat Jalan</label>
                    <input type="file" name="surat_jalan">
                    <?php if ($edit && $edit['surat_jalan']): ?>
                    <small style="color:#94a3b8;margin-top:4px;">
                        File saat ini: <a href="../upload/surat_jalan/<?= $edit['surat_jalan'] ?>" target="_blank" style="color:#60a5fa;"><?= $edit['surat_jalan'] ?></a>
                    </small>
                    <?php endif; ?>
                </div>
            </div>

            <textarea name="keterangan" placeholder="Keterangan"><?= $edit['keterangan'] ?? '' ?></textarea>

            <?php if ($edit): ?>
            <input type="hidden" name="id" value="<?= $edit['id'] ?>">
            <button type="submit" name="update"><i class="fa-solid fa-pen" style="margin-right:6px;"></i>Update Data</button>
            <a href="bongkar.php" style="color:#94a3b8;margin-left:12px;font-size:14px;">Batal</a>
            <?php else: ?>
            <button type="submit" name="simpan"><i class="fa-solid fa-floppy-disk" style="margin-right:6px;"></i>Simpan Data</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2><i class="fa-solid fa-table" style="color:#5c8dff;margin-right:8px;"></i>Data Bongkar Saya</h2>

        <div class="table-box">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Plat</th>
                        <th>Type</th>
                        <th>Supir</th>
                        <th>Lokasi Bongkar</th>
                        <th>Jam Tiba</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Keluar</th>
                        <th>Ket</th>
                        <th>Surat Jalan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['tanggal'] ?></td>
                        <td><strong><?= $row['plat'] ?></strong></td>
                        <td><?= $row['type_unit'] ?></td>
                        <td><?= $row['supir'] ?></td>
                        <td><?= $row['lokasi_bongkar'] ?></td>
                        <td><?= $row['jam_tiba'] ?: '-' ?></td>
                        <td><?= $row['jam_mulai_bongkar'] ?: '-' ?></td>
                        <td><?= $row['jam_selesai_bongkar'] ?: '-' ?></td>
                        <td><?= $row['jam_keluar'] ?: '-' ?></td>
                        <td><?= $row['keterangan'] ?: '-' ?></td>
                        <td>
                            <?php if ($row['surat_jalan']): ?>
                            <a class="file-link" href="../upload/surat_jalan/<?= $row['surat_jalan'] ?>" target="_blank">
                                <i class="fa-solid fa-file"></i> Lihat
                            </a>
                            <?php else: ?>
                            <span style="color:#475569;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="bongkar.php?edit=<?= $row['id'] ?>" class="action-btn edit-btn">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($result->num_rows == 0): ?>
                    <tr>
                        <td colspan="12" style="text-align:center;padding:40px;color:#475569;">
                            <i class="fa-solid fa-warehouse" style="font-size:24px;display:block;margin-bottom:8px;"></i>
                            Belum ada data bongkar
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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
