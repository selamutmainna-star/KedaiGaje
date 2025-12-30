<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$data_file = __DIR__ . '/data/pesanan.json';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="rekap_semua_selesai.csv"');

// Buka output CSV
$output = fopen('php://output', 'w');

// Header kolom
fputcsv($output, [
    'ID', 'Nama', 'Telp', 'Alamat', 'Item Pesanan', 'Catatan',
    'Total', 'Pembayaran', 'Tipe Layanan', 'Waktu', 'Status'
]);

// Ambil data JSON
$rows = [];
if (file_exists($data_file)) {
    $rows = json_decode(file_get_contents($data_file), true) ?? [];
}

foreach ($rows as $r) {
    if (($r['status'] ?? '') !== 'selesai') continue;

    // Format item pesanan
    $itemsText = "";
    if (!empty($r['items'])) {
        foreach ($r['items'] as $item) {
            $itemsText .= "{$item['name']} x{$item['qty']}; ";
        }
    }

    fputcsv($output, [
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

fclose($output);
exit;
