<?php
$data_file = __DIR__ . '/data/pesanan.json';

$rows = [];
if (file_exists($data_file)) {
    $rows = json_decode(file_get_contents($data_file), true);
}

?>

<table border="1" width="100%">
    <tr>
        <th>ID</th>
        <th>Nama</th>
        <th>Tipe</th>
        <th>Total</th>
        <th>Status</th>
    </tr>

    <?php foreach($rows as $r): ?>
        <?php if(($r['status'] ?? '') !== 'selesai'): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= $r['nama'] ?></td>
            <td><?= $r['tipe_layanan'] ?></td>
            <td>Rp <?= number_format($r['total'],0,',','.') ?></td>
            <td><?= $r['status'] ?></td>
        </tr>
        <?php endif; ?>
    <?php endforeach; ?>
</table>
