<?php
session_start();

// File penyimpanan menu
$data_file = "menu.json";

if (!file_exists($data_file)) {
    file_put_contents($data_file, json_encode([], JSON_PRETTY_PRINT));
}

$menu = json_decode(file_get_contents($data_file), true);

// ===== Tambah Menu =====
if (isset($_POST['add'])) {
    $new = [
        'id'     => time(),
        'type'   => $_POST['type'],
        'name'   => $_POST['name'],
        'price'  => intval($_POST['price']),
        'img'    => $_POST['img'],
        'status' => "Tersedia"
    ];
    $menu[] = $new;
    file_put_contents($data_file, json_encode($menu, JSON_PRETTY_PRINT));
    header("Location: admin_menu.php");
    exit;
}

// ===== Hapus Menu =====
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $menu = array_filter($menu, fn($m) => $m['id'] != $id);
    file_put_contents($data_file, json_encode($menu, JSON_PRETTY_PRINT));
    header("Location: admin_menu.php");
    exit;
}

// ===== Update Menu =====
if (isset($_POST['update'])) {
    foreach ($menu as &$m) {
        if ($m['id'] == $_POST['id']) {
            $m['name']   = $_POST['name'];
            $m['price']  = $_POST['price'];
            $m['type']   = $_POST['type'];
            $m['status'] = $_POST['status'];
            $m['img']    = $_POST['img']; // menyimpan gambar baru
        }
    }
    file_put_contents($data_file, json_encode($menu, JSON_PRETTY_PRINT));
    header("Location: admin_menu.php");
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Kelola Menu • Kelola Menu Kedai Gaje</title>

<style>
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



    /* HEADER */
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

    /* TOP BUTTONS */
    .top-buttons {
        display: flex;
        gap: 15px;
    }

    .btn-topmenu {
        background: rgb(80, 173, 255);
        color: #4a2c16;
        padding: 12px 20px;
        text-decoration: none;
        font-weight: 600;
        border-radius: 12px;
        border: 2px solid rgba(166, 228, 241, 0.8);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .btn-topmenu:hover {
        background: rgba(133, 218, 255, 0.9);
        border-color: #c99b70;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .btn-topmenu.logout {
        background: rgba(247, 246, 246, 0.9);
        border-color: rgba(90, 41, 41, 0.8);
        color: #7a1e1e;
    }

    .btn-topmenu.logout:hover {
        background: rgba(247, 156, 156, 0.9);
        border-color: #cc6f6f;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .container {
        padding: 30px;
        max-width: 1200px;
        margin: 0 auto;
        animation: fadeIn 1s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* CARD TAMBAH MENU */
    .card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 25px;
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        margin-bottom: 30px;
        animation: slideUp 0.8s ease-out;
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    h2, h3 {
        margin-top: 0;
        color: #4a2c16;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-group {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: center;
        margin-bottom: 15px;
    }

    .form-group input, .form-group select {
        padding: 12px 15px;
        border: 2px solid #ddd;
        border-radius: 10px;
        font-family: inherit;
        font-size: 16px;
        transition: all 0.3s ease;
        flex: 1;
        min-width: 200px;
        position: relative;
    }

    .form-group input:focus, .form-group select:focus {
        border-color: #3c60ff;
        box-shadow: 0 0 8px rgba(60, 96, 255, 0.3);
        outline: none;
    }

    .form-group input::placeholder {
        color: #aaa;
    }

    .form-group .icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #aaa;
        font-size: 18px;
    }

    .form-group input {
        padding-left: 40px;
    }

    button {
        padding: 12px 20px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        font-family: inherit;
        font-size: 16px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .btn-add {
        background: linear-gradient(135deg, #7cb342, #4caf50);
        color: white;
    }

    .btn-add:hover {
        background: linear-gradient(135deg, #4caf50, #388e3c);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    /* TABLE */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        animation: fadeIn 1s ease-out 0.2s both;
    }

    th {
        background: rgb(80, 173, 255);
        padding: 15px;
        font-size: 16px;
        color:rgb(255, 255, 255);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    td {
        padding: 12px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    tr:hover {
        background: rgba(206, 242, 251, 0.8);
        transform: scale(1.01);
    }

    .btn-update {
        background: linear-gradient(135deg, #029663, #4caf50);
        color: white;
        margin-right: 8px;
    }

    .btn-update:hover {
        background: linear-gradient(135deg, #4caf50, #388e3c);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .btn-delete {
        background: linear-gradient(135deg, #e53935, #d32f2f);
        color: white;
        padding: 10px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .btn-delete:hover {
        background: linear-gradient(135deg, #d32f2f, #b71c1c);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    /* Rata kolom agar judul sejajar dengan isi */
    th, td {
        text-align: center;
    }

    td input, td select {
        width: 90%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-family: inherit;
        transition: all 0.3s ease;
    }

    td input:focus, td select:focus {
        border-color: #3c60ff;
        box-shadow: 0 0 6px rgba(60, 96, 255, 0.3);
        outline: none;
    }

    /* Gambar styling */
    td img {
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    td img:hover {
        transform: scale();
    }
.header-logo {
    width: 60px;   /* besarkan di sini */
    height: 60px;  /* samain biar proporsional */
    object-fit: contain;
}


</style>


</head>
<body>

<!-- HEADER -->
<header class="header">
  <h2 class="header-title">
      <img src="brand.png" class="header-logo">
      Admin Kedai Gaje
  </h2>

  <div class="top-buttons">
      <a href="admin.php" class="btn-topmenu">
          <i class="fa-solid fa-list"></i> Kelola Data
      </a>
      <a href="logout.php" class="btn-topmenu logout">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
      </a>
  </div>
</header>


<div class="container">

<!-- FORM TAMBAH MENU -->
<div class="card">
    <h2><i class="fa-solid fa-plus-circle"></i> Tambah Menu Baru</h2>
    <form method="post">
        <div class="form-group">
            <div style="position: relative; flex: 1;">
                <i class="fa-solid fa-utensils icon"></i>
                <input type="text" name="name" placeholder="Nama menu" required>
            </div>
            <div style="position: relative; flex: 1;">
                <i class="fa-solid fa-dollar-sign icon"></i>
                <input type="number" name="price" placeholder="Harga" required>
            </div>
            <select name="type" style="flex: 1;">
                <option value="Makanan"><i class="fa-solid fa-utensils"></i> Makanan</option>
                <option value="Minuman"><i class="fa-solid fa-glass-water"></i> Minuman</option>
            </select>
            <div style="position: relative; flex: 1;">
                <i class="fa-solid fa-image icon"></i>
                <input type="text" name="img" placeholder="nama_gambar.png">
            </div>
            <button name="add" class="btn-add"><i class="fa-solid fa-plus"></i> Tambah</button>
        </div>
    </form>
</div>

<!-- TABLE MENU -->
<h2><i class="fa-solid fa-list-ul"></i> Daftar Menu</h2>

<table>
<tr>
    <th><i class="fa-solid fa-utensils"></i> Nama</th>
    <th><i class="fa-solid fa-dollar-sign"></i> Harga</th>
    <th><i class="fa-solid fa-tags"></i> Jenis</th>
    <th><i class="fa-solid fa-circle-check"></i> Status</th>
    <th><i class="fa-solid fa-image"></i> Gambar</th>
    <th><i class="fa-solid fa-cogs"></i> Aksi</th>
</tr>

<?php foreach ($menu as $m): ?>
<tr>
    <form method="post">

        <td>
            <input type="text" name="name" value="<?=$m['name']?>" required>
        </td>

        <td>
            <input type="number" name="price" value="<?=$m['price']?>" required>
        </td>

        <td>
            <select name="type">
                <option value="Makanan" <?=$m['type']=="Makanan"?"selected":""?>><i class="fa-solid fa-utensils"></i> Makanan</option>
                <option value="Minuman" <?=$m['type']=="Minuman"?"selected":""?>><i class="fa-solid fa-glass-water"></i> Minuman</option>
            </select>
        </td>

        <td>
            <select name="status">
                <option value="Tersedia" <?=$m['status']=="Tersedia"?"selected":""?>><i class="fa-solid fa-circle" style="color: green;"></i> 🟢 Tersedia</option>
                <option value="Habis" <?=$m['status']=="Habis"?"selected":""?>><i class="fa-solid fa-circle" style="color: red;"></i> 🔴 Habis</option>
            </select>
        </td>

        <!-- KOLOM GAMBAR -->
        <td style="text-align:center;">
            <img src="img/<?= $m['img'] ?>" width="60" style="border-radius:6px"><br>
            <input type="text" name="img" value="<?= $m['img'] ?>" style="margin-top:5px;">
        </td>

        <td>
            <input type="hidden" name="id" value="<?=$m['id']?>">
            <button name="update" class="btn-update"><i class="fa-solid fa-edit"></i> Update</button>
            <a href="?delete=<?=$m['id']?>" class="btn-delete"><i class="fa-solid fa-trash"></i> Hapus</a>
        </td>

    </form>
</tr>
<?php endforeach; ?>
</table>


</div>

</body>
</html>
