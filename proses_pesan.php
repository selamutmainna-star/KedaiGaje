<?php
$dataFile = __DIR__ . "/data/pesanan.json";

$nama       = $_POST['nama'] ?? "";
$alamat     = $_POST['alamat'] ?? "";
$telp       = $_POST['telp'] ?? "";
$catatan    = $_POST['catatan'] ?? "";
$pembayaran = $_POST['pembayaran'] ?? "";
$tipe       = $_POST['tipe_layanan'] ?? "";
$wilayah    = $_POST['wilayah'] ?? "-";
$ongkir     = intval($_POST['ongkir'] ?? 0);
$total      = intval($_POST['total'] ?? 0);
$items      = json_decode($_POST['items'], true);

// Load menu.json
$menuFile = __DIR__ . "/data/menu.json";
$menuData = json_decode(file_get_contents($menuFile), true);

// Normalisasi nama
function normalizeName($name) {
    return strtolower(trim(preg_replace('/\s+/', ' ', $name)));
}

// Function cari harga sesuai nama
function findPrice($menuData, $name) {
    $name = normalizeName($name);

    foreach ($menuData as $m) {
        $menuName = normalizeName($m['name']);

        // EXACT MATCH setelah normalisasi
        if ($name === $menuName) {
            return (int)$m['price'];
        }
    }

    // Kedua: cek mengandung kata yang mirip
    foreach ($menuData as $m) {
        $menuName = normalizeName($m['name']);

        if (
            strpos($menuName, $name) !== false ||
            strpos($name, $menuName) !== false
        ) {
            return (int)$m['price'];
        }
    }

    // Ketiga: toleransi typo (levenshtein)
    $best = 0;
    $score = 99;

    foreach ($menuData as $m) {
        $menuName = normalizeName($m['name']);
        $lev = levenshtein($name, $menuName);

        if ($lev < $score && $lev <= 3) {
            $score = $lev;
            $best = (int)$m['price'];
        }
    }

    return $best;
}

// Tambahkan harga asli ke setiap item
function findPriceById($menuData, $id) {
    foreach ($menuData as $m) {
        if ($m['id'] == $id) {
            return intval($m['price']);
        }
    }
    return 0;
}

// pastikan harga ada
foreach ($items as &$it) {
    if (!isset($it['price'])) $it['price'] = 0;
}

$waktu = date("d-m-Y H:i:s");

// VALIDASI
if (empty($nama) || empty($items)) {
    header("Location: index.php?error=1");
    exit;
}

if ($tipe === "Di Antar" && empty($alamat)) {
    header("Location: index.php?error=2");
    exit;
}

if (($tipe === "Di Antar" || $tipe === "Take Away") && empty($telp)) {
    header("Location: index.php?error=3");
    exit;
}

// Buat ID unik
$id = time();

// Ambil file pesanan lama
$all = [];
if (file_exists($dataFile)) {
    $all = json_decode(file_get_contents($dataFile), true);
}

// Simpan data pesanan
$all[] = [
    "id" => $id,
    "nama" => $nama,
    "alamat" => $alamat,
    "telp" => $telp,
    "catatan" => $catatan,
    "items" => $items,
    "total" => $total,
    "ongkir" => $ongkir,
    "wilayah" => $wilayah,
    "tipe_layanan" => $tipe,
    "pembayaran" => $pembayaran,
    "waktu" => $waktu,
    "status" => "baru"   // ← WAJIB ADA!
];

// Simpan ke JSON
file_put_contents($dataFile, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Redirect ke halaman selesai
header("Location: selesai.php?id=$id");
exit;
?>
