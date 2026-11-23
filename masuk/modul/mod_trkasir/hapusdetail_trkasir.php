<?php
session_start();
if (empty($_SESSION['username']) and empty($_SESSION['passuser'])) {
    http_response_code(403);
    echo 'Anda harus login.';
    exit;
}

include "../../../configurasi/koneksi.php";

$id_dtrkasir  = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['id_dtrkasir']);

//ambil data
$ambildata=mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_dtrkasir, id_barang, qty_dtrkasir FROM trkasir_detail
WHERE id_dtrkasir='$id_dtrkasir'");
$r=mysqli_fetch_array($ambildata);

$id_barang = $r['id_barang'];
$qty_dtrkasir = $r['qty_dtrkasir'];

mysqli_begin_transaction($GLOBALS["___mysqli_ston"]);
$gagal = false;

//update stok

$cekstok=mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_barang, stok_barang FROM barang
WHERE id_barang='$id_barang'");
$rst=mysqli_fetch_array($cekstok);

$stok_barang = $rst['stok_barang'];
$stokakhir = $stok_barang + $qty_dtrkasir;

$okstok = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE barang SET stok_barang = '$stokakhir' WHERE id_barang = '$id_barang'");

$okdetail = mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM trkasir_detail WHERE id_dtrkasir = '$id_dtrkasir'");

$okkomisi = mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM komisi_pegawai WHERE id_dtrkasir = '$id_dtrkasir'");

if (!$okstok || !$okdetail || !$okkomisi) {
    $gagal = true;
}

if ($gagal) {
    mysqli_rollback($GLOBALS["___mysqli_ston"]);
    echo $stok_barang;
} else {
    mysqli_commit($GLOBALS["___mysqli_ston"]);
    echo $stokakhir;
}
?>
