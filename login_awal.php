<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Kedai Gaje</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
.food {
    position: fixed;
    bottom: -50px;
    font-size: 28px;
    opacity: 0.8;
    animation: naik 20s linear infinite;
    z-index: 0; /* supaya di belakang card */
    pointer-events: none;
}

@keyframes naik {
    0% {
        transform: translateY(0) scale(1);
        opacity: 0.9;
    }
    50% {
        opacity: 1;
        transform: translateY(-400px) scale(1.2);
    }
    100% {
        transform: translateY(-900px) scale(1.4);
        opacity: 0;
    }
}



   * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(-45deg,
      hsl(280, 90.10%, 60.20%),
      rgb(112, 172, 255),
      rgb(108, 214, 249),
      rgb(130, 237, 159)
  );
  background-size: 400% 400%;
  animation: gradientMove 12s ease infinite;

  min-height: 100vh;

  display: flex;
  flex-direction: column; /* penting */
  align-items: center;
  padding-top: 130px;       /* biar turun sedikit */
}
.grid {
  margin-bottom: 130px; /* jarak antara 4 fitur dan footer */
}
footer {
  text-align: center;
  color: #fff;
  font-size: 0.9rem;
  margin-bottom: 10px; /* biar tidak nempel terlalu bawah */
}



    @keyframes gradientMove {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

/* GRID UTAMA */
.grid {
  width: 85%;
  max-width: 1100px;
  display: grid;
  grid-template-columns: 1.1fr 1fr;
  grid-template-rows: auto auto;
  gap: 25px;
}

/* CARD */
.card {
  background: #fff;
  border-radius: 20px;
  padding: 28px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.08);
  transition: .2s;
  position: relative;
  z-index: 10;
}

.card:hover {
  transform: translateY(-3px);
  box-shadow: 0 20px 55px rgba(0,0,0,0.12);
}

/* LOGIN */
.login-card {
  grid-column: 1;
  grid-row: 1;
  text-align: center;
  padding: 40px;
}

.login-card img {
  width: 65px;
  margin-bottom: 18px;
}

.btn {
  display: block;
  width: 80%;
  margin: 10px auto;
  padding: 10px;
  text-align: center;
  border-radius: 8px;
  background: #6f8ff7;
  color: #fff;
  text-decoration: none;
}

/* FOTO */
.foto-card {
  grid-column: 2;
  grid-row: 1;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.foto-card img {
  width: 90%;
  height: 320px;
  object-fit: cover;
  border-radius: 20px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.10);
}

/* ABOUT CARD (INI YG BENER) */
.about-card {
  grid-column: 1;
  grid-row: 2;

  display: flex;
  padding: 0;
  overflow: hidden;

  border-radius: 20px;
  background: #fff;
  box-shadow: 0 10px 40px rgba(0,0,0,0.08);
}

/* BACKGROUND FOTO */
.about-bg {
  width: 45%;
  background: url('f2.png') center/cover no-repeat;
}

/* TEKS */
.about-text {
  width: 55%;
  padding: 30px;
}

.about-text h3 {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 10px;
}

.about-text p {
  font-size: .9rem;
  color: #444;
  line-height: 1.6;
}

/* MAP */
.map-card {
  grid-column: 2;
  grid-row: 2;
}

.map-card iframe {
  width: 100%;
  height: 250px;
  border-radius: 20px;
  border: none;
}

/* RESPONSIVE */
@media (max-width: 900px) {
  .grid {
    grid-template-columns: 1fr;
  }

  .foto-card img {
    height: 230px;
  }

  .about-card {
    flex-direction: column;
  }

  .about-bg {
    width: 100%;
    height: 150px;
  }

  .about-text {
    width: 100%;
  }
}

  </style>
</head>

<body>
  <?php
// Food emoji list
$food_list = [
"🍔","🍟","🍕","🌭","🍗","🍖","🥪","🍝","🍜","🍛",
"🍱","🍣","🍤","🥟","🍘","🍥","🥠","🍙","🍚","🍘",
"🍧","🍨","🍦","🧁","🍰","🎂","🍩","🍪","🍫","🍿",
"🧃","🥛","☕","🍵","🥤","🍹","🍸","🍺","🍻","🧋",
"🥨","🧀","🥐","🥯","🥞","🧇","🍞","🥖","🥗","🥘",
"🍲","🍔","🍟","🍕","🍤","🌮","🌯","🍗","🍛","🍣"
];

foreach ($food_list as $emoji) {
    $left = rand(2, 95);
    $duration = rand(14, 34);
    $delay = rand(0, 15);

    echo "<div class='food' style='left: {$left}%; animation-duration: {$duration}s; animation-delay: {$delay}s;'>{$emoji}</div>";
}
?>


  <div class="grid">

    <!-- LOGIN (kiri atas) -->
    <div class="card login-card">
      <img src="logoikon.png" alt="icon">
      <h2>Selamat Datang</h2>
      <p>Silahkan Pilih Login Sebagai :</p>

      <a href="proses_pesan.php" class="btn">Pembeli</a>
      <a href="login.php" class="btn">Admin</a>

      <div class="line"></div>
    </div>

    <!-- FOTO (kanan atas) -->
    <div class="card foto-card">
      <img src="kedai.jpg" alt="Foto Kedai">
    </div>

    <!-- DESKRIPSI (kiri bawah) -->
<!-- DESKRIPSI (kiri bawah) -->
<!-- DESKRIPSI (kiri bawah) -->
<div class="card about-card">
    <div class="about-bg"></div>

    <div class="about-text">
        <h3>Tentang Kedai Gaje</h3>
        <p>
            Kedai Gaje adalah kedai yang menyajikan berbagai menu makanan 
            dan minuman lezat dengan harga terjangkau, menawarkan suasana 
            nyaman serta cocok untuk makan santai atau sekedar menikmati 
            minuman favorit.
        </p>
    </div>
</div>



    <!-- MAP (kanan bawah) -->
    <div class="card map-card">
      <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3975.766046701465!2d117.599110!3d3.365508!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x320df2f2f86e3d63%3A0x8f3f4f2e4b228835!2sJuata%20Kerikil%2C%20Tarakan!5e0!3m2!1sid!2sid!4v1700000000000"></iframe>

      <div class="info">
        <p><i class="fa-solid fa-location-dot"></i> Jl. Aki Balak RT.03, Juata Kerikil</p>
        <p><i class="fa-brands fa-instagram"></i> @kedaigaje</p>
        <p><i class="fa-regular fa-clock"></i> 10.00 – 22.00 WITA</p>
      </div>
    </div>

  </div>

  <footer>© KEDAI GAJE 2025</footer>

</body>
</html>
