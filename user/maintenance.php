<?php
session_start();
require "../config/database.php";
require "../includes/auth-check.php";
requireLogin();

$pageTitle = "Maintenance Kendaraan";
$cssFile = "user";
$activePage = "maintenance";

$user_id = $_SESSION['user_id'];

$edit = null;
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $ambil_edit = mysqli_query($conn, "SELECT * FROM maintenance WHERE id='$id_edit'");
    $edit = mysqli_fetch_assoc($ambil_edit);
}

function uploadBukti($file) {
    $allowed = ['jpg','jpeg','png','mp4','mov'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return false;
    if ($file['size'] > 20 * 1024 * 1024) return false; // maks 20MB

    $targetDir = "../uploads/maintenance/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

    $newName = uniqid('maintenance_') . '.' . $ext;
    $targetPath = $targetDir . $newName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return "uploads/maintenance/" . $newName;
    }
    return false;
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $supir = $_POST['supir'];
    $plat = $_POST['plat'];
    $kendaraan = $_POST['kendaraan'];
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'];
    $checklist = [];
    if (isset($_POST['kondisi'])) {
        foreach ($_POST['kondisi'] as $nama => $kondisi) {
            if ($kondisi != "Pilih") {
                $checklist[] = $nama . " (" . $kondisi . ")";
            }
        }
    }
    $checklist = implode(", ", $checklist);

    $filePath = $_POST['file_lama'] ?? "";
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] == 0) {
        $uploaded = uploadBukti($_FILES['bukti']);
        if ($uploaded !== false) {
            if ($filePath && file_exists("../" . $filePath)) {
                unlink("../" . $filePath); // hapus file lama
            }
            $filePath = $uploaded;
        }
    }

    mysqli_query($conn, "
        UPDATE maintenance SET
            supir='$supir', plat='$plat', kendaraan='$kendaraan',
            tanggal='$tanggal', checklist='$checklist', keterangan='$keterangan', file='$filePath'
        WHERE id='$id'
    ");
    header("Location: maintenance.php");
}

if (isset($_POST['simpan'])) {
    $supir = $_POST['supir'];
    $plat = $_POST['plat'];
    $kendaraan = $_POST['kendaraan'];
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'];
    $checklist = [];
    if (isset($_POST['kondisi'])) {
        foreach ($_POST['kondisi'] as $nama => $kondisi) {
            if ($kondisi != "Pilih") {
                $checklist[] = $nama . " (" . $kondisi . ")";
            }
        }
    }
    $checklist = implode(", ", $checklist);

    $filePath = "";
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] == 0) {
        $uploaded = uploadBukti($_FILES['bukti']);
        if ($uploaded !== false) $filePath = $uploaded;
    }

    mysqli_query($conn, "
        INSERT INTO maintenance (user_id, supir, plat, kendaraan, tanggal, checklist, keterangan, file)
        VALUES ('$user_id', '$supir', '$plat', '$kendaraan', '$tanggal', '$checklist', '$keterangan', '$filePath')
    ");
    header("Location: maintenance.php");
}

