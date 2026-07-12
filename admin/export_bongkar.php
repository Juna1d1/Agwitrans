<?php
session_start();
require "../config/database.php";

if(!isset($_SESSION['user']) || $_SESSION['role'] != 'admin'){
    exit("Akses ditolak!");
}

header("Content-Type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=data_bongkar.xls");

$data = $conn->query("

SELECT 

bongkar.*,
users.username,

detail_bongkar.uang_pelunasan

FROM bongkar

JOIN users
ON bongkar.user_id = users.id

LEFT JOIN detail_bongkar
ON detail_bongkar.bongkar_id = bongkar.id

ORDER BY bongkar.id DESC

");
?>

<table border="1">

<tr>

<th>Tanggal</th>
<th>User</th>

<th>Plat</th>
<th>Type Unit</th>
<th>Supir</th>

<th>Lokasi Bongkar</th>

<th>Jam Tiba</th>
<th>Mulai Bongkar</th>
<th>Selesai Bongkar</th>
<th>Jam Keluar</th>

<th>Keterangan</th>

<th>Pelunasan</th>

</tr>

<?php while($m = $data->fetch_assoc()){ ?>

<tr>

<td><?= $m['tanggal'] ?></td>

<td><?= $m['username'] ?></td>

<td><?= $m['plat'] ?></td>

<td><?= $m['type_unit'] ?></td>

<td><?= $m['supir'] ?></td>

<td><?= $m['lokasi_bongkar'] ?></td>

<td><?= $m['jam_tiba'] ?></td>

<td><?= $m['jam_mulai_bongkar'] ?></td>

<td><?= $m['jam_selesai_bongkar'] ?></td>

<td><?= $m['jam_keluar'] ?></td>

<td><?= $m['keterangan'] ?></td>

<td><?= $m['uang_pelunasan'] ?></td>

</tr>

<?php } ?>

</table>
