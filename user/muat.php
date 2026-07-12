<?php
session_start();
require "../config/database.php";
require "../includes/auth-check.php";
requireLogin();

$pageTitle = "Muat Barang";
$cssFile = "user";
$activePage = "muat";

$user_id = $_SESSION['user_id'];

$edit = null;
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM muat WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id_edit, $user_id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $plat = $_POST['plat'];
    $supir = $_POST['supir'];
    $lokasi_muat = $_POST['lokasi_muat'];
    $tujuan_bongkar = $_POST['tujuan_bongkar'];
    $tanggal = $_POST['tanggal'];
    $jam_kedatangan = $_POST['jam_kedatangan'];
    $jam_mulai_muat = $_POST['jam_mulai_muat'];
    $jam_selesai_muat = $_POST['jam_selesai_muat'];
    $jam_keberangkatan = $_POST['jam_keberangkatan'];
    $keterangan = $_POST['keterangan'];

    $q = $conn->prepare("SELECT type_unit FROM kendaraan WHERE plat=?");
    $q->bind_param("s", $plat);
    $q->execute();
    $type = $q->get_result()->fetch_assoc();
    $type_unit = $type['type_unit'];

    $stmt = $conn->prepare("
        UPDATE muat SET
            plat=?, supir=?, type_unit=?,
            lokasi_muat=?, tujuan_bongkar=?,
            tanggal=?,
            jam_kedatangan=?, jam_mulai_muat=?, jam_selesai_muat=?, jam_keberangkatan=?,
            keterangan=?
        WHERE id=? AND user_id=?
    ");
    $stmt->bind_param("sssssssssssii",
        $plat, $supir, $type_unit,
        $lokasi_muat, $tujuan_bongkar,
        $tanggal,
        $jam_kedatangan, $jam_mulai_muat, $jam_selesai_muat, $jam_keberangkatan,
        $keterangan,
        $id, $user_id
    );
    $stmt->execute();
    echo "<script>alert('Data muat berhasil diupdate!'); window.location='muat.php';</script>";
}