$data = mysqli_query($conn, "
    SELECT * FROM maintenance WHERE user_id='$user_id' ORDER BY id DESC
");

$items = [
    "Oli Mesin", "Oli Hidrolik", "Oli Power Steering",
    "Air Radiator", "Minyak Rem", "Fisik Ban",
    "Tekanan Angin Ban", "Lampu", "Kebersihan",
];

$aksesoris_items = ["Track-Belt", "Terpal", "Gembok"];
?>
<?php require "../includes/header.php"; ?>
<?php require "../includes/sidebar-user.php"; ?>

<div class="content">

<div class="card">
    <h2><i class="fa-solid fa-screwdriver-wrench" style="color:#5c8dff;margin-right:8px;"></i>Maintenance Kendaraan</h2>

    <form method="POST" enctype="multipart/form-data">
        <div class="grid">
            <?php $kendaraan = mysqli_query($conn, "SELECT * FROM kendaraan"); ?>

            <div class="input-group">
                <label>Nama Supir</label>
                <input type="text" name="supir" placeholder="Masukkan nama supir" value="<?= $edit['supir'] ?? '' ?>" required>
            </div>

            <div class="input-group">
                <label>Plat Nomor</label>
                <select name="plat" id="platSelect" required>
                    <option value="">-- Pilih Plat --</option>
                    <?php while ($k = mysqli_fetch_assoc($kendaraan)): ?>
                    <option value="<?= $k['plat'] ?>" data-type="<?= $k['type_unit'] ?>"
                        <?= (isset($edit['plat']) && $edit['plat'] == $k['plat']) ? 'selected' : '' ?>>
                        <?= $k['plat'] ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="input-group">
                <label>Jenis Kendaraan</label>
                <input type="text" name="kendaraan" id="kendaraanInput" placeholder="Terisi otomatis"
                    value="<?= $edit['kendaraan'] ?? '' ?>" readonly required>
            </div>

            <div class="input-group">
                <label>Tanggal</label>
                <input type="date" name="tanggal" value="<?= $edit['tanggal'] ?? '' ?>" required>
            </div>
        </div>

        <div class="section-title"><i class="fa-solid fa-list-check" style="margin-right:8px;"></i>Cek Kondisi Kendaraan</div>

        <div class="checklist-grid">
            <?php foreach ($items as $item): ?>
            <div class="check-box">
                <label>
                    <input type="checkbox" value="<?= $item ?>">
                    <?= $item ?>
                </label>
                <select name="kondisi[<?= $item ?>]">
                    <option value="Pilih">— Pilih Kondisi —</option>
                    <option value="Baik">✓ Baik</option>
                    <option value="Perlu Ganti/Tambah">⚠ Perlu Ganti/Tambah</option>
                </select>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="section-title"><i class="fa-solid fa-toolbox" style="margin-right:8px;"></i>Cek Aksesoris</div>

        <div class="checklist-grid">
            <?php foreach ($aksesoris_items as $item): ?>
            <div class="check-box">
                <label>
                    <input type="checkbox" value="<?= $item ?>">
                    <?= $item ?>
                </label>
                <select name="kondisi[<?= $item ?>]">
                    <option value="Pilih">— Pilih Kondisi —</option>
                    <option value="Baik">✓ Baik</option>
                    <option value="Perlu Ganti/Tambah">⚠ Perlu Ganti/Tambah</option>
                </select>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="input-group">
            <label>Foto/Video Bukti</label>
            <input type="file" name="bukti" accept="image/*,video/*">
            <?php if ($edit && $edit['file']): ?>
                <small style="display:block;margin-top:4px;">
                    File saat ini: <a href="../<?= $edit['file'] ?>" target="_blank">Lihat file</a>
                </small>
                <input type="hidden" name="file_lama" value="<?= $edit['file'] ?>">
            <?php endif; ?>
        </div>

        <div class="keterangan-box">
            <div style="font-size:14px;font-weight:600;margin-bottom:10px;color:#e2e8f0;">
                <i class="fa-solid fa-pen" style="margin-right:8px;"></i>Keterangan Tambahan
            </div>
            <textarea name="keterangan" placeholder="Contoh: Ring 8 hilang, Ban bocor, dll"><?= $edit['keterangan'] ?? '' ?></textarea>
        </div>

        <?php if ($edit): ?>
        <input type="hidden" name="id" value="<?= $edit['id'] ?>">
        <button type="submit" name="update"><i class="fa-solid fa-pen" style="margin-right:6px;"></i>Update Data</button>
        <?php else: ?>
        <button type="submit" name="simpan"><i class="fa-solid fa-floppy-disk" style="margin-right:6px;"></i>Simpan Data</button>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <h2><i class="fa-solid fa-history" style="color:#5c8dff;margin-right:8px;"></i>Riwayat Maintenance</h2>

    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>Supir</th>
                    <th>Plat</th>
                    <th>Kendaraan</th>
                    <th>Tanggal</th>
                    <th>Checklist</th>
                    <th>Bukti</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($data)): ?>
                <tr>
                    <td><strong><?= $row['supir'] ?></strong></td>
                    <td><span style="color:#60a5fa;font-weight:600;"><?= $row['plat'] ?></span></td>
                    <td><?= $row['kendaraan'] ?></td>
                    <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                    <td class="checklist-cell">
                        <?php
                        $list = explode(",", $row['checklist']);
                        foreach ($list as $item) {
                            $item = trim($item);
                            if (str_contains($item, "Baik")) {
                                echo "<span class='badge badge-good'>$item</span> ";
                            } else {
                                echo "<span class='badge badge-warning'>$item</span> ";
                            }
                        }
                        ?>
                    </td>
                    <td>
                    <?php if (!empty($row['file'])):
                        $ext = strtolower(pathinfo($row['file'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg','jpeg','png'])): ?>
                            <a href="../<?= $row['file'] ?>" target="_blank">
                                <img src="../<?= $row['file'] ?>" style="width:50px;height:50px;object-fit:cover;border-radius:6px;">
                            </a>
                        <?php else: ?>
                            <a href="../<?= $row['file'] ?>" target="_blank"><i class="fa-solid fa-circle-play"></i> Video</a>
                        <?php endif;
                    else: echo '-'; endif; ?>
                    </td>
                    <td class="keterangan-cell"><?= $row['keterangan'] ?: '-' ?></td>
                    <td>
                        <a href="maintenance.php?edit=<?= $row['id'] ?>" class="action-btn edit-btn">
                            <i class="fa-solid fa-pen"></i> Edit
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if (mysqli_num_rows($data) == 0): ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:40px;color:#475569;">
                        <i class="fa-solid fa-clipboard" style="font-size:24px;display:block;margin-bottom:8px;"></i>
                        Belum ada data maintenance
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div>

<script>
const platSelect = document.getElementById("platSelect");
const kendaraanInput = document.getElementById("kendaraanInput");
function updateKendaraan() {
    const selected = platSelect.options[platSelect.selectedIndex];
    kendaraanInput.value = selected.getAttribute("data-type") || "";
}
platSelect.addEventListener("change", updateKendaraan);
updateKendaraan();
</script>

</body>
</html>
