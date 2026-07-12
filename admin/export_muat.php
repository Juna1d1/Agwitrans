<?php
session_start();
require "../config/database.php";

if(!isset($_SESSION['user']) || $_SESSION['role'] != 'admin'){
    die("Akses ditolak!");
}

/* ================= SEARCH ================= */

$search = "";

if(isset($_GET['search'])){
    $search = trim($_GET['search']);
}

/* ================= QUERY ================= */

if($search != ''){

    $stmt = $conn->prepare("

    SELECT 

    muat.*,
    users.username,

    detail_muat.uang_jalan_supir,
    detail_muat.dp

    FROM muat

    JOIN users
    ON muat.user_id = users.id

    LEFT JOIN detail_muat
    ON detail_muat.muat_id = muat.id

    WHERE users.username LIKE ?

    ORDER BY muat.id DESC

    ");

    $like = "%$search%";

    $stmt->bind_param("s",$like);

    $stmt->execute();

    $data = $stmt->get_result();

}else{

    $data = $conn->query("

    SELECT 

    muat.*,
    users.username,

    detail_muat.uang_jalan_supir,
    detail_muat.dp

    FROM muat

    JOIN users
    ON muat.user_id = users.id

    LEFT JOIN detail_muat
    ON detail_muat.muat_id = muat.id

    ORDER BY muat.id DESC

    ");
}

/* ================= HEADER EXCEL ================= */

header("Content-Type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=data_muat.xls");

?>

<table border="1">

<tr>

<th>Tanggal</th>
<th>User</th>
<th>Plat</th>
<th>Type Unit</th>
<th>Supir</th>

<th>Lokasi Muat</th>
<th>Tujuan Bongkar</th>

<th>Jam Kedatangan</th>
<th>Jam Mulai Muat</th>
<th>Jam Selesai Muat</th>
<th>Jam Keberangkatan</th>

<th>Keterangan</th>

<th>Uang Jalan</th>
<th>DP</th>

</tr>

<?php while($m = $data->fetch_assoc()){ ?>

<tr>

<td><?= $m['tanggal'] ?></td>

<td><?= $m['username'] ?></td>

<td><?= $m['plat'] ?></td>

<td><?= $m['type_unit'] ?></td>

<td><?= $m['supir'] ?></td>

<td><?= $m['lokasi_muat'] ?></td>

<td><?= $m['tujuan_bongkar'] ?></td>

<td><?= $m['jam_kedatangan'] ?></td>

<td><?= $m['jam_mulai_muat'] ?></td>

<td><?= $m['jam_selesai_muat'] ?></td>

<td><?= $m['jam_keberangkatan'] ?></td>

<td><?= $m['keterangan'] ?></td>

<td><?= $m['uang_jalan_supir'] ?></td>

<td><?= $m['dp'] ?></td>

</tr>

<?php } ?>

</table>
