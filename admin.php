<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

/* ------------------ Paths ------------------ */
$data_file = __DIR__ . '/data/pesanan.json';
$menu_file = __DIR__ . '/data/menu.json';

/* ------------------ Helpers ------------------ */
function safe_json_decode($json) {
    $d = json_decode($json, true);
    return is_array($d) ? $d : [];
}

function parseTanggal($waktu) {
    // expects "DD-MM-YYYY HH:ii:ss"
    $parts = explode(' ', $waktu);
    if (count($parts) < 2) return null;
    [$tgl, $jam] = $parts;
    $dmy = explode('-', $tgl);
    if (count($dmy) !== 3) return null;
    return ['hari' => $dmy[0], 'bulan' => $dmy[1], 'tahun' => $dmy[2]];
}

/* ------------------ Load data ------------------ */
$rows = [];
if (file_exists($data_file)) {
    $raw = file_get_contents($data_file);
    $rows = safe_json_decode($raw);
}
if (!is_array($rows)) $rows = [];

// ensure every entry has a status (backwards-compat)
foreach ($rows as &$r) {
    if (!isset($r['status'])) $r['status'] = 'baru';
    if (!isset($r['waktu'])) $r['waktu'] = date('d-m-Y H:i:s', intval($r['id'] ?? time()));
}
unset($r);

// sort by waktu desc (safe)
usort($rows, function($a, $b) {
    $ta = strtotime($a['waktu'] ?? '0');
    $tb = strtotime($b['waktu'] ?? '0');
    return $tb <=> $ta;
});

/* ------------------ Load menu (optional) ------------------ */
$menuData = [];
if (file_exists($menu_file)) {
    $menuData = safe_json_decode(file_get_contents($menu_file));
}

/* ------------------ ACTIONS ------------------ */
/* mark selesai */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selesai'])) {
    $id = $_POST['selesai'];
    foreach ($rows as &$r) {
        if (($r['id'] ?? null) == $id) {
            $r['status'] = 'selesai';
        }
    }
    unset($r);
    file_put_contents($data_file, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: admin.php');
    exit;
}

