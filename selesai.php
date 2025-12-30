<?php
// success.php — tampilkan detail pesanan berhasil
$id = $_GET['id'] ?? '';
$dataFile = __DIR__ . '/data/pesanan.json';
$order = null;

// Fungsi format rupiah
function rupiah($n) {
  return 'Rp ' . number_format($n, 0, ',', '.');
}

// Ambil data pesanan dari file JSON
if ($id && file_exists($dataFile)) {
    $all = json_decode(file_get_contents($dataFile), true);
    foreach ($all as $p) {
        if ($p['id'] == $id) {
            $order = $p;
            break;
        }
    }
}

$layanan = $order['tipe_layanan'] ?? '-';
$wilayah = $order['wilayah'] ?? '-';
$ongkir  = $order['ongkir'] ?? 0;

$layananNormal = strtolower(str_replace(" ", "", $layanan));

$totalAkhir = $order['total'];  // SUDAH ADA ONGKIR DARI script.js

?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pesanan Berhasil - Kedai Gaje</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* ================= BACKGROUND GRADIENT + FOOD FALLING ================= */
body {
    margin: 0;
    font-family: "Poppins", sans-serif;
    background: linear-gradient(135deg, #3c60ff, #83c1f1, #71f8ef, #4af575);
    background-size: 400% 400%;
    animation: bgMove 10s ease infinite;
    display: flex;
    justify-content: center;
    padding: 40px;
    overflow-x: hidden;
}

@keyframes bgMove {
    0% { background-position: 0% 0%; }
    50% { background-position: 100% 100%; }
    100% { background-position: 0% 0%; }
}

/* --- Food Falling Animation --- */
.food {
    position: fixed;
    top: -50px;
    font-size: 35px;
    opacity: 0.8;
    animation: fall linear infinite;
    z-index: 0;
}

@keyframes fall {
    0% { transform: translateY(-50px) rotate(0deg); opacity: 1; }
    100% { transform: translateY(120vh) rotate(360deg); opacity: 0; }
}

/* ================= CARD ================= */
.container {
    position: relative;
    width: 100%;
    max-width: 650px;
    background: rgba(255,255,255,0.82);
    backdrop-filter: blur(15px);
    padding: 35px;
    border-radius: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.18);
    animation: floatIn 1s ease-out;
    border: 1px solid rgba(255,255,255,0.5);
    z-index: 10;
}

