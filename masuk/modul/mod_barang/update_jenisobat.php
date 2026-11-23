<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['username']) and empty($_SESSION['passuser'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login.']);
    exit;
}

include "../../../configurasi/koneksi.php";

$id_barang = isset($_POST['id_barang']) ? (int) $_POST['id_barang'] : 0;
$jenisobat = isset($_POST['jenisobat']) ? $_POST['jenisobat'] : '';

if ($id_barang <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID barang tidak valid.']);
    exit;
}

$jenisobatEsc = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $jenisobat);

$cek = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT jenisobat FROM jenis_obat WHERE jenisobat = '$jenisobatEsc'");
if (mysqli_num_rows($cek) == 0) {
    echo json_encode(['success' => false, 'message' => 'Jenis Obat / Rak tidak terdaftar.']);
    exit;
}

$ok = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE barang SET jenisobat = '$jenisobatEsc' WHERE id_barang = '$id_barang'");

if ($ok) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($GLOBALS["___mysqli_ston"])]);
}