if (isset($_POST['simpan'])) {
    $plat = $_POST['plat'];
    $supir = $_POST['supir'];
    $lokasi_muat = $_POST['lokasi_muat'];
    $tujuan_bongkar = $_POST['tujuan_bongkar'];
    $tanggal = $_POST['tanggal'];
    $jam_kedatangan = $_POST['jam_kedatangan'];
    $jam_mulai_muat = $_POST['jam_mulai_muat'];
    $jam_selesai_muat = $_POST['jam_selesai_muat'];
    $jam_keberangkatan = $_POST['jam_keberangkatan'];
    $keterangan = $_POST['keterangan'];

    $q = $conn->prepare("SELECT type_unit FROM kendaraan WHERE plat=?");
    $q->bind_param("s", $plat);
    $q->execute();
    $type = $q->get_result()->fetch_assoc();
    $type_unit = $type['type_unit'];

    $stmt = $conn->prepare("
        INSERT INTO muat (user_id, plat, supir, type_unit, lokasi_muat, tujuan_bongkar, tanggal, jam_kedatangan, jam_mulai_muat, jam_selesai_muat, jam_keberangkatan, keterangan)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->bind_param("isssssssssss",
        $user_id, $plat, $supir, $type_unit,
        $lokasi_muat, $tujuan_bongkar, $tanggal,
        $jam_kedatangan, $jam_mulai_muat, $jam_selesai_muat, $jam_keberangkatan,
        $keterangan
    );
    $stmt->execute();
    echo "<script>alert('Data muat berhasil disimpan!'); window.location='muat.php';</script>";
}

$data = $conn->prepare("SELECT * FROM muat WHERE user_id=? ORDER BY id DESC");
$data->bind_param("i", $user_id);
$data->execute();
$result = $data->get_result();

$kendaraan = $conn->query("SELECT * FROM kendaraan");
?>
<?php require "../includes/header.php"; ?>
<?php require "../includes/sidebar-user.php"; ?>

<div class="content">

    <div class="card">
        <h2><i class="fa-solid fa-box" style="color:#5c8dff;margin-right:8px;"></i><?= $edit ? 'Edit Data Muat' : 'Input Data Muat' ?></h2>

        <form method="POST">
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
                    <label>Lokasi Muat</label>
                    <input type="text" name="lokasi_muat" placeholder="Lokasi Muat" value="<?= $edit['lokasi_muat'] ?? '' ?>" required>
                </div>

                <div class="input-group">
                    <label>Tujuan Bongkar</label>
                    <input type="text" name="tujuan_bongkar" placeholder="Tujuan Bongkar" value="<?= $edit['tujuan_bongkar'] ?? '' ?>" required>
                </div>

                <div class="input-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" value="<?= $edit['tanggal'] ?? '' ?>" required>
                </div>

                <div class="input-group">
                    <label>Jam Kedatangan</label>
                    <input type="text" name="jam_kedatangan" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5" class="time-24h" value="<?= !empty($edit['jam_kedatangan']) ? substr($edit['jam_kedatangan'], 0, 5) : '' ?>">
                </div>

                <div class="input-group">
                    <label>Jam Mulai Muat</label>
                    <input type="text" name="jam_mulai_muat" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5" class="time-24h" value="<?= !empty($edit['jam_mulai_muat']) ? substr($edit['jam_mulai_muat'], 0, 5) : '' ?>"">
                </div>

                <div class="input-group">
                    <label>Jam Selesai Muat</label>
                    <input type="text" name="jam_selesai_muat" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5" class="time-24h" value="<?= !empty($edit['jam_selesai_muat']) ? substr($edit['jam_selesai_muat'], 0, 5) : '' ?>">
                </div>

                <div class="input-group">
                    <label>Jam Keberangkatan</label>
                    <input type="text" name="jam_keberangkatan" placeholder="Contoh: 14:30" pattern="[0-2][0-9]:[0-5][0-9]" maxlength="5" class="time-24h" value="<?= !empty($edit['jam_keberangkatan']) ? substr($edit['jam_keberangkatan'], 0, 5) : '' ?>"">
                </div>
            </div>

            <textarea name="keterangan" placeholder="Keterangan"><?= $edit['keterangan'] ?? '' ?></textarea>

            <?php if ($edit): ?>
            <input type="hidden" name="id" value="<?= $edit['id'] ?>">
            <button type="submit" name="update"><i class="fa-solid fa-pen" style="margin-right:6px;"></i>Update Data</button>
            <a href="muat.php" style="color:#94a3b8;margin-left:12px;font-size:14px;">Batal</a>
            <?php else: ?>
            <button type="submit" name="simpan"><i class="fa-solid fa-floppy-disk" style="margin-right:6px;"></i>Simpan Data</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2><i class="fa-solid fa-table" style="color:#5c8dff;margin-right:8px;"></i>Data Muat Saya</h2>

        <div class="table-box">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Plat</th>
                        <th>Type</th>
                        <th>Supir</th>
                        <th>Lokasi Muat</th>
                        <th>Tujuan</th>
                        <th>Jam Datang</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Berangkat</th>
                        <th>Ket</th>
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
                        <td><?= $row['lokasi_muat'] ?></td>
                        <td><?= $row['tujuan_bongkar'] ?></td>
                        <td><?= $row['jam_kedatangan'] ?: '-' ?></td>
                        <td><?= $row['jam_mulai_muat'] ?: '-' ?></td>
                        <td><?= $row['jam_selesai_muat'] ?: '-' ?></td>
                        <td><?= $row['jam_keberangkatan'] ?: '-' ?></td>
                        <td><?= $row['keterangan'] ?: '-' ?></td>
                        <td>
                            <a href="muat.php?edit=<?= $row['id'] ?>" class="action-btn edit-btn">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($result->num_rows == 0): ?>
                    <tr>
                        <td colspan="12" style="text-align:center;padding:40px;color:#475569;">
                            <i class="fa-solid fa-box-open" style="font-size:24px;display:block;margin-bottom:8px;"></i>
                            Belum ada data muat
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
