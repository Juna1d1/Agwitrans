<?php
session_start();
require "../config/database.php";
require "../includes/auth-check.php";
requireAdmin();

$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

if ($search != '') {
    $stmt = $conn->prepare("
        SELECT muat.*, users.username,
        detail_muat.uang_jalan_supir, detail_muat.dp
        FROM muat
        JOIN users ON muat.user_id = users.id
        LEFT JOIN detail_muat ON detail_muat.muat_id = muat.id
        WHERE users.username LIKE ?
        ORDER BY muat.id DESC
    ");
    $like = "%$search%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $data = $stmt->get_result();
} else {
    $data = $conn->query("
        SELECT muat.*, users.username,
        detail_muat.uang_jalan_supir, detail_muat.dp
        FROM muat
        JOIN users ON muat.user_id = users.id
        LEFT JOIN detail_muat ON detail_muat.muat_id = muat.id
        ORDER BY muat.id DESC
    ");
}
$totalData = $data->num_rows;

$pageTitle = "Data Muat";
$cssFile = "admin";
?>
<?php require "../includes/header.php"; ?>
<div class="admin-wrap">

<?php require "../includes/sidebar-admin.php"; ?>

<div class="content">

    <div class="page-header">
        <h1><i class="fa-solid fa-box"></i> Data Muat</h1>
        <div class="btn-group">
            <a href="tambah_muat.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Tambah Muat</a>
            <a href="export_muat.php?search=<?= $search ?>" class="btn btn-success"><i class="fa-solid fa-file-excel"></i> Export Excel</a>
        </div>
    </div>

    <form method="GET" class="search-bar">
        <div class="input-wrap">
            <i class="fa-solid fa-search"></i>
            <input type="text" name="search" placeholder="Cari username..." value="<?= $search ?>">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
        <?php if ($search != ''): ?>
        <a href="muat.php" class="btn btn-danger"><i class="fa-solid fa-times"></i> Reset</a>
        <?php endif; ?>
    </form>

    <div class="card">
        <div class="card-title">
            <i class="fa-solid fa-table" style="color:#60a5fa;"></i>
            Data Muat Barang
            <span class="count-badge"><?= $totalData ?></span>
        </div>

        <?php if ($totalData > 0): ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>User</th>
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
                        <th>Uang Jalan</th>
                        <th>DP</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($m = $data->fetch_assoc()): ?>
                    <tr>
                        <td><?= $m['tanggal'] ?></td>
                        <td><span class="badge badge-info"><?= $m['username'] ?></span></td>
                        <td><strong><?= $m['plat'] ?></strong></td>
                        <td><?= $m['type_unit'] ?></td>
                        <td><?= $m['supir'] ?></td>
                        <td><?= $m['lokasi_muat'] ?></td>
                        <td><?= $m['tujuan_bongkar'] ?></td>
                        <td><?= $m['jam_kedatangan'] ?: '-' ?></td>
                        <td><?= $m['jam_mulai_muat'] ?: '-' ?></td>
                        <td><?= $m['jam_selesai_muat'] ?: '-' ?></td>
                        <td><?= $m['jam_keberangkatan'] ?: '-' ?></td>
                        <td><?= $m['keterangan'] ?: '-' ?></td>
                        <td class="text-right">Rp <?= number_format((int)$m['uang_jalan_supir']) ?></td>
                        <td class="text-right">Rp <?= number_format((int)$m['dp']) ?></td>
                        <td class="text-center">
                            <div class="btn-group" style="justify-content:center;">
                                <a href="edit_muat.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-warning"><i class="fa-solid fa-pen"></i></a>
                                <a href="hapus_muat.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus data?')"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fa-solid fa-box-open"></i>
            <p>Belum ada data muat</p>
        </div>
        <?php endif; ?>
    </div>

</div>
</div>
</body>
</html>
