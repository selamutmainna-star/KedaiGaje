<?php
session_start();

// Username dan password admin
$ADMIN_USERNAME = 'admin';
$ADMIN_PASSWORD = 'selll123';

// Proses login ketika form dikirim
$login_error = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['username'] === $ADMIN_USERNAME && $_POST['password'] === $ADMIN_PASSWORD) {
        $_SESSION['admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $login_error = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Admin - Kedai Gaje</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
    *{
        padding:0; margin:0; box-sizing:border-box;
    }

    body {
        overflow: hidden;
        font-family: "Poppins", sans-serif;
        background: linear-gradient(120deg, #7c4dff, #00e5ff, #00c853);
        background-size: 300% 300%;
        animation: bgShift 15s ease infinite;
        height: 100vh;
        position: relative;
    }

    @keyframes bgShift {
        0% {background-position: 0% 50%;}
        50% {background-position: 100% 50%;}
        100% {background-position: 0% 50%;}
    }

    /* --- Food Emoji Floating --- */
 .food {
    position: absolute;
    top: -20%; /* lebih tinggi agar tidak terlihat muncul dulu */
    font-size: 45px;
    opacity: 0; /* awalnya transparan */
    animation: floatDown linear infinite;
    filter: drop-shadow(0 0 8px rgba(255,255,255,0.4));
}

@keyframes floatDown {
    0% { 
        transform: translateY(0); 
        opacity: 0; /* tidak terlihat saat spawn */
    }
    10% {
        opacity: 0.9; /* mulai terlihat saat turun */
    }
    100% { 
        transform: translateY(130vh); 
        opacity: 0; /* hilang saat keluar layar */
    }
}


    /* --- Particle Neon --- */
    .particle {
        position: absolute;
        width: 6px;
        height: 6px;
        background: white;
        border-radius: 50%;
        opacity: 0.8;
        animation: rise 8s linear infinite;
        filter: blur(2px);
    }

    @keyframes rise {
        0% { transform: translateY(0) scale(1); }
        100% { transform: translateY(-150vh) scale(0.2); }
    }

    /* --- Login Box --- */
.login-box {
    position: absolute;
    z-index: 999;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    
    width: 340px;
    padding: 30px;

    /* Lebih solid, gak transparan banget */
    background: rgba(255, 255, 255, 0.55);
    border: 1px solid rgba(255,255,255,0.6);
    backdrop-filter: blur(6px);

    border-radius: 18px;
    text-align: center;
    color: #222;
    box-shadow: 0 0 25px rgba(255,255,255,0.45);
    animation: fadeIn 1s ease-out;
}


    @keyframes fadeIn {
        from {opacity: 0; transform: translate(-50%, -40%);}
        to   {opacity: 1; transform: translate(-50%, -50%);}
    }

    .login-box h2 {
        margin-bottom: 10px;
        font-weight: 600;
        font-size: 26px;
        text-shadow: 0 0 10px rgba(255,255,255,0.6);
    }

    .login-box p {
        margin-bottom: 20px;
        opacity: 0.85;
    }

    input {
        width: 90%;
        padding: 12px;
        margin: 8px 0;
        border-radius: 8px;
        border: none;
        outline: none;
        font-size: 15px;
    }

    .password-row {
        position: relative;
    }

    .toggle-pass {
        position: absolute;
        right: 25px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        font-size: 18px;
        color: #333;
    }

    button {
        width: 95%;
        padding: 12px;
        background:rgb(59, 137, 255);
        border: none;
        border-radius: 10px;
        color: black;
        font-size: 16px;
        cursor: pointer;
        margin-top: 10px;
        font-weight: 600;
        transition: 0.25s;
    }

    button:hover {
        background: #00bcd4;
        transform: scale(1.03);
    }

    .error {
        margin-top: 10px;
        background: rgba(255, 70, 70, 0.7);
        padding: 8px;
        border-radius: 6px;
        font-size: 13px;
    }

    /* Buttons Under */
    .btn-row{
        margin-top:15px;
        display:flex;
        justify-content:space-between;
    }

.mini-btn{
    padding:8px 12px;
    background:rgba(0, 0, 0, 0.55); /* LEBIH GELAP & KONTRAS */
    border-radius:8px;
    text-decoration:none;
    color:white !important; /* BIAR TEKS JELAS */
    font-size:13px;
    backdrop-filter:blur(3px);
    transition:0.25s;
}
.mini-btn:hover{
    background:rgba(0, 0, 0, 0.75);
    transform:scale(1.05);
}


    .mini-btn:hover{
        background:rgba(255,255,255,0.45);
        transform:scale(1.05);
    }

    /* Light/Dark toggle */
    #toggleTheme {
        position:absolute;
        top:20px;
        right:20px;
        padding:8px 12px;
        border-radius:10px;
        background:white;
        color:black;
        cursor:pointer;
        font-size:13px;
        z-index:1000;
    }

</style>
</head>
<body>

<!-- Toggle Theme -->
<div id="toggleTheme">🌙 Dark Mode</div>

<?php
$food_list = [
"🍔","🍟","🍕","🌭","🍗","🍖","🥪","🍝","🍜","🍛",
"🍱","🍣","🍤","🥟","🍘","🍥","🥠","🍙","🍚","🍘",
"🍧","🍨","🍦","🧁","🍰","🎂","🍩","🍪","🍫","🍿",
"🥤","🍹","🍸","🍺","🧋"
];

foreach ($food_list as $emoji) {
    $left = rand(1, 97);
    $duration = rand(12, 30);
    $delay = rand(0, 18);

    echo "<div class='food' style='left: {$left}%; animation-duration: {$duration}s; animation-delay: {$delay}s;'>{$emoji}</div>";
}

// 20 particle neon
for ($i=0; $i<20; $i++) {
    $left = rand(0,100);
    $size = rand(4,8);
    $duration = rand(6,12);
    $delay = rand(0,8);

    echo "<div class='particle' style='left:{$left}%; width:{$size}px; height:{$size}px; animation-duration:{$duration}s; animation-delay:{$delay}s;'></div>";
}
?>

<div class="login-box">
    <h2>Login Admin</h2>
    <p>Selamat datang di panel admin 🛠️</p>

    <form method="POST">
        <input type="text" name="username" placeholder="Username Admin" required>

        <div class="password-row">
            <input type="password" name="password" id="pass" placeholder="Password Admin" required>
            <span class="toggle-pass" onclick="togglePass()">👁️</span>
        </div>

        <button type="submit">Masuk</button>

        <?php if ($login_error): ?>
            <div class="error">Username atau password salah.</div>
        <?php endif; ?>
    </form>

    <div class="btn-row">
        <a href="tes1.html" class="mini-btn">⬅ Home</a>
    </div>
</div>

<script>
function togglePass(){
    const p = document.getElementById("pass");
    p.type = p.type === "password" ? "text" : "password";
}

const toggleBtn = document.getElementById("toggleTheme");
let dark=false;

toggleBtn.onclick = ()=>{
    dark = !dark;

    if(dark){
        document.body.style.filter="brightness(0.65)";
        toggleBtn.textContent="☀️ Light Mode";
    } else {
        document.body.style.filter="brightness(1)";
        toggleBtn.textContent="🌙 Dark Mode";
    }
};
</script>

</body>
</html>
