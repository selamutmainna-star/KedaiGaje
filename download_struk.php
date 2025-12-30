<?php
require(__DIR__ . '/fpdf/fpdf.php'); // pastikan folder fpdf ada
$dataFile = __DIR__ . '/data/pesanan.json';
$id = $_GET['id'] ?? '';

function rupiah($n): string{ return 'Rp '.number_format(num: $n,decimals: 0,decimal_separator: ',',thousands_separator: '.'); }

if (!$id || !file_exists(filename: $dataFile)) {
    die("Data tidak ditemukan.");
}

$all = json_decode(json: file_get_contents(filename: $dataFile), associative: true);
$order = null;
foreach ($all as $p) {
    if ($p['id'] == $id) {
        $order = $p;
        break;
    }
}

if (!$order) {
    die("Pesanan tidak ditemukan.");
}

// Buat PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont(family: 'Arial',style: 'B',size: 16);
$pdf->Cell(w: 0,h: 10,txt: 'Kedai Gaje Tarakan',border: 0,ln: 1,align: 'C');
$pdf->SetFont(family: 'Arial',style: '',size: 12);
$pdf->Cell(w: 0,h: 8,txt: 'Struk Pemesanan',border: 0,ln: 1,align: 'C');
$pdf->Ln(h: 5);

$pdf->SetFont(family: 'Arial',style: '',size: 11);
$pdf->Cell(w: 0,h: 6,txt: 'ID Pesanan: '.$order['id'],border: 0,ln: 1);
$pdf->Cell(w: 0,h: 6,txt: 'Nama: '.$order['nama'],border: 0,ln: 1);
$pdf->Cell(w: 0,h: 6,txt: 'Alamat: '.$order['alamat'],border: 0,ln: 1);
$pdf->Cell(w: 0,h: 6,txt: 'No. Telp: '.$order['telp'],border: 0,ln: 1);
$pdf->Cell(w: 0,h: 6,txt: 'Tanggal & Jam: '.$order['waktu'],border: 0,ln: 1);
$pdf->Ln(5);

$pdf->SetFont(family: 'Arial',style: 'B',size: 12);
$pdf->Cell(w: 100,h: 8,txt: 'Nama Item',border: 1);
$pdf->Cell(w: 30,h: 8,txt: 'Qty',border: 1);
$pdf->Cell(w: 50,h: 8,txt: 'Harga',border: 1);
$pdf->Ln();

$pdf->SetFont(family: 'Arial',style: '',size: 11);
foreach ($order['items'] as $item) {
    $pdf->Cell(w: 100,h: 8,txt: $item['name'],border: 1);
    $pdf->Cell(w: 30,h: 8,txt: $item['qty'],border: 1,ln: 0,align: 'C');
    $pdf->Cell(w: 50,h: 8,txt: rupiah(n: $item['price']),border: 1,ln: 0,align: 'R');
    $pdf->Ln();
}

$pdf->SetFont(family: 'Arial',style: 'B',size: 12);
$pdf->Cell(w: 130,h: 8,txt: 'Total',border: 1);
$pdf->Cell(w: 50,h: 8,txt: rupiah(n: $order['total']),border: 1,ln: 0,align: 'R');
$pdf->Ln(h: 10);

$pdf->SetFont(family: 'Arial',style: '',size: 11);
$pdf->Cell(w: 0,h: 6,txt: 'Metode Pembayaran: '.$order['pembayaran'],border: 0,ln: 1);

if ($order['pembayaran'] === 'Transfer') {
    $pdf->Ln(h: 5);
    $pdf->MultiCell(w: 0,h: 6,txt: "Harap transfer ke rekening:\nBCA 1234567890 a.n Kedai Gaje Tarakan\n\nSetelah transfer, kirim bukti ke WhatsApp: 0812-3456-7890",border: 0,align: 'L');
}

$pdf->Ln(h: 10);
$pdf->Cell(w: 0,h: 6,txt: 'Terima kasih telah memesan di Kedai Gaje Tarakan!',border: 0,ln: 1,align: 'C');

$pdf->Output(dest: 'D', name: 'Struk_Pesanan_'.$order['id'].'.pdf');
exit;
?>