/* hapus satu pesanan (via GET link) */
if (isset($_GET['hapus_id'])) {
    $hid = $_GET['hapus_id'];
    $new = [];
    foreach ($rows as $r) {
        if (($r['id'] ?? null) == $hid) continue;
        $new[] = $r;
    }
    $rows = $new;
    file_put_contents($data_file, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: admin.php');
    exit;
}

/* hapus semua */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_semua'])) {
    file_put_contents($data_file, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header('Location: admin.php');
    exit;
}

/* download CSV (rekap selesai semua) */
if (isset($_GET['download']) && $_GET['download'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=rekap_penjualan.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Nama','Telp','Alamat','Items','Catatan','Total','Pembayaran','Tipe Layanan','Waktu','Status']);
    foreach ($rows as $r) {
        if (($r['status'] ?? '') !== 'selesai') continue;
        $itemsText = '';
        if (!empty($r['items']) && is_array($r['items'])) {
            foreach ($r['items'] as $it) {
                $itemsText .= sprintf('%s x%s; ', $it['name'] ?? '-', $it['qty'] ?? 0);
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
}

/* ------------------ Rekap per hari/bulan/tahun (hanya selesai) ------------------ */
$totalCash = 0;
$totalTransfer = 0;
$rekap = [
    'hari' => ['cash'=>0,'transfer'=>0,'total'=>0],
    'bulan' => ['cash'=>0,'transfer'=>0,'total'=>0],
    'tahun' => ['cash'=>0,'transfer'=>0,'total'=>0],
];

$hariNow = date('d'); $bulanNow = date('m'); $tahunNow = date('Y');

foreach ($rows as $r) {
    if (($r['status'] ?? '') !== 'selesai') continue;

    $pay = strtolower($r['pembayaran'] ?? '');
    $total = (int)($r['total'] ?? 0);

    if ($pay === 'cash') $totalCash += $total;
    else $totalTransfer += $total;

    $tgl = parseTanggal($r['waktu'] ?? '');
    if (!$tgl) continue;

    if ($tgl['hari'] == $hariNow && $tgl['bulan'] == $bulanNow && $tgl['tahun'] == $tahunNow) {
        if ($pay === 'cash') $rekap['hari']['cash'] += $total; else $rekap['hari']['transfer'] += $total;
        $rekap['hari']['total'] += $total;
    }
    if ($tgl['bulan'] == $bulanNow && $tgl['tahun'] == $tahunNow) {
        if ($pay === 'cash') $rekap['bulan']['cash'] += $total; else $rekap['bulan']['transfer'] += $total;
        $rekap['bulan']['total'] += $total;
    }
    if ($tgl['tahun'] == $tahunNow) {
        if ($pay === 'cash') $rekap['tahun']['cash'] += $total; else $rekap['tahun']['transfer'] += $total;
        $rekap['tahun']['total'] += $total;
    }
}
$totalSemua = $totalCash + $totalTransfer;

/* ------------------ Searching rekap (session show-once) ------------------ */
$showResult = false;
$searchCash = $searchTransfer = $searchTotal = 0;
$mode = $_GET['mode'] ?? '';
$value = $_GET['value'] ?? '';

if (isset($_GET['reset'])) {
    unset($_SESSION['search_result']);
    unset($_SESSION['search_once']);
    header('Location: admin.php');
    exit;
}

if (!empty($_GET['mode']) && !empty($_GET['value']) && !isset($_SESSION['search_result'])) {
    $tmpCash = $tmpTransfer = $tmpTotal = 0;
    foreach ($rows as $r) {
        if (($r['status'] ?? '') !== 'selesai') continue;
        $tgl = parseTanggal($r['waktu'] ?? '');
        if (!$tgl) continue;
        $tanggalFull = "{$tgl['hari']}-{$tgl['bulan']}-{$tgl['tahun']}";
        $bulanFull = "{$tgl['bulan']}-{$tgl['tahun']}";
        $cocok = false;
        if ($_GET['mode'] === 'hari' && $_GET['value'] === $tanggalFull) $cocok = true;
        if ($_GET['mode'] === 'bulan' && $_GET['value'] === $bulanFull) $cocok = true;
        if ($_GET['mode'] === 'tahun' && $_GET['value'] === $tgl['tahun']) $cocok = true;
        if ($cocok) {
            $pay = strtolower($r['pembayaran'] ?? '');
            $tot = (int)($r['total'] ?? 0);
            if ($pay === 'cash') $tmpCash += $tot; else $tmpTransfer += $tot;
            $tmpTotal += $tot;
        }
    }
    $_SESSION['search_result'] = ['mode'=>$_GET['mode'],'value'=>$_GET['value'],'cash'=>$tmpCash,'transfer'=>$tmpTransfer,'total'=>$tmpTotal];
    $_SESSION['search_once'] = true;
    header('Location: admin.php');
    exit;
}

if (!empty($_SESSION['search_result']) && !empty($_SESSION['search_once'])) {
    $sr = $_SESSION['search_result'];
    $searchCash = (int)($sr['cash'] ?? 0);
    $searchTransfer = (int)($sr['transfer'] ?? 0);
    $searchTotal = (int)($sr['total'] ?? 0);
    $mode = $sr['mode'] ?? '';
    $value = $sr['value'] ?? '';
    $showResult = true;
    unset($_SESSION['search_result']);
    unset($_SESSION['search_once']);
}

/* ------------------ Output HTML ------------------ */
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Kedai Gaje</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
:root{
  --cream:#f8f3e7; --brown:#6b3e26; --light:#a2714e;
}
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #3c60ff, #83c1f1, #71f8ef, #4af575);
    background-size: 400% 400%;
    animation: bgMove 14s ease infinite;
}

@keyframes bgMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}


.header {
    background: rgba(255,255,255,0.35);
    backdrop-filter: blur(10px);
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid rgba(255,255,255,0.3);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

    @keyframes slideDown {
        from { transform: translateY(-100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .header h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: white;
        display: flex;
        align-items: center;
        gap: 10px;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    .header-title {
    display: flex;
    align-items: center;   /* Biar logo & teks sejajar tengah */
    gap: 8px;              /* Jarak antara logo dan teks */
}

.header-logo {
    width: 60px;      /* ukuran lebih kecil */
    height: 60px;
    object-fit: contain;
    margin-top: 0px;  /* sedikit turun kalau masih kurang sejajar */
}

/* --- Tambahkan atau Ganti Style .btn --- */
.header-actions {
    display: flex;
    gap: 10px; /* Jarak antara tombol */
    align-items: center;
}

.btn {
    padding: 10px 18px; /* Padding lebih ramping */
    text-decoration: none;
    font-weight: 600;
    border-radius: 10px;
    border: none; /* Hilangkan border lama */
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    cursor: pointer;
}

/* Style Khusus untuk Tombol Kelola Menu (Biru Teal) */
.btn-menu {
    background:rgb(80, 173, 255);
    color: white;
    border: 2px solid rgba(166, 228, 241, 0.8);
}

.btn-menu:hover {
    background: #007f99; /* Warna sedikit lebih gelap saat hover */
    border-color: #007f99;
    transform: translateY(-2px) scale(1.02); /* Animasi naik sedikit */
    box-shadow: 0 6px 15px rgba(133, 218, 255, 0.9);
}

/* Style Khusus untuk Tombol Logout (Merah) */
.btn-logout {
    background:rgb(255, 255, 255); /* Warna Merah Gelap */
    color: rgb(55, 0, 0);
    border: 2px rgb(224, 171, 176);
    
}

.btn-logout:hover {
    background:rgb(249, 145, 154); /* Warna sedikit lebih gelap saat hover */
    border-color: #8a1f2a;
    transform: translateY(-2px) scale(1.02); /* Animasi naik sedikit */
    box-shadow: 0 6px 15px rgba(176, 42, 55, 0.4);
}

/* Hapus style .btn lama jika masih ada, atau ganti sepenuhnya */
/*
.btn {background: rgb(80, 173, 255);
    color: #4a2c16;
    padding: 12px 20px;
    ... (dan seterusnya, hapus bagian ini)
}
*/


.container{padding:20px}
.table-wrap{background:#fff;border-radius:10px;padding:12px;box-shadow:0 6px 18px rgba(0,0,0,0.06)}
/* === TABEL GAYA ADMIN_MENU (biru pastel) === */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

th {
    background: rgb(106, 193, 208);
    padding: 12px;
    font-size: 14px;
    color: #08304b;
    text-align: center;
}

td {
    padding: 10px;
    border-bottom: 1px solid #eee;
    text-align: center;
}

tr:hover {
    background: rgb(206, 242, 251);
}

/* Biar kolom "baru" ga merusak warna hover */
.new-row {
    background:rgb(225, 251, 244) !important;
}

.tr-compact td{padding:6px 8px}
.small{font-size:12px;color:#666}
.status-selesai{color:green;font-weight:700}
.tag-baru{background:#fff3cd;padding:4px 6px;border-radius:6px;font-size:12px}
.tooltip { position: relative; cursor: pointer; color: #444; }
.tooltip .tip { visibility: hidden; opacity: 0; position: absolute; left: 0; top: 120%; background: #333; color: #fff; padding: 8px; border-radius:6px; min-width:160px; z-index:20; transition: .15s ease; font-size:12px; }
.tooltip:hover .tip { visibility: visible; opacity: 1; transform: translateY(0); }

/* responsive */
@media (max-width: 900px){
  table{font-size:12px}
  .hide-mobile{display:none}

  /* === FORM CARI REKAP (baru, lebih modern) === */
.search-box {
    background: #ffffffdd;
    backdrop-filter: blur(3px);
    padding: 18px;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.07);
    margin-top: 25px;
    border: 1px solid #e7e7e7;
}

.search-box h4 {
    margin: 0 0 12px 0;
    font-weight: 600;
    color: #05425c;
}

.search-flex {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.search-flex select,
.search-flex input {
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #cfd9df;
    font-size: 14px;
    outline: none;
}

.search-flex select:focus,
.search-flex input:focus {
    border-color: #71c9eb;
    box-shadow: 0 0 0 2px #bdefff;
}

/* Tombol lebih cantik */
.btn-brown {
    background: rgb(0, 134, 155);
    color: white;
    border-radius: 10px;
    padding: 9px 15px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    font-weight: 600;
}

.btn-brown:hover {
    background: rgb(0, 102, 120);
}

.btn-reset {
    background: #d9534f;
    color: white;
    padding: 9px 15px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
}

.btn-reset:hover {
    background:#b7322f;
}

/* === CARD HASIL PENCARIAN === */
.search-result-card {
    margin-top: 12px;
    background: #f9ffff;
    border-left: 5px solid #00a3c8;
    padding: 12px 15px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.search-result-card p {
    margin: 6px 0;
    font-size: 14px;
}

.search-result-card .btn {
    margin-top: 6px;
}
/* === FORM PENCARIAN REKAP === */
.search-box {
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    border: 1px solid rgba(255,255,255,0.3);
    margin-top: 25px;
    transition: all 0.3s ease;
}

.search-box:hover {
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}

.search-box h4 {
    margin-bottom: 14px;
    font-weight: 600;
    color: #05425c;
}

.search-flex {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}

.search-flex select,
.search-flex input {
    padding: 12px 16px;
    border-radius: 12px;
    border: 1px solid rgba(0,0,0,0.1);
    font-size: 14px;
    outline: none;
    transition: all 0.2s ease;
}

.search-flex select:focus,
.search-flex input:focus {
    border-color: #00a3c8;
    box-shadow: 0 0 0 3px rgba(0,163,200,0.2);
}

/* Tombol */
.btn-brown {
    background: #00a3c8;
    color: white;
    border-radius: 12px;
    padding: 10px 18px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: 0.2s;
}
.btn-brown:hover { background: #007f99; transform: translateY(-2px); }

.btn-reset {
    background: #b02a37;
    color: white;
    border-radius: 12px;
    padding: 10px 18px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: 0.2s;
}
.btn-reset:hover { background: #8a1f2a; transform: translateY(-2px); }

/* Card hasil pencarian */
.search-result-card {
    margin-top: 16px;
    background: rgba(255,255,255,0.25);
    backdrop-filter: blur(8px);
    padding: 16px 20px;
    border-radius: 14px;
    border-left: 5px solid #00a3c8;
    box-shadow: 0 8px 22px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}
.search-result-card:hover { box-shadow: 0 12px 28px rgba(0,0,0,0.15); }
.search-result-card p { margin: 6px 0; font-size: 14px; }

    /* Gambar styling */
    td img {
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    td img:hover {
        transform: scale(2);
    }
.header-logo {
    width: 30px;   /* besarkan di sini */
    height: 30px;  /* samain biar proporsional */
    object-fit: contain;
}


}
</style>
</head>
<body>
<header class="header">
    <h2 class="header-title">
        <img src="brand.png" class="header-logo">
        Admin Kedai Gaje
    </h2>
    <div class="header-actions"> <a class="btn btn-menu" href="admin_menu.php">
            <i class="fa-solid fa-list"></i> Kelola Menu
        </a>
        <a class="btn btn-logout" href="logout.php">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</header>

<div class="container">
  <h3 style="margin:10px 0"><i class="fa-solid fa-receipt"></i> Daftar Pesanan (Semua Status)</h3>

  <div class="table-wrap">
    <table>
      <thead>
        <tr class="tr-compact">
          <th style="width:70px">ID</th>
          <th style="width:140px">Nama</th>
          <th class="hide-mobile" style="width:110px">Telp</th>
          <th style="width:90px">Layanan</th>
          <th style="width:90px">Wilayah</th>
          <th style="width:70px">Ongkir</th>
          <th class="hide-mobile" style="width:140px">Alamat</th>
          <th>Pesanan</th>
          <th class="hide-mobile" style="width:120px">Catatan</th>
          <th style="width:90px">Total</th>
          <th style="width:100px">Bayar</th>
          <th style="width:140px">Waktu</th>
          <th style="width:90px">Status</th>
          <th style="width:80px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr class="tr-compact <?= (($r['status'] ?? '') === 'baru') ? 'new-row' : '' ?>">
          <td><?= htmlspecialchars($r['id'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['nama'] ?? '-') ?></td>
          <td class="hide-mobile small"><?= htmlspecialchars($r['telp'] ?? '-') ?></td>
          <td class="small"><?= htmlspecialchars($r['tipe_layanan'] ?? '-') ?></td>
          <td class="small"><?= htmlspecialchars($r['wilayah'] ?? '-') ?></td>
          <td class="small">Rp <?= number_format((int)($r['ongkir'] ?? 0)) ?></td>
          <td class="hide-mobile small">
            <?php if (!empty($r['alamat'])): ?>
              <div class="tooltip"><?= htmlspecialchars(substr($r['alamat'],0,20)) ?>...
                <div class="tip"><?= nl2br(htmlspecialchars($r['alamat'])) ?></div>
              </div>
            <?php else: echo '-'; endif; ?>
          </td>

          <td>
            <?php
            if (!empty($r['items']) && is_array($r['items'])) {
                foreach ($r['items'] as $it) {
                    $qty = (int)($it['qty'] ?? 0);
                    $price = (int)($it['price'] ?? 0);
                    $subtotal = $qty * $price;
                    echo htmlspecialchars($it['name'] ?? '-') . " x{$qty} — Rp " . number_format($subtotal) . "<br>";
                }
            } else {
                echo "-";
            }
            ?>
          </td>

          <td class="hide-mobile small">
            <?php if (!empty($r['catatan'])): ?>
              <div class="tooltip"><?= htmlspecialchars(substr($r['catatan'],0,20)) ?>...
                <div class="tip"><?= nl2br(htmlspecialchars($r['catatan'])) ?></div>
              </div>
            <?php else: echo '-'; endif; ?>
          </td>

          <td>Rp <?= number_format((int)($r['total'] ?? 0)) ?></td>
          <td class="small"><?= htmlspecialchars($r['pembayaran'] ?? '-') ?></td>
          <td class="small"><?= htmlspecialchars($r['waktu'] ?? '-') ?></td>

          <td>
            <?php if (($r['status'] ?? '') === 'selesai'): ?>
              <span class="status-selesai"><i class="fa-solid fa-circle-check"></i> Selesai</span>
            <?php else: ?>
              <span class="tag-baru">Baru</span>
            <?php endif; ?>
          </td>

          <td>
            <?php if (($r['status'] ?? '') !== 'selesai'): ?>
              <form method="post" style="display:inline">
                <button type="submit" name="selesai" value="<?= htmlspecialchars($r['id'] ?? '') ?>" class="small">Centang</button>
              </form>
            <?php endif; ?>
            <a href="admin.php?hapus_id=<?= urlencode($r['id'] ?? '') ?>" onclick="return confirm('Hapus pesanan?')" style="display:inline-block;margin-left:6px;color:#b02a37">Hapus</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- REKAP -->
  <div style="display:flex;gap:18px;margin-top:18px;flex-wrap:wrap">
    <div style="flex:1;min-width:220px;background:#fff;padding:12px;border-radius:10px;box-shadow:0 6px 14px rgba(0,0,0,0.04)">
      <h4 style="margin:6px 0">Rekap Hari Ini</h4>
      <p><strong>Total:</strong> Rp <?= number_format($rekap['hari']['total']) ?></p>
      <p class="small">Cash: Rp <?= number_format($rekap['hari']['cash']) ?> • Transfer: Rp <?= number_format($rekap['hari']['transfer']) ?></p>
    </div>

    <div style="flex:1;min-width:220px;background:#fff;padding:12px;border-radius:10px;box-shadow:0 6px 14px rgba(0,0,0,0.04)">
      <h4 style="margin:6px 0">Rekap Bulan Ini</h4>
      <p><strong>Total:</strong> Rp <?= number_format($rekap['bulan']['total']) ?></p>
      <p class="small">Cash: Rp <?= number_format($rekap['bulan']['cash']) ?> • Transfer: Rp <?= number_format($rekap['bulan']['transfer']) ?></p>
    </div>

    <div style="flex:1;min-width:220px;background:#fff;padding:12px;border-radius:10px;box-shadow:0 6px 14px rgba(0,0,0,0.04)">
      <h4 style="margin:6px 0">Rekap Tahun Ini</h4>
      <p><strong>Total:</strong> Rp <?= number_format($rekap['tahun']['total']) ?></p>
      <p class="small">Cash: Rp <?= number_format($rekap['tahun']['cash']) ?> • Transfer: Rp <?= number_format($rekap['tahun']['transfer']) ?></p>
    </div>
  </div>

  <!-- TOTALS & ACTIONS -->
  <div style="margin-top:18px;display:flex;gap:12px;flex-wrap:wrap;align-items:center">
    <div style="background:#fff;padding:12px;border-radius:10px;box-shadow:0 6px 14px rgba(0,0,0,0.04)">
      <h4 style="margin:6px 0">Total Semua</h4>
      <p><strong>Rp <?= number_format($totalSemua) ?></strong></p>
      <p class="small">Cash: Rp <?= number_format($totalCash) ?> • Transfer: Rp <?= number_format($totalTransfer) ?></p>
    </div>

    <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
      <a href="admin.php?download=csv" class="btn" style="background:#333;color:#fff"><i class="fa-solid fa-file-arrow-down"></i> Unduh Rekap Selesai</a>

      <form method="post" onsubmit="return confirm('Hapus semua pesanan?')">
        <input type="hidden" name="hapus_semua" value="1">
        <button type="submit" class="btn" style="background:#b02a37;color:#fff">Hapus Semua</button>
      </form>
    </div>
  </div>

  <!-- PENCARIAN REKAP -->
  <div class="search-box" id="search-section">
    <h4><i class="fa-solid fa-magnifying-glass-chart"></i> Cari Rekapan</h4>

    <form method="get" action="#search-section" class="search-flex">
        <select name="mode" id="mode" onchange="updateInputField()">
            <option value="">-- Pilih --</option>
            <option value="hari" <?= ($mode=='hari')?'selected':'' ?>>Hari</option>
            <option value="bulan" <?= ($mode=='bulan')?'selected':'' ?>>Bulan</option>
            <option value="tahun" <?= ($mode=='tahun')?'selected':'' ?>>Tahun</option>
        </select>

        <div id="input-field" style="min-width:180px"></div>

        <button type="submit" class="btn-brown">Cari</button>
    </form>

    <div id="search-result">
      <?php if ($showResult): ?>
        <div class="search-result-card">
            <?php if ($searchTotal > 0): ?>
              <p><strong>Hasil:</strong> Rp <?= number_format($searchTotal) ?></p>
              <p>Cash: Rp <?= number_format($searchCash) ?> • Transfer: Rp <?= number_format($searchTransfer) ?></p>

              <a href="rekapan_searching.php?mode=<?= urlencode($mode) ?>&value=<?= urlencode($value) ?>" 
                 class="btn" style="background:#333;color:#fff">
                Unduh Hasil
              </a>

            <?php else: ?>
              <p style="color:#b02a37">Tidak ada penjualan untuk kriteria ini.</p>
            <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

</div>

<script>
/* --- update input field for search --- */
function updateInputField() {
    const mode = document.getElementById('mode').value;
    const place = document.getElementById('input-field');
    const phpValue = <?= json_encode($value) ?>;

    let html = '';
    if (mode === 'hari') html = `<input type="text" name="value" placeholder="DD-MM-YYYY" value="${phpValue}" style="width:180px;padding:10px 14px;border-radius:12px;border:1px solid rgba(0,0,0,0.15)">`;
    else if (mode === 'bulan') html = `<input type="text" name="value" placeholder="MM-YYYY" value="${phpValue}" style="width:180px;padding:10px 14px;border-radius:12px;border:1px solid rgba(0,0,0,0.15)">`;
    else if (mode === 'tahun') html = `<input type="text" name="value" placeholder="YYYY" value="${phpValue}" style="width:120px;padding:10px 14px;border-radius:12px;border:1px solid rgba(0,0,0,0.15)">`;
    place.innerHTML = html;
}
updateInputField();

/* --- auto-check new orders (calls cek_pesanan.php) --- */
let lastCount = <?= count(array_filter($rows, fn($r) => ($r['status'] ?? '') !== 'selesai')) ?>;

async function cekPesananBaru() {
    try {
        const res = await fetch('cek_pesanan.php');
        const data = await res.json();
        const current = data.belum_selesai ?? 0;
        if (current > lastCount) {
            // play sound
            try { new Audio('sound.mp3').play(); } catch(e){}
            // highlight first row
            const first = document.querySelector('tbody tr');
            if (first) {
                first.style.transition = 'background 0.4s';
                first.style.background = '#fff3cd';
                setTimeout(()=> first.style.background = '', 3500);
            }
        }
        lastCount = current;
    } catch(e) {
        console.log('cek failed', e);
    }
}
setInterval(cekPesananBaru, 4000);
</script>

</body>
</html>
