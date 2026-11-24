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

// Satu UPDATE...JOIN untuk SEMUA barang sekaligus, menggantikan pola lama yang menjalankan
// 2 query JOIN terpisah untuk SETIAP barang (hampir 10.000 query untuk ~5000 barang, dan
// trbmasuk_detail.kd_barang tidak ada index).
// kd_barang di tabel detail kadang kemasukan karakter nyasar (whitespace, titik di akhir, dll -
// data lama, entah dari mana sumbernya), jadi dicocokkan lewat CAST ke angka, bukan string
// persis - supaya baris seperti itu tetap ikut terhitung alih-alih diam-diam terlewat.
mysqli_begin_transaction($db);

$ok = mysqli_query($db, "UPDATE barang b
    LEFT JOIN (
        SELECT CAST(kd_barang AS UNSIGNED) AS kd_barang_num, SUM(qty_dtrbmasuk) AS totalbeli
        FROM trbmasuk_detail
        GROUP BY CAST(kd_barang AS UNSIGNED)
    ) m ON m.kd_barang_num = b.kd_barang
    LEFT JOIN (
        SELECT CAST(kd_barang AS UNSIGNED) AS kd_barang_num, SUM(qty_dtrkasir) AS totaljual
        FROM trkasir_detail
        GROUP BY CAST(kd_barang AS UNSIGNED)
    ) k ON k.kd_barang_num = b.kd_barang
    SET b.stok_barang = COALESCE(m.totalbeli, 0) - COALESCE(k.totaljual, 0)
    WHERE b.stok_barang <> (COALESCE(m.totalbeli, 0) - COALESCE(k.totaljual, 0))");

if ($ok) {
    mysqli_commit($db);
} else {
    mysqli_rollback($db);
}

header('location:../../media_admin.php?module=trkasir');

}
?>