@keyframes floatIn {
    from { opacity: 0; transform: translateY(40px) scale(0.95); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* --- Card Glow Animation --- */
.container:hover {
    transition: 0.4s;
    box-shadow: 0 0 25px rgba(255,255,255,0.35);
}

/* ================= TITLE ================= */
h2 {
    text-align: center;
    font-size: 27px;
    color: #3a2e2e;
    margin-bottom: 25px;
    animation: fadeDown 0.8s ease-out;
}

@keyframes fadeDown {
    from { opacity: 0; transform: translateY(-15px); }
    to   { opacity: 1; transform: translateY(0); }
}

h2 i {
    animation: wobble 2s infinite;
}

@keyframes wobble {
    0%   { transform: rotate(0deg); }
    50%  { transform: rotate(8deg); }
    100% { transform: rotate(0deg); }
}

p, li {
    font-size: 16px;
    color: #333;
    line-height: 1.6;
}

/* ================= TOTAL BOX ================= */
.total-card {
    background: white;
    padding: 15px 20px;
    border-radius: 18px;
    margin: 20px 0;
    border-left: 6px solid #6b4caf;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    animation: popIn 0.7s ease-out;
}

@keyframes popIn {
    from { transform: scale(0.85); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
}

/* ================= BUTTONS ================= */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 13px 22px;
    border-radius: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: 0.25s ease;
    position: relative;
    overflow: hidden;
}

.btn i { font-size: 18px; }

/* --- Hover glow + ripple effect --- */
.btn::after {
    content: "";
    position: absolute;
    width: 0;
    height: 0;
    background: rgba(255,255,255,0.4);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    opacity: 0;
}

.btn:hover::after {
    width: 200%;
    height: 200%;
    top: 50%;
    left: 50%;
    opacity: 1;
    transition: 0.4s ease;
}

/* Button styles */
.btn-download { background:#6b3e26; color:white; }
.btn-download:hover { background:#8b5438; }

.btn-wa { background:#25D366; color:white; }
.btn-wa:hover { background:#1ebe57; }

.btn-home { background:#4b7bec; color:white; }
.btn-home:hover { background:#3968d6; }

.button-row { display:flex; justify-content:center; gap:15px; margin-top:10px; }

.center-btn { display:flex; justify-content:center; margin-top:20px; }

hr { border:none; height:1px; background:#ddd; margin:25px 0; }

</style>
</head>

<body>
    <script>
const foods = ["🍔","🍟","🍕","🍗","🥤","🍱","🍣","🍩","🧋","🍦"];
for (let i = 0; i < 16; i++) {
    let f = document.createElement("div");
    f.className = "food";
    f.innerHTML = foods[Math.floor(Math.random()*foods.length)];
    f.style.left = Math.random()*100 + "vw";
    f.style.animationDuration = (5 + Math.random()*5) + "s";
    f.style.fontSize = (25 + Math.random()*20) + "px";
    document.body.appendChild(f);
}
</script>

<div class="container">

  <h2><i class="fa-solid fa-check-circle" style="color:#4b7bec"></i> Pesanan Berhasil!</h2>

<?php if ($order): ?>

    <p><strong>ID Pesanan:</strong> <?=$order['id']?></p>
    <p><strong>Nama:</strong> <?=$order['nama']?></p>
    <?php if ($layananNormal === "diantar"): ?>
    <p><strong>Alamat:</strong> <?=$order['alamat']?></p>
<?php endif; ?>

    <p><strong>No. Telp:</strong> <?=$order['telp']?></p>
    <p><strong>Tanggal & Jam:</strong> <?=$order['waktu']?></p>
    <p><strong>Catatan Pesanan:</strong> <?=nl2br($order['catatan'])?></p>

    <h3>Detail Pesanan:</h3>
    <ul>
        <?php foreach($order['items'] as $item): ?>
            <li><?=$item['name']?> (x<?=$item['qty']?>) - <?=rupiah($item['price'])?></li>
        <?php endforeach; ?>
    </ul>

    <div class="total-card">
        <p><strong>Tipe Layanan:</strong> <?=$layanan?></p>

        <?php if ($layananNormal === "diantar"): ?>
            <p><strong>Wilayah:</strong> <?=$wilayah?></p>
            <p><strong>Ongkir:</strong> <?=rupiah($ongkir)?></p>
        <?php endif; ?>

        <h3><strong>Total Akhir:</strong> <?=rupiah($totalAkhir)?></h3>
    </div>

 <?php if (strtolower($order['pembayaran']) === 'transfer'): ?>
    <div style="
        margin-top: 20px;
        padding: 18px 20px;
        background: #ffffffd9;
        border-left: 5px solid #6b4caf;
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    ">
        <h3 style="margin:0 0 10px 0; font-size:18px;">Informasi Pembayaran</h3>

        <p style="margin:4px 0;">Silakan transfer ke rekening berikut, lalu kirim bukti transfer melalui tombol WhatsApp di bawah!</p>

        <table style="margin-top:12px; width:100%; border-collapse: collapse;">
            <tr>
                <td style="padding:6px 0; font-weight:600;">Bank</td>
                <td>: Mandiri</td>
            </tr>
            <tr>
                <td style="padding:6px 0; font-weight:600;">No. Rekening</td>
                <td>: 1234567890</td>
            </tr>
            <tr>
                <td style="padding:6px 0; font-weight:600;">Atas Nama</td>
                <td>: Kedai Gaje Tarakan</td>
            </tr>
        </table>

        <p style="margin-top:15px; font-weight:600; color:#6b4caf;">
            Total yang harus dibayar: <?=rupiah($totalAkhir)?>
        </p>
    </div>
<?php endif; ?>


    <hr>

    <!-- Buttons -->
<div class="button-row">
    <a href="download_struk.php?id=<?=$order['id']?>" class="btn btn-download">
        <i class="fa-solid fa-file-arrow-down"></i> Unduh Struk (PDF)
    </a>

    <a href="https://wa.me/6282352456224?text=..." 
       target="_blank" class="btn btn-wa">
        <i class="fa-brands fa-whatsapp"></i> Kirim Bukti ke WhatsApp
    </a>
</div>

<div class="center-btn">
    <a href="tes1.html" class="btn btn-home">
        <i class="fa-solid fa-house"></i> Kembali ke Beranda
    </a>
</div>


<?php else: ?>
    <p>Data pesanan tidak ditemukan.</p>
<?php endif; ?>

</div>
</body>
</html>
