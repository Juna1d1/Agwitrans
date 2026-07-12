<?php
session_start();
require "../config/database.php";
require "../includes/auth-check.php";
requireAdmin();

require "../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$items = [
    "Oli Mesin", "Oli Hidrolik", "Oli Power Steering", "Air Radiator",
    "Minyak Rem", "Fisik Ban", "Tekanan Angin Ban", "Lampu",
    "Kebersihan", "Track-Belt", "Terpal", "Gembok"
];

$data = mysqli_query($conn, "
    SELECT maintenance.*, users.username
    FROM maintenance
    JOIN users ON maintenance.user_id = users.id
    ORDER BY maintenance.tanggal DESC
");

$colCount = 6 + count($items) + 1;
$lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$centerWrap = [
    "horizontal" => Alignment::HORIZONTAL_CENTER,
    "vertical" => Alignment::VERTICAL_CENTER,
];
$thinBorder = [
    "borderStyle" => Border::BORDER_THIN,
    "color" => ["rgb" => "BFBFBF"],
];

// ===== Row 1: Title =====
$sheet->mergeCells("A1:{$lastCol}1");
$sheet->setCellValue("A1", "DATA MAINTENANCE KENDARAAN");
$sheet->getStyle("A1")->applyFromArray([
    "font" => ["bold" => true, "size" => 14, "color" => ["rgb" => "FFFFFF"], "name" => "Arial"],
    "fill" => ["fillType" => Fill::FILL_SOLID, "startColor" => ["rgb" => "1F4E79"]],
    "alignment" => $centerWrap,
]);
$sheet->getRowDimension(1)->setRowHeight(24);

// ===== Row 2: Group header =====
$infoHeaders = ["No", "User", "Supir", "Plat", "Kendaraan", "Tanggal"];
foreach ($infoHeaders as $i => $h) {
    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
    $sheet->setCellValue("{$col}2", $h);
}

$checklistStart = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7);
$checklistEnd = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(6 + count($items));
$sheet->mergeCells("{$checklistStart}2:{$checklistEnd}2");
$sheet->setCellValue("{$checklistStart}2", "CHECKLIST KONDISI");

$keteranganCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);
$sheet->setCellValue("{$keteranganCol}2", "Keterangan");

$sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
    "font" => ["bold" => true, "size" => 10, "color" => ["rgb" => "FFFFFF"], "name" => "Arial"],
    "fill" => ["fillType" => Fill::FILL_SOLID, "startColor" => ["rgb" => "4472C4"]],
    "alignment" => $centerWrap,
    "borders" => ["allBorders" => $thinBorder],
]);
$sheet->getRowDimension(2)->setRowHeight(18);

// ===== Row 3: Sub-header =====
for ($i = 1; $i <= $colCount; $i++) {
    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
    $idx = $i - 7;
    $val = ($idx >= 0 && $idx < count($items)) ? $items[$idx] : "";
    $sheet->setCellValue("{$col}3", $val);
}

$sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
    "font" => ["bold" => true, "size" => 9, "color" => ["rgb" => "FFFFFF"], "name" => "Arial"],
    "fill" => ["fillType" => Fill::FILL_SOLID, "startColor" => ["rgb" => "375623"]],
    "alignment" => ["horizontal" => Alignment::HORIZONTAL_CENTER, "vertical" => Alignment::VERTICAL_CENTER, "wrapText" => true],
    "borders" => ["allBorders" => $thinBorder],
]);
$sheet->getRowDimension(3)->setRowHeight(38);

// ===== Data rows =====
$no = 1;
$rowNum = 4;
$evenFill = ["fillType" => Fill::FILL_SOLID, "startColor" => ["rgb" => "DEEAF1"]];
$oddFill = ["fillType" => Fill::FILL_SOLID, "startColor" => ["rgb" => "FFFFFF"]];

while ($r = mysqli_fetch_assoc($data)) {
    $bgFill = ($no % 2 === 0) ? $evenFill : $oddFill;

    // Parse checklist: "Oli Mesin (Baik), Oli Hidrolik (Perlu Ganti/Tambah), ..."
    $parsed = [];
    $checklistRaw = $r['checklist'] ?? '';
    if ($checklistRaw !== '') {
        $chunks = explode(",", $checklistRaw);
        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if (preg_match('/^(.+?) \((Baik|Perlu Ganti\/Tambah)\)$/', $chunk, $m)) {
                $parsed[trim($m[1])] = $m[2];
            }
        }
    }

    // Tanggal
    $tanggal = $r['tanggal'] ?? '';
    $tglVal = ($tanggal !== '') ? date("d-m-Y", strtotime($tanggal)) : '-';

    // Info columns
    $infoData = [$no, $r['username'] ?? '-', $r['supir'] ?? '-', $r['plat'] ?? '-', $r['kendaraan'] ?? '-', $tglVal];
    foreach ($infoData as $i => $val) {
        $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
        $sheet->setCellValue("{$col}{$rowNum}", $val);
    }

    // Checklist columns
    foreach ($items as $i => $item) {
        $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7 + $i);
        $cell = "{$col}{$rowNum}";

        if (isset($parsed[$item])) {
            if ($parsed[$item] === "Baik") {
                $sheet->setCellValue($cell, "✔ Baik");
                $sheet->getStyle($cell)->applyFromArray([
                    "font" => ["color" => ["rgb" => "375623"], "size" => 9, "name" => "Arial"],
                    "fill" => ["fillType" => Fill::FILL_SOLID, "startColor" => ["rgb" => "E2EFDA"]],
                ]);
            } else {
                $sheet->setCellValue($cell, "⚠ Rusak");
                $sheet->getStyle($cell)->applyFromArray([
                    "font" => ["bold" => true, "color" => ["rgb" => "833C00"], "size" => 9, "name" => "Arial"],
                    "fill" => ["fillType" => Fill::FILL_SOLID, "startColor" => ["rgb" => "FCE4D6"]],
                ]);
            }
        } else {
            $sheet->setCellValue($cell, "—");
            $sheet->getStyle($cell)->applyFromArray([
                "font" => ["color" => ["rgb" => "AAAAAA"], "size" => 9, "name" => "Arial"],
                "fill" => ["fillType" => Fill::FILL_SOLID, "startColor" => ["rgb" => "F2F2F2"]],
            ]);
        }
    }

    // Keterangan
    $keterangan = $r['keterangan'] ?? '';
    $sheet->setCellValue("{$keteranganCol}{$rowNum}", ($keterangan !== '') ? $keterangan : '-');

    // Row styling
    $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->applyFromArray([
        "font" => ["size" => 9, "name" => "Arial"],
        "fill" => $bgFill,
        "alignment" => $centerWrap,
        "borders" => ["allBorders" => $thinBorder],
    ]);
    $sheet->getStyle("{$keteranganCol}{$rowNum}")->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_LEFT);

    $sheet->getRowDimension($rowNum)->setRowHeight(22);
    $rowNum++;
    $no++;
}

// ===== Column widths =====
$widths = [5, 10, 12, 13, 13, 11];
foreach ($widths as $i => $w) {
    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
    $sheet->getColumnDimension($col)->setWidth($w);
}
for ($i = 7; $i <= 6 + count($items); $i++) {
    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
    $sheet->getColumnDimension($col)->setWidth(12);
}
$sheet->getColumnDimension($keteranganCol)->setWidth(25);

// ===== Freeze panes =====
$freezeCell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(7) . "4";
$sheet->freezePane($freezeCell);

// ===== Output =====
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=data_maintenance_rapih.xlsx");
header("Cache-Control: max-age=0");

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$writer->save("php://output");
exit;
