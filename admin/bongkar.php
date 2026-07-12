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
        SELECT bongkar.*, users.username,
        detail_bongkar.uang_pelunasan, detail_bongkar.surat_jalan
        FROM bongkar
        JOIN users ON bongkar.user_id = users.id
        LEFT JOIN detail_bongkar ON detail_bongkar.bongkar_id = bongkar.id
        WHERE users.username LIKE ?
        ORDER BY bongkar.id DESC
    ");
    $like = "%$search%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $data = $stmt->get_result();
} else {
    $data = $conn->query("
        SELECT bongkar.*, users.username,
        detail_bongkar.uang_pelunasan, detail_bongkar.surat_jalan
        FROM bongkar
        JOIN users ON bongkar.user_id = users.id
        LEFT JOIN detail_bongkar ON detail_bongkar.bongkar_id = bongkar.id
        ORDER BY bongkar.id DESC
    ");
}
$totalData = $data->num_rows;

$pageTitle = "Data Bongkar";
$cssFile = "admin";
?>
<?php require "../includes/header.php"; ?>
<div class="admin-wrap">

<?php require "../includes/sidebar-admin.php"; ?>

<div class="content">

    <div class="page-header">
        <h1><i class="fa-solid fa-warehouse"></i> Data Bongkar</h1>
        <div class="btn-group">
            <a href="export_bongkar.php?search=<?= $search ?>" class="btn btn-success"><i class="fa-solid fa-file-excel"></i> Export Excel</a>
        </div>
    </div>

    <form method="GET" class="search-bar">
        <div class="input-wrap">
            <i class="fa-solid fa-search"></i>
            <input type="text" name="search" placeholder="Cari username..." value="<?= $search ?>">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
        <?php if ($search != ''): ?>
        <a href="bongkar.php" class="btn btn-danger"><i class="fa-solid fa-times"></i> Reset</a>
        <?php endif; ?>
    </form>

    <div class="card">
        <div class="card-title">
            <i class="fa-solid fa-table" style="color:#60a5fa;"></i>
            Data Bongkar Barang
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
                        <th>Lokasi Bongkar</th>
                        <th>Jam Tiba</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Keluar</th>
                        <th>Ket</th>
                        <th>Pelunasan</th>
                        <th>Surat Jalan</th>
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
                        <td><?= $m['lokasi_bongkar'] ?></td>
                        <td><?= $m['jam_tiba'] ?: '-' ?></td>
                        <td><?= $m['jam_mulai_bongkar'] ?: '-' ?></td>
                        <td><?= $m['jam_selesai_bongkar'] ?: '-' ?></td>
                        <td><?= $m['jam_keluar'] ?: '-' ?></td>
                        <td><?= $m['keterangan'] ?: '-' ?></td>
                        <td class="text-right">Rp <?= number_format((int)$m['uang_pelunasan']) ?></td>
                        <td>
                            <?php if ($m['surat_jalan'] != ''): ?>
                            <a class="file-link" href="/upload/surat_jalan/<?= $m['surat_jalan'] ?>" target="_blank">
                                <i class="fa-solid fa-file"></i> Lihat
                            </a>
                            <?php else: ?>
                            <span style="color:#475569;">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group" style="justify-content:center;">
                                <a href="edit_bongkar.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-warning"><i class="fa-solid fa-pen"></i></a>
                                <a href="hapus_bongkar.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus data?')"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fa-solid fa-warehouse"></i>
            <p>Belum ada data bongkar</p>
        </div>
        <?php endif; ?>
    </div>

</div>
</div>
</body>
</html>
