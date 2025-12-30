<!doctype html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kedai Gaje Tarakan</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* ====================== GLOBAL STYLE ======================= */

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

/* Fade-in animation */
.fade {
    animation: fadeIn 0.8s ease forwards;
    opacity: 0;
}

@keyframes fadeIn {
    to { opacity: 1; transform: translateY(0); }
    from { opacity: 0; transform: translateY(20px); }
}

/* ====================== HEADER ======================= */

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

.brand {
    display: flex;
    gap: 15px;
    align-items: center;
}

.logo {
    width: 95px;
    height: 95px;
    border-radius: 25px;
    object-fit: cover;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: .3s;
}

.logo:hover {
    transform: scale(1.05);
}

.contact a {
    margin-left: 15px;
    color: #1b7a90;
    font-weight: 600;
    text-decoration: none;
    padding: 8px 14px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    transition: .3s;
}

.contact a:hover {
    background: #1b7a90;
    color: white;
}

/* ====================== MENU GRID ======================= */

.container {
    display: flex;
    justify-content: center;
    gap: 35px;
    padding: 40px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 30px;
}

/* Card design */
.card {
    background: rgba(255,255,255,0.55);
    backdrop-filter: blur(10px);
    padding: 15px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 4px 18px rgba(0,0,0,0.1);
    transition: .3s;
    animation: fadeIn .6s ease;
}

.card:hover {
    transform: translateY(-5px);
}

/* Image */
.card img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 15px;
    transition: .3s;
}

.card:hover img {
    transform: scale(1.06);
}

/* Button Add */
.btn {
    padding: 8px 18px;
    border: none;
    background: #8cc4ea;
    color: white;
    border-radius: 10px;
    cursor: pointer;
    transition: .25s;
    font-size: 14px;
}

.btn:hover {
    transform: scale(1.07);
    filter: brightness(1.1);
}

.btn.primary {
    background:rgb(42, 136, 251);
}

.btn.primary:hover {
    background: #135a66;
}

/* Back Button Floating */
.btn-back {
    display: inline-block;
    margin-bottom: 20px;
    background: #1b7a90;
    padding: 10px 18px;
    border-radius: 12px;
    color: white;
    text-decoration: none;
    font-weight: bold;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    transition: .3s;
}

.btn-back:hover {
    transform: translateX(-5px);
}

/* ====================== ORDER PANEL ======================= */

.order-panel {
    width: 320px;
    background: rgba(255,255,255,0.55);
    backdrop-filter: blur(15px);
    padding: 25px;
    border-radius: 20px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.15);
    position: sticky;
    top: 20px;
    animation: fadeIn 1s ease;
}

.order-panel input,
.order-panel textarea,
.order-panel select {
    width: 100%;
    margin-bottom: 12px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 12px;
    font-size: 14px;
    background: rgba(255,255,255,0.7);
}

/* Total */
.total {
    font-size: 20px;
    font-weight: bold;
    margin-top: 15px;
    padding: 10px;
    background:rgb(183, 218, 251);
    border-radius: 10px;
    text-align: center;
    animation: fadeIn .8s ease;
}

    /* ================= FOOTER ================= */
    footer{ margin-top:60px; padding:22px; text-align:center; color:#021428; font-weight:600; border-radius:14px; }

</style>
</head>

<body>

<header class="header fade">
    <div class="brand">
        <img src="brand.png" class="logo">
        <div>
            <h1>Kedai Gaje Tarakan</h1>
            <p>📍Jl. Aki Balak RT.03, Juata Kerikil</p>
            <p>⏰ Buka 10:00–22:00</p>
        </div>
    </div>
    <div class="contact">
        <a href="https://wa.me/6282352456224" target="_blank"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
        <a href="https://instagram.com/kedaigaje" target="_blank"><i class="fa-brands fa-instagram"></i> Instagram</a>
    </div>
</header>

<main class="container fade">

<section class="menu-list">
    <a href="tes1.html" class="btn-back">↩ Kembali</a>
    <h2>Menu Kami</h2>

    <div class="grid">

        <?php
        $menu = json_decode(file_get_contents("menu.json"), true);
        function rupiah($n){ return "Rp " . number_format($n,0,',','.'); }

        foreach($menu as $m): ?>
        <div class="card">
            <img src="<?=$m['img']?>" alt="<?=$m['name']?>">
            <h3><?=$m['name']?></h3>
            <p class="price"><?=rupiah($m['price'])?></p>

            <?php if($m['status']=="Habis"): ?>
                <button class="btn" style="background:#aaa" disabled>Menu Habis</button>
            <?php else: ?>
                <button class="btn add"
                    data-id="<?=$m['id']?>"
                    data-name="<?=$m['name']?>"
                    data-price="<?=$m['price']?>">
                    Tambah
                </button>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

    </div>
</section>

<aside class="order-panel">

<h2>🧾 Pesanan</h2>
<p>────────────────────────</p>
<form id="orderForm" action="proses_pesan.php" method="post">

    <label>Nama</label>
    <input type="text" name="nama" required>

    <label>Tipe Layanan</label>
    <select id="tipe_layanan" name="tipe_layanan" required>
        <option value="">-- Pilih --</option>
        <option value="Makan di Tempat">Makan di Tempat</option>
        <option value="Take Away">Take Away</option>
        <option value="Di Antar">Di Antar</option>
    </select>

    <div id="wilayahBox" style="display:none">
        <label>Wilayah Pengantaran</label>
        <select id="wilayah" name="wilayah">
            <option value="">-- Pilih Wilayah --</option>
            <option value="Tarakan Barat">Tarakan Barat (Rp.5000)</option>
            <option value="Tarakan Timur">Tarakan Timur (Rp.10000)</option>
            <option value="Tarakan Utara">Tarakan Utara (Rp.5000)</option>
            <option value="Tarakan Tengah">Tarakan Tengah (Rp.8000)</option>
        </select>
    </div>

    <input type="hidden" name="items" id="itemsField">
    <input type="hidden" name="total" id="totalField">
    <input type="hidden" name="ongkir" id="ongkirField">

    <div id="alamatBox" style="display:none;">
        <label>Rincian Alamat Lengkap</label>
        <textarea name="alamat" id="alamat"></textarea>
    </div>

    <div id="telpBox" style="display:none;">
        <label>No. Telp</label>
        <input type="tel" name="telp" id="telp">
    </div>

        <div id="cart"></div>

    <label>Catatan</label>
    <textarea name="catatan"></textarea>


    <label>Pembayaran</label>
    <select name="pembayaran" required>
        <option value="">-- Pilih --</option>
        <option value="Cash">Cash</option>
        <option value="Transfer">Transfer</option>
    </select>

    <div class="total">Total: <span id="totalText">Rp 0</span></div>
    <p>────────────────────────</p>
    <button type="submit" class="btn primary">Kirim Pesanan</button>

</form>
</aside>

</main>

<script src="script.js" defer></script>

<footer>© 2025 KEDAI GAJE</footer>
</body>
</html> 