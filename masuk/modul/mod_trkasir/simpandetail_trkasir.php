<?php
session_start();
if (empty($_SESSION['username']) and empty($_SESSION['passuser'])) {
    http_response_code(403);
    echo 'Anda harus login.';
    exit;
}

include "../../../configurasi/koneksi.php";

$kd_trkasir = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['kd_trkasir']);
$id_dtrkasir = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['id_dtrkasir']);
$id_barang = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['id_barang']);
$kd_barang = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['kd_barang']);
$nmbrg_dtrkasir = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['nmbrg_dtrkasir']);
$qty_dtrkasir = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['qty_dtrkasir']);
$sat_dtrkasir = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['sat_dtrkasir']);
$hrgjual_dtrkasir = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['hrgjual_dtrkasir']);
$indikasi = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['indikasi']);
$jenisobat = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['jenisobat']);
$komisi = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['komisi_dtrkasir']);
$currentdate = date('Y-m-d',time());
$id_admin = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['id_admin']);

if($qty_dtrkasir == ""){
$qty_dtrkasir = "1";
}else{

}

mysqli_begin_transaction($GLOBALS["___mysqli_ston"]);
$gagal = false;

if($id_dtrkasir == "" || $id_dtrkasir == null){

//cek apakah barang sudah ada
$cekdetail=mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_barang, kd_barang, kd_trkasir, id_dtrkasir, qty_dtrkasir
FROM trkasir_detail
WHERE kd_barang='$kd_barang' AND kd_trkasir='$kd_trkasir'");

$ketemucekdetail=mysqli_num_rows($cekdetail);
$rcek=mysqli_fetch_array($cekdetail);
if ($ketemucekdetail > 0){

$id_dtrkasir = $rcek['id_dtrkasir'];
$qtylama = $rcek['qty_dtrkasir'];
$ttlqty = $qtylama + $qty_dtrkasir;
$ttlharga = $ttlqty * $hrgjual_dtrkasir;

$okdetail = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE trkasir_detail SET qty_dtrkasir = '$ttlqty',
										hrgjual_dtrkasir = '$hrgjual_dtrkasir',
										hrgttl_dtrkasir = '$ttlharga'
										WHERE id_dtrkasir = '$id_dtrkasir' and kd_barang='$kd_barang'");
if (!$okdetail) {
    $gagal = true;
}

//update stok
$cekstok = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM barang
WHERE id_barang='$id_barang'");
$rst = mysqli_fetch_array($cekstok);

$stok_barang = $rst['stok_barang'];
$stokakhir = (($stok_barang + $qtylama) - $ttlqty);

$okstok = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE barang SET
    stok_barang = '$stokakhir',
    jenisobat = '$jenisobat',
    hrgjual_barang = '$hrgjual_dtrkasir' WHERE id_barang = '$id_barang'");
if (!$okstok) {
    $gagal = true;
}

if($_SESSION['komisi']=='Y'){
    if($_SESSION['penjualansebelum']=='Y'){
        $ttlkomisi = $ttlqty * $komisi;
        $okkomisi = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE komisi_pegawai SET ttl_komisi = '$ttlkomisi'
        WHERE id_dtrkasir='$id_dtrkasir'");
    } else {
        $ttlkomisi = $ttlqty * $komisi;
        $okkomisi = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE komisi_pegawai SET ttl_komisi = '$ttlkomisi'
        WHERE id_dtrkasir='$id_dtrkasir' AND id_admin='$_SESSION[idadmin]'");
    }
    if (!$okkomisi) {
        $gagal = true;
    }
}
}else{

$ttlharga = $qty_dtrkasir * $hrgjual_dtrkasir;

$okinsert = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO trkasir_detail(kd_trkasir,
										id_barang,
										kd_barang,
										nmbrg_dtrkasir,
										qty_dtrkasir,
										sat_dtrkasir,
										hrgjual_dtrkasir,
										hrgttl_dtrkasir)
								  VALUES('$kd_trkasir',
										'$id_barang',
										'$kd_barang',
										'$nmbrg_dtrkasir',
										'$qty_dtrkasir',
										'$sat_dtrkasir',
										'$hrgjual_dtrkasir',
										'$ttlharga')");

if (!$okinsert) {
    $gagal = true;
}

$insertid_dtrkasir = mysqli_insert_id($GLOBALS["___mysqli_ston"]);

if (!$gagal) {
    //update stok
    $cekstok = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM barang
    WHERE id_barang='$id_barang'");
    $rst = mysqli_fetch_array($cekstok);

    $stok_barang = $rst['stok_barang'];
    $stokakhir = $stok_barang - $qty_dtrkasir;

    $okstok = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE barang SET
        stok_barang = '$stokakhir',
        jenisobat = '$jenisobat',
        hrgjual_barang = '$hrgjual_dtrkasir' WHERE id_barang = '$id_barang'");
    if (!$okstok) {
        $gagal = true;
    }

    if($_SESSION['komisi']=='Y'){
        if($_SESSION['penjualansebelum']=='Y'){
            $ttlkomisi = $qty_dtrkasir * $komisi;
            $okkomisi = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO komisi_pegawai (kd_trkasir, id_dtrkasir, id_admin, ttl_komisi, tgl_komisi, status_komisi)
            VALUES('$kd_trkasir', '$insertid_dtrkasir', '$id_admin', '$ttlkomisi', '$currentdate', 'on')");
        } else {
            $ttlkomisi = $qty_dtrkasir * $komisi;
            $okkomisi = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO komisi_pegawai (kd_trkasir, id_dtrkasir, id_admin, ttl_komisi, tgl_komisi, status_komisi)
            VALUES('$kd_trkasir', '$insertid_dtrkasir', '$_SESSION[idadmin]', '$ttlkomisi', '$currentdate', 'on')");
        }
        if (!$okkomisi) {
            $gagal = true;
        }
    }
}
}

}else{
//
$cekdetail=mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM trkasir_detail
WHERE id_dtrkasir='$id_dtrkasir'");
$rcek=mysqli_fetch_array($cekdetail);
$id_dtrkasir = $rcek['id_dtrkasir'];
$qtylama = $rcek['qty_dtrkasir'];
$qtybaru = $qtylama + $qty_dtrkasir;
$ttlharga = $qtybaru * $hrgjual_dtrkasir;

$okdetail = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE trkasir_detail SET qty_dtrkasir = '$qtybaru',
										hrgjual_dtrkasir = '$hrgjual_dtrkasir',
										hrgttl_dtrkasir = '$ttlharga'
										WHERE id_dtrkasir = '$id_dtrkasir'");
if (!$okdetail) {
    $gagal = true;
}

//update stok
$cekstok = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM barang
WHERE id_barang='$id_barang'");
$rst = mysqli_fetch_array($cekstok);

$stok_barang = $rst['stok_barang'];
$stokakhir = (($stok_barang + $qtylama) - $qty_dtrkasir);

$okstok = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE barang SET
    stok_barang = '$stokakhir',
    jenisobat = '$jenisobat',
    hrgjual_barang = '$hrgjual_dtrkasir' WHERE id_barang = '$id_barang'");
if (!$okstok) {
    $gagal = true;
}

if($_SESSION['komisi']=='Y'){
    if($_SESSION['penjualansebelum']=='Y'){
        $ttlkomisi = $qtybaru * $komisi;
        $okkomisi = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE komisi_pegawai SET ttl_komisi = '$ttlkomisi'
        WHERE id_dtrkasir='$id_dtrkasir'");

    } else {
        $ttlkomisi = $qtybaru * $komisi;
        $okkomisi = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE komisi_pegawai SET ttl_komisi = '$ttlkomisi'
        WHERE id_dtrkasir='$id_dtrkasir' AND id_admin='$_SESSION[idadmin]'");
    }
    if (!$okkomisi) {
        $gagal = true;
    }
}
}

if ($gagal) {
    mysqli_rollback($GLOBALS["___mysqli_ston"]);
} else {
    mysqli_commit($GLOBALS["___mysqli_ston"]);
}

?>
