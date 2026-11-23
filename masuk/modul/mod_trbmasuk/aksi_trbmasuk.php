<?php
error_reporting(0);
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

function esc_trbmasuk($v) {
	return mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $v);
}

$module= "trbmasuk";
$stt_aksi=$_POST['stt_aksi'];
if($stt_aksi == "input_trbmasuk" || $stt_aksi == "ubah_trbmasuk"){
$act=$stt_aksi;
}else{
$act=$_GET['act'];
}


// Input admin
if ($module=='trbmasuk' AND $act=='input_trbmasuk'){

	$kd_trbmasuk = esc_trbmasuk($_POST['kd_trbmasuk']);
	$petugas = esc_trbmasuk($_POST['petugas']);
	$tgl_trbmasuk = esc_trbmasuk($_POST['tgl_trbmasuk']);
	$id_supplier = esc_trbmasuk($_POST['id_supplier']);
	$nm_supplier = esc_trbmasuk($_POST['nm_supplier']);
	$tlp_supplier = esc_trbmasuk($_POST['tlp_supplier']);
	$alamat_trbmasuk = esc_trbmasuk($_POST['alamat_trbmasuk']);
	$ttl_trkasir = esc_trbmasuk($_POST['ttl_trkasir']);
	$dp_bayar = esc_trbmasuk($_POST['dp_bayar']);
	$sisa_bayar = esc_trbmasuk($_POST['sisa_bayar']);
	$ket_trbmasuk = esc_trbmasuk($_POST['ket_trbmasuk']);
	$carabayar = esc_trbmasuk($_POST['carabayar']);
	$diskon2 = esc_trbmasuk($_POST['diskon2']);
	$id_admin_sesi = esc_trbmasuk($_SESSION['idadmin']);

	mysqli_begin_transaction($GLOBALS["___mysqli_ston"]);
	$gagal = false;

    $caribrgmasuk = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM trbmasuk WHERE kd_trbmasuk = '$kd_trbmasuk'");
	$brm = mysqli_num_rows($caribrgmasuk);
	if($brm == 0){
	    $shift = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM waktukerja ORDER BY id_shift DESC LIMIT 1");
	    $sh = mysqli_fetch_array($shift);

	    if($sh['shift']=='pagi'){
	        $rshift = '1';
	    } else {
	        $rshift = '2';
	    }

	    $okrestore = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE  trkasir_restore SET
	        petugas             = '$petugas',
	        shift               = '$rshift',
	        tgl_trkasir         = '$tgl_trbmasuk',
	        nm_pelanggan        = '$nm_supplier',
	        tlp_pelanggan       = '$tlp_supplier',
	        alamat_pelanggan    = '$alamat_trbmasuk',
	        ttl_trkasir         = '$ttl_trkasir',
	        dp_bayar            = '$dp_bayar',
	        diskon1             = '$diskon2',
	        diskon2             = '',
	        sisa_bayar          = '$sisa_bayar',
	        ket_trkasir         = '$ket_trbmasuk',
	        id_carabayar        = '$carabayar'
	    WHERE kd_trkasir = '$kd_trbmasuk'");

	    if (!$okrestore) {
	        $gagal = true;
	    }
	}

	if (!$gagal) {
	    $okheader = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO
										trbmasuk(id_resto,
										kd_trbmasuk,
										tgl_trbmasuk,
										id_supplier,
										petugas,
										nm_supplier,
										tlp_supplier,
										alamat_trbmasuk,
										ttl_trbmasuk,
										dp_bayar,
										sisa_bayar,
										ket_trbmasuk,
										carabayar)
								 VALUES('pusat',
										'$kd_trbmasuk',
										'$tgl_trbmasuk',
										'$id_supplier',
										'$petugas',
										'$nm_supplier',
										'$tlp_supplier',
										'$alamat_trbmasuk',
										'$ttl_trkasir',
										'$dp_bayar',
										'$sisa_bayar',
										'$ket_trbmasuk',
										'$carabayar'
										)");

	    if (!$okheader) {
	        $gagal = true;
	    }
	}

	if (!$gagal) {
		$tgl_sekarang = date('Y-m-d H:i:s', time());
		$okkartustok = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO kartu_stok(kode_transaksi, tgl_sekarang) VALUES('$kd_trbmasuk','$tgl_sekarang')");

		$okkdbm = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE kdbm SET stt_kdbm = 'OFF' WHERE id_admin = '$id_admin_sesi' AND id_resto = 'pusat' AND kd_trbmasuk = '$kd_trbmasuk'");

		if (!$okkartustok || !$okkdbm) {
		    $gagal = true;
		}
	}

	if ($gagal) {
	    mysqli_rollback($GLOBALS["___mysqli_ston"]);
	} else {
	    mysqli_commit($GLOBALS["___mysqli_ston"]);
	}

	//echo "<script type='text/javascript'>alert('Transkasi berhasil ditambahkan !');window.location='../../media_admin.php?module=".$module."'</script>";


}
 //updata trbmasuk
 elseif ($module=='trbmasuk' AND $act=='ubah_trbmasuk'){

	$tgl_trbmasuk = esc_trbmasuk($_POST['tgl_trbmasuk']);
	$id_supplier = esc_trbmasuk($_POST['id_supplier']);
	$nm_supplier = esc_trbmasuk($_POST['nm_supplier']);
	$tlp_supplier = esc_trbmasuk($_POST['tlp_supplier']);
	$alamat_trbmasuk = esc_trbmasuk($_POST['alamat_trbmasuk']);
	$ttl_trkasir = esc_trbmasuk($_POST['ttl_trkasir']);
	$dp_bayar = esc_trbmasuk($_POST['dp_bayar']);
	$sisa_bayar = esc_trbmasuk($_POST['sisa_bayar']);
	$ket_trbmasuk = esc_trbmasuk($_POST['ket_trbmasuk']);
	$carabayar = esc_trbmasuk($_POST['carabayar']);
	$id_trbmasuk = esc_trbmasuk($_POST['id_trbmasuk']);

    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE trbmasuk SET tgl_trbmasuk = '$tgl_trbmasuk',
									id_supplier = '$id_supplier',
									nm_supplier = '$nm_supplier',
									tlp_supplier = '$tlp_supplier',
									alamat_trbmasuk = '$alamat_trbmasuk',
									ttl_trbmasuk = '$ttl_trkasir',
									dp_bayar = '$dp_bayar',
									sisa_bayar = '$sisa_bayar',
									ket_trbmasuk = '$ket_trbmasuk',
									carabayar = '$carabayar'
									WHERE id_trbmasuk = '$id_trbmasuk'");


	//echo "<script type='text/javascript'>alert('Transkasi berhasil Ubah !');window.location='../../media_admin.php?module=".$module."'</script>";

}
//Hapus Proyek
elseif ($module=='trbmasuk' AND $act=='hapus'){

	$id_trbmasuk = esc_trbmasuk($_GET['id']);

	mysqli_begin_transaction($GLOBALS["___mysqli_ston"]);
	$gagal = false;

  //update bagian stok dulu
  //ambil data induk
	$ambildatainduk=mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_trbmasuk, kd_trbmasuk FROM trbmasuk
	WHERE id_trbmasuk='$id_trbmasuk'");
	$r1=mysqli_fetch_array($ambildatainduk);
	$kd_trbmasuk = esc_trbmasuk($r1['kd_trbmasuk']);

	//loop data detail
	//ambil data induk
	$ambildatadetail=mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_dtrbmasuk, kd_trbmasuk, id_barang, qty_dtrbmasuk FROM trbmasuk_detail WHERE kd_trbmasuk='$kd_trbmasuk'");
	while (!$gagal && ($r=mysqli_fetch_array($ambildatadetail))){

	$id_dtrbmasuk = esc_trbmasuk($r['id_dtrbmasuk']);
	$id_barang = esc_trbmasuk($r['id_barang']);
	$qty_dtrbmasuk = $r['qty_dtrbmasuk'];

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

	}

	if (!$gagal) {
	    $okheader = mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM trbmasuk WHERE id_trbmasuk = '$id_trbmasuk'");
	    $okkartustok = mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM kartu_stok WHERE kode_transaksi = '$kd_trbmasuk'");

	    if (!$okheader || !$okkartustok) {
	        $gagal = true;
	    }
	}

	if ($gagal) {
	    mysqli_rollback($GLOBALS["___mysqli_ston"]);
	    echo "<script type='text/javascript'>alert('Gagal menghapus data, semua perubahan dibatalkan.');window.location='../../media_admin.php?module=".$module."'</script>";
	} else {
	    mysqli_commit($GLOBALS["___mysqli_ston"]);
	    echo "<script type='text/javascript'>alert('Data berhasil dihapus !');window.location='../../media_admin.php?module=".$module."'</script>";
	}
}

}
?>
