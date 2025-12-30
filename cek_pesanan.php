<?php
$data_file = __DIR__ . '/data/pesanan.json';

$rows = [];
if (file_exists($data_file)) {
    $rows = json_decode(file_get_contents($data_file), true) ?? [];
}

$belum = 0;

foreach ($rows as $r) {
    if (($r['status'] ?? '') !== 'selesai') {
        $belum++;
    }
}

echo json_encode([
    "belum_selesai" => $belum
]);
