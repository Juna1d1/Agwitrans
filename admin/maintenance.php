<?php
session_start();
require "../config/database.php";
require "../includes/auth-check.php";
requireAdmin();

if (isset($_GET['export'])) {
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=data_maintenance.xls");
    $export = mysqli_query($conn, "
        SELECT maintenance.*, users.username
        FROM maintenance
        JOIN users ON maintenance.user_id = users.id
        ORDER BY maintenance.id DESC
    ");
    echo "<table border='1'>
    <tr><th>User</th><th>Supir</th><th>Plat</th><th>Kendaraan</th><th>Tanggal</th><th>Checklist</th><th>Keterangan</th></tr>";
    while ($e = mysqli_fetch_assoc($export)) {
        echo "<tr>
            <td>{$e['username']}</td>
            <td>{$e['supir']}</td>
            <td>{$e['plat']}</td>
            <td>{$e['kendaraan']}</td>
            <td>{$e['tanggal']}</td>
            <td>{$e['checklist']}</td>
            <td>{$linkFile}</td>
            <td>{$e['keterangan']}</td>
        </tr>";
    }
    echo "</table>";
    exit;
}

$where = "";
if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where = "WHERE users.username LIKE '%$search%'";
}

$data = mysqli_query($conn, "
    SELECT maintenance.*, users.username
    FROM maintenance
    JOIN users ON maintenance.user_id = users.id
    $where
    ORDER BY maintenance.id DESC
");
$totalData = mysqli_num_rows($data);

$pageTitle = "Data Maintenance";
$cssFile = "admin";
?>
<?php require "../includes/header.php"; ?>
<div class="admin-wrap">

<?php require "../includes/sidebar-admin.php"; ?>

<div class="content">

    <div class="page-header">
        <h1><i class="fa-solid fa-screwdriver-wrench"></i> Data Maintenance</h1>
        <div class="btn-group">
            <a href="export-maintenance-excel.php" class="btn btn-success"><i class="fa-solid fa-file-excel"></i> Export Excel</a>
        </div>
    </div>
    
    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-info" style="margin-bottom:15px;">
        <?= htmlspecialchars($_GET['msg']) ?>
    </div>
    <?php endif; ?>
    
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fa-solid fa-tools"></i></div>
            <div class="stat-info">
                <h3><?= $totalData ?></h3>
                <p>Total Maintenance</p>
            </div>
        </div>
    </div>

    <form method="GET" class="search-bar">
        <div class="input-wrap">
            <i class="fa-solid fa-search"></i>
            <input type="text" name="search" placeholder="Cari user..." value="<?= $_GET['search'] ?? '' ?>">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
        <?php if (isset($_GET['search']) && $_GET['search'] != ''): ?>
        <a href="maintenance.php" class="btn btn-danger"><i class="fa-solid fa-times"></i> Reset</a>
        <?php endif; ?>
    </form>

    <div class="card">
        <div class="card-title">
            <i class="fa-solid fa-clipboard-list" style="color:#60a5fa;"></i>
            Riwayat Maintenance Kendaraan
            <span class="count-badge"><?= $totalData ?></span>
        </div>

        <?php if ($totalData > 0): ?>
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User</th>
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
                        <td><span class="badge badge-info"><?= $row['username'] ?></span></td>
                        <td><?= $row['supir'] ?></td>
                        <td><strong><?= $row['plat'] ?></strong></td>
                        <td><?= $row['kendaraan'] ?></td>
                        <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                        <td style="max-width:400px;line-height:1.6;white-space:normal;">
                            <?php
                            $list = explode(",", $row['checklist']);
                            foreach ($list as $item) {
                                $item = trim($item);
                                if (str_contains($item, "Baik")) {
                                    echo "<span class='badge badge-success'>$item</span> ";
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
                        <td style="max-width:250px;line-height:1.5;white-space:normal;"><?= $row['keterangan'] ?: '-' ?></td>
                        <td>
                            <a href="hapus_maintenance.php?id=<?= $row['id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Yakin ingin menghapus data maintenance ini?')">
                                <i class="fa-solid fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fa-solid fa-clipboard"></i>
            <p>Belum ada data maintenance</p>
        </div>
        <?php endif; ?>
    </div>

</div>
</div>
</body>
</html>
