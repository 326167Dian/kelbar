<?php
session_start();
if (empty($_SESSION['username']) and empty($_SESSION['passuser'])) {
    http_response_code(403);
    echo 'Anda harus login.';
    exit;
}

include "../../../configurasi/koneksi.php";

$id_dtrbmasuk  = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['id_dtrbmasuk']);

//ambil data
$ambildata=mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_dtrbmasuk, id_barang, qty_dtrbmasuk FROM trbmasuk_detail
WHERE id_dtrbmasuk='$id_dtrbmasuk'");
$r=mysqli_fetch_array($ambildata);

$id_barang = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $r['id_barang']);
$qty_dtrbmasuk = $r['qty_dtrbmasuk'];

mysqli_begin_transaction($GLOBALS["___mysqli_ston"]);
$gagal = false;

//update stok
$cekstok=mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_barang, stok_barang FROM barang
WHERE id_barang='$id_barang'");
$rst=mysqli_fetch_array($cekstok);

$stok_barang = $rst['stok_barang'];
$stokakhir = $stok_barang - $qty_dtrbmasuk;

$okstok = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE barang SET stok_barang = '$stokakhir' WHERE id_barang = '$id_barang'");

$okdetail = mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM trbmasuk_detail WHERE id_dtrbmasuk = '$id_dtrbmasuk'");

if (!$okstok || !$okdetail) {
    $gagal = true;
}

if ($gagal) {
    mysqli_rollback($GLOBALS["___mysqli_ston"]);
} else {
    mysqli_commit($GLOBALS["___mysqli_ston"]);
}
?>
