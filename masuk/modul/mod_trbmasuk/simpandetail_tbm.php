<?php
session_start();
if (empty($_SESSION['username']) and empty($_SESSION['passuser'])) {
    http_response_code(403);
    echo 'Anda harus login.';
    exit;
}

include "../../../configurasi/koneksi.php";

$kd_trbmasuk = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['kd_trbmasuk']);
$id_barang = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['id_barang']);
$kd_barang = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['kd_barang']);
$nmbrg_dtrbmasuk = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['nmbrg_dtrbmasuk']);
$qty_dtrbmasuk = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['qty_dtrbmasuk']);
$sat_dtrbmasuk = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['sat_dtrbmasuk']);
$hrgsat_dtrbmasuk = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['hrgsat_dtrbmasuk']);
$hrgjual_dtrbmasuk = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['hrgjual_dtrbmasuk']);

$no_batch = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['no_batch']);
$exp_date = date('Y-m-d', strtotime($_POST['exp_date']));

if ($qty_dtrbmasuk == "") {
	$qty_dtrbmasuk = "1";
} else {
}
if ($_POST['exp_date']=='')
{ $tgl_awal = date('Y-m-d');
    $exp_date=date('Y-m-d', strtotime('+720 days', strtotime( $tgl_awal)));
}
else {
    $exp_date = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $_POST['exp_date']);
}

mysqli_begin_transaction($GLOBALS["___mysqli_ston"]);
$gagal = false;

//cek apakah barang sudah ada
$cekdetail = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_barang, kd_barang, kd_trbmasuk, id_dtrbmasuk, qty_dtrbmasuk
FROM trbmasuk_detail
WHERE kd_barang='$kd_barang' AND kd_trbmasuk='$kd_trbmasuk'");

$ketemucekdetail = mysqli_num_rows($cekdetail);
$rcek = mysqli_fetch_array($cekdetail);
if ($ketemucekdetail > 0) {

	$id_dtrbmasuk = $rcek['id_dtrbmasuk'];
	$qtylama = $rcek['qty_dtrbmasuk'];
	$ttlqty = $qtylama + $qty_dtrbmasuk;
	$ttlharga = round(($ttlqty * $hrgsat_dtrbmasuk),0);

	$okdetail = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE trbmasuk_detail SET qty_dtrbmasuk = '$ttlqty',
										hrgsat_dtrbmasuk = '$hrgsat_dtrbmasuk',
										hrgjual_dtrbmasuk = '$hrgjual_dtrbmasuk',
										hrgttl_dtrbmasuk = '$ttlharga'
										WHERE id_dtrbmasuk = '$id_dtrbmasuk'");
	if (!$okdetail) {
	    $gagal = true;
	}

	//update stok
	$cekstok = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM barang WHERE id_barang='$id_barang'");
	$rst = mysqli_fetch_array($cekstok);

	$stok_barang = $rst['stok_barang'];
	$stokakhir = (($stok_barang - $qtylama) + $ttlqty);

	$okstok = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE barang SET
		stok_barang = '$stokakhir',
		sat_barang = '$sat_dtrbmasuk',
		hrgjual_barang = '$hrgjual_dtrbmasuk',
		hrgsat_barang = '$hrgsat_dtrbmasuk' WHERE id_barang = '$id_barang'");
	if (!$okstok) {
	    $gagal = true;
	}

	$caribrgmasuk = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM trbmasuk WHERE kd_trbmasuk = '$kd_trbmasuk'");
	$brm = mysqli_num_rows($caribrgmasuk);
	if($brm == 0){
	    $okrestore = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE  trkasir_restore SET
	        qty_dtrkasir        = '$ttlqty',
	        hrgjual_dtrkasir    = '$hrgjual_dtrbmasuk',
	        hrgttl_dtrkasir     = '$ttlharga'
	    WHERE kd_trkasir = '$kd_trbmasuk' AND id_barang = '$id_barang'");
	    if (!$okrestore) {
	        $gagal = true;
	    }
	}

} else {

	$ttlharga = round(($qty_dtrbmasuk * $hrgsat_dtrbmasuk),0);

	$okinsert = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO trbmasuk_detail(kd_trbmasuk,
										id_barang,
										kd_barang,
										nmbrg_dtrbmasuk,
										qty_dtrbmasuk,
										sat_dtrbmasuk,
										hrgsat_dtrbmasuk,
										hrgjual_dtrbmasuk,
										hrgttl_dtrbmasuk,
										no_batch,
										exp_date)
								  VALUES('$kd_trbmasuk',
										'$id_barang',
										'$kd_barang',
										'$nmbrg_dtrbmasuk',
										'$qty_dtrbmasuk',
										'$sat_dtrbmasuk',
										'$hrgsat_dtrbmasuk',
										'$hrgjual_dtrbmasuk',
										'$ttlharga',
										'$no_batch',
										'$exp_date')");

	if (!$okinsert) {
	    $gagal = true;
	}

	//update stok
	$cekstok = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM barang WHERE id_barang='$id_barang'");
	$rst = mysqli_fetch_array($cekstok);

	$stok_barang = $rst['stok_barang'];
	$stokakhir = $stok_barang + $qty_dtrbmasuk;

	$okstok = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE barang SET
		stok_barang = '$stokakhir',
		sat_barang = '$sat_dtrbmasuk',
		hrgjual_barang = '$hrgjual_dtrbmasuk',
		hrgsat_barang = '$hrgsat_dtrbmasuk' WHERE id_barang = '$id_barang'");
	if (!$okstok) {
	    $gagal = true;
	}

	$caribrgmasuk = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM trbmasuk WHERE kd_trbmasuk = '$kd_trbmasuk'");
	$brm = mysqli_num_rows($caribrgmasuk);
	if($brm == 0){
	    $okrestoreinsert = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO trkasir_restore(kd_trkasir, id_barang, kd_barang, nmbrg_dtrkasir, qty_dtrkasir, sat_dtrkasir, hrgjual_dtrkasir, hrgttl_dtrkasir) VALUES('$kd_trbmasuk','$id_barang','$kd_barang','$nmbrg_dtrbmasuk','$qty_dtrbmasuk','$sat_dtrbmasuk','$hrgjual_dtrbmasuk','$ttlharga')");
	    if (!$okrestoreinsert) {
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
