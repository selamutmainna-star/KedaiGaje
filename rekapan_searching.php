<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$mode  = $_GET['mode']  ?? '';
$value = $_GET['value'] ?? '';

if (empty($mode) || empty($value)) {
    die("Parameter tidak lengkap!");
}

$data_file = __DIR__ . '/data/pesanan.json';

if (!file_exists($data_file)) {
    die("Data tidak ditemukan!");
}

$rows = json_decode(file_get_contents($data_file), true) ?? [];

function parseTanggal($waktu) {
    $parts = explode(' ', $waktu);
    if (count($parts) < 2) return null;

    [$tgl, $jam] = $parts;
    [$d, $m, $y] = explode('-', $tgl);

    return [
        'hari' => $d,
        'bulan' => $m,
        'tahun' => $y
    ];
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="rekapan_filter.csv"');

$out = fopen('php://output', 'w');

fputcsv($out, [
    'ID','Nama','Telp','Alamat','Item Pesanan','Catatan',
    'Total','Pembayaran','Tipe Layanan','Waktu','Status'
]);

foreach ($rows as $r) {
    if (($r['status'] ?? '') !== 'selesai') continue;

    $tgl = parseTanggal($r['waktu']);
    if (!$tgl) continue;

    $tanggalFull = "{$tgl['hari']}-{$tgl['bulan']}-{$tgl['tahun']}";
    $bulanFull   = "{$tgl['bulan']}-{$tgl['tahun']}";

    $cocok = false;

    if ($mode === "hari" && $value === $tanggalFull) {
        $cocok = true;
    }
    elseif ($mode === "bulan" && $value === $bulanFull) {
        $cocok = true;
    }
    elseif ($mode === "tahun" && $value === $tgl['tahun']) {
        $cocok = true;
    }

    if (!$cocok) continue;

    $itemsText = '';
    if (!empty($r['items'])) {
        foreach ($r['items'] as $item) {
            $itemsText .= "{$item['name']} x{$item['qty']}; ";
        }
    }

    fputcsv($out, [
        $r['id'] ?? '',
        $r['nama'] ?? '',
        $r['telp'] ?? '',
        $r['alamat'] ?? '',
        trim($itemsText),
        $r['catatan'] ?? '',
        $r['total'] ?? 0,
        $r['pembayaran'] ?? '',
        $r['tipe_layanan'] ?? '',
        $r['waktu'] ?? '',
        $r['status'] ?? ''
    ]);
}

fclose($out);
exit;
