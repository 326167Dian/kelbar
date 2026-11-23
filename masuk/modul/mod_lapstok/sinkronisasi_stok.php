<?php
session_start();
 if (empty($_SESSION['username']) AND empty($_SESSION['passuser'])){
  echo "<link href='style.css' rel='stylesheet' type='text/css'>
 <center>Untuk mengakses modul, Anda harus login <br>";
  echo "<a href=../../index.php><b>LOGIN</b></a></center>";
}
else{

include "../../../configurasi/koneksi.php";
include "../../../configurasi/fungsi_thumb.php";
include "../../../configurasi/library.php";

$db = $GLOBALS["___mysqli_ston"];

// Hitung total beli & total jual untuk SEMUA barang sekaligus (2 query agregat),
// menggantikan pola lama yang menjalankan 2 query JOIN terpisah untuk SETIAP barang
// (hampir 10.000 query untuk ~5000 barang, dan trbmasuk_detail.kd_barang tidak ada index).
$totalBeli = array();
$beliResult = mysqli_query($db, "SELECT trbmasuk_detail.kd_barang, SUM(trbmasuk_detail.qty_dtrbmasuk) AS totalbeli
    FROM trbmasuk_detail
    JOIN trbmasuk ON trbmasuk_detail.kd_trbmasuk = trbmasuk.kd_trbmasuk
    GROUP BY trbmasuk_detail.kd_barang");
while ($row = mysqli_fetch_assoc($beliResult)) {
    $totalBeli[$row['kd_barang']] = $row['totalbeli'];
}

$totalJual = array();
$jualResult = mysqli_query($db, "SELECT trkasir_detail.kd_barang, SUM(trkasir_detail.qty_dtrkasir) AS totaljual
    FROM trkasir_detail
    JOIN trkasir ON trkasir_detail.kd_trkasir = trkasir.kd_trkasir
    GROUP BY trkasir_detail.kd_barang");
while ($row = mysqli_fetch_assoc($jualResult)) {
    $totalJual[$row['kd_barang']] = $row['totaljual'];
}

$tampil_barang = mysqli_query($db, "SELECT kd_barang, stok_barang FROM barang ORDER BY id_barang");

// Satu transaksi untuk semua UPDATE supaya tidak ada overhead commit per baris.
mysqli_begin_transaction($db);

while ($r = mysqli_fetch_assoc($tampil_barang)) {
    $kd_barang = $r['kd_barang'];
    $beli = isset($totalBeli[$kd_barang]) ? $totalBeli[$kd_barang] : 0;
    $jual = isset($totalJual[$kd_barang]) ? $totalJual[$kd_barang] : 0;
    $selisih = $beli - $jual;
    $stokbarang = $r['stok_barang'];

    if ($stokbarang != $selisih) {
        mysqli_query($db, "UPDATE barang SET stok_barang = '$selisih' WHERE kd_barang = '$kd_barang'");
    }
}

mysqli_commit($db);

header('location:../../media_admin.php?module=trkasir');

}
?>
