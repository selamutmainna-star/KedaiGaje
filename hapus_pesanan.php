<?php
session_start();
if(!isset($_SESSION['admin'])){ header('Location: login.php'); exit; }
$id = isset($_GET['id']) ? $_GET['id'] : null;
$file = __DIR__ . '/data/pesanan.json';
if($id && file_exists($file)){
    $arr = json_decode(file_get_contents($file), true) ?? [];
    $new = [];
    foreach($arr as $a){
        if(($a['id'] ?? '') == $id) continue;
        $new[] = $a;
    }
    file_put_contents($file, json_encode($new, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}
header('Location: admin.php'); exit;
?>
