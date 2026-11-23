<?php
error_reporting(0);
session_start();
if (empty($_SESSION['username']) and empty($_SESSION['passuser'])) {
	echo "<link href='style.css' rel='stylesheet' type='text/css'>
 <center>Untuk mengakses modul, Anda harus login <br>";
	echo "<a href=../../index.php><b>LOGIN</b></a></center>";
} else {
	include "../../../configurasi/koneksi.php";
	include "../../../configurasi/fungsi_thumb.php";
	include "../../../configurasi/library.php";

	function esc_trkasir($v) {
		return mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $v);
	}

	$module = "trkasir";
	$stt_aksi = $_POST['stt_aksi'];
	if ($stt_aksi == "input_trkasir" || $stt_aksi == "ubah_trkasir") {
		$act = $stt_aksi;
	} else {
		$act = $_GET['act'];
	}

	// Input admin
	if ($module == 'trkasir' and $act == 'input_trkasir') {

		$kd_trkasir = esc_trkasir($_POST['kd_trkasir']);
		$petugas = esc_trkasir($_POST['petugas']);
		$shift = esc_trkasir($_POST['shift']);
		$tgl_trkasir = esc_trkasir($_POST['tgl_trkasir']);
		$nm_pelanggan = esc_trkasir($_POST['nm_pelanggan']);
		$tlp_pelanggan = esc_trkasir($_POST['tlp_pelanggan']);
		$alamat_pelanggan = esc_trkasir($_POST['alamat_pelanggan']);
		$ttl_trkasir = esc_trkasir($_POST['ttl_trkasir']);
		$diskon2 = esc_trkasir($_POST['diskon2']);
		$dp_bayar = esc_trkasir($_POST['dp_bayar']);
		$sisa_bayar = esc_trkasir($_POST['sisa_bayar']);
		$ket_trkasir = esc_trkasir($_POST['ket_trkasir']);
		$id_carabayar = esc_trkasir($_POST['id_carabayar']);
		$id_admin_sesi = esc_trkasir($_SESSION['idadmin']);

        $cariitem = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM trkasir_detail WHERE kd_trkasir = '$kd_trkasir'");
        $countItem = mysqli_num_rows($cariitem);

        if($countItem <= 0){
            $data['message'] = 'failed';
			echo json_encode($data);
        } else {
            mysqli_begin_transaction($GLOBALS["___mysqli_ston"]);
            $gagal = false;

    		$insert = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO trkasir(
    										kd_trkasir,
    										petugas,
    										shift,
    										tgl_trkasir,
    										nm_pelanggan,
    										tlp_pelanggan,
    										alamat_pelanggan,
    										ttl_trkasir,
    										diskon2,
    										dp_bayar,
    										sisa_bayar,
    										ket_trkasir,
    										id_carabayar
    										)
    								 VALUES('$kd_trkasir',
    								 		'$petugas',
    								 		'$shift',
    										'$tgl_trkasir',
    										'$nm_pelanggan',
    										'$tlp_pelanggan',
    										'$alamat_pelanggan',
    										'$ttl_trkasir',
    										'$diskon2',
    										'$dp_bayar',
    										'$sisa_bayar',
    										'$ket_trkasir',
    										'$id_carabayar'
    										)");

    		if (!$insert) {
    			$gagal = true;
    		}

            if (!$gagal) {
                $tgl_sekarang = date('Y-m-d H:i:s', time());
                $okkartustok = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO kartu_stok(kode_transaksi, tgl_sekarang) VALUES('$kd_trkasir','$tgl_sekarang')");
                $okkdtk = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE kdtk SET stt_kdtk = 'OFF' WHERE id_admin = '$id_admin_sesi' AND kd_trkasir = '$kd_trkasir'");

                if (!$okkartustok || !$okkdtk) {
                    $gagal = true;
                }
            }

    		if (!$gagal) {
                $ambildatainduk = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM trkasir WHERE kd_trkasir='$kd_trkasir'");
    			$r1 = mysqli_fetch_array($ambildatainduk);

    			//loop data detail
    			$ambildatadetail = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM trkasir_detail WHERE kd_trkasir='$kd_trkasir'");
    			while (!$gagal && ($r = mysqli_fetch_array($ambildatadetail))) {
                    $okrestore = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO trkasir_restore(
    						kd_trkasir, petugas, shift, tgl_trkasir, nm_pelanggan, tlp_pelanggan, alamat_pelanggan,
    						ttl_trkasir, dp_bayar, diskon1, diskon2, sisa_bayar, ket_trkasir, id_carabayar, id_barang,
    						kd_barang, nmbrg_dtrkasir, qty_dtrkasir, sat_dtrkasir, hrgjual_dtrkasir, hrgttl_dtrkasir)
    					VALUES(
    						'" . esc_trkasir($r1['kd_trkasir']) . "','" . esc_trkasir($r1['petugas']) . "','" . esc_trkasir($r1['shift']) . "','" . esc_trkasir($r1['tgl_trkasir']) . "','" . esc_trkasir($r1['nm_pelanggan']) . "','" . esc_trkasir($r1['tlp_pelanggan']) . "','" . esc_trkasir($r1['alamat_pelanggan']) . "','" . esc_trkasir($r1['ttl_trkasir']) . "','" . esc_trkasir($r1['dp_bayar']) . "','" . esc_trkasir($r1['diskon1']) . "','" . esc_trkasir($r1['diskon2']) . "','" . esc_trkasir($r1['sisa_bayar']) . "','" . esc_trkasir($r1['ket_trkasir']) . "','" . esc_trkasir($r1['id_carabayar']) . "','" . esc_trkasir($r['id_barang']) . "','" . esc_trkasir($r['kd_barang']) . "','" . esc_trkasir($r['nmbrg_dtrkasir']) . "','" . esc_trkasir($r['qty_dtrkasir']) . "','" . esc_trkasir($r['sat_dtrkasir']) . "','" . esc_trkasir($r['hrgjual_dtrkasir']) . "','" . esc_trkasir($r['hrgttl_dtrkasir']) . "')");

                    if (!$okrestore) {
                        $gagal = true;
                    }
                }
            }

            if ($gagal) {
                mysqli_rollback($GLOBALS["___mysqli_ston"]);
                $data['message'] = 'failed';
                echo json_encode($data);
            } else {
                mysqli_commit($GLOBALS["___mysqli_ston"]);
                $data['message'] = 'success';
                echo json_encode($data);
            }
        }
		//echo "<script type='text/javascript'>alert('Transkasi berhasil ditambahkan !');window.location='../../media_admin.php?module=".$module."'</script>";
	}

	//updata trkasir
	elseif ($module == 'trkasir' and $act == 'ubah_trkasir') {

		$tgl_trkasir = esc_trkasir($_POST['tgl_trkasir']);
		$petugas = esc_trkasir($_POST['petugas']);
		$nm_pelanggan = esc_trkasir($_POST['nm_pelanggan']);
		$tlp_pelanggan = esc_trkasir($_POST['tlp_pelanggan']);
		$alamat_pelanggan = esc_trkasir($_POST['alamat_pelanggan']);
		$ttl_trkasir = esc_trkasir($_POST['ttl_trkasir']);
		$diskon2 = esc_trkasir($_POST['diskon2']);
		$dp_bayar = esc_trkasir($_POST['dp_bayar']);
		$sisa_bayar = esc_trkasir($_POST['sisa_bayar']);
		$ket_trkasir = esc_trkasir($_POST['ket_trkasir']);
		$id_carabayar = esc_trkasir($_POST['id_carabayar']);
		$id_trkasir = esc_trkasir($_POST['id_trkasir']);

		$ubah = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE trkasir SET tgl_trkasir = '$tgl_trkasir',
									petugas = '$petugas',
									nm_pelanggan = '$nm_pelanggan',
									tlp_pelanggan = '$tlp_pelanggan',
									alamat_pelanggan = '$alamat_pelanggan',
									ttl_trkasir = '$ttl_trkasir',
									diskon2 = '$diskon2',
									dp_bayar = '$dp_bayar',
									sisa_bayar = '$sisa_bayar',
									ket_trkasir = '$ket_trkasir',
									id_carabayar = '$id_carabayar'
									WHERE id_trkasir = '$id_trkasir'");


        if($ubah){
            $data['message'] = 'success';
    		echo json_encode($data);
    	} else {
    		$data['message'] = 'failed';
    		echo json_encode($data);
    	}
		//echo "<script type='text/javascript'>alert('Transkasi berhasil Ubah !');window.location='../../media_admin.php?module=".$module."'</script>";


	}
	//Hapus Proyek
	elseif ($module == 'trkasir' and $act == 'hapus') {

		if ($_SESSION['level'] != 'pemilik') {
			echo "<script type='text/javascript'>window.location='../../media_admin.php?module=" . $module . "'</script>";
		} else {
			$id_trkasir = esc_trkasir($_GET['id']);

			mysqli_begin_transaction($GLOBALS["___mysqli_ston"]);
			$gagal = false;

			//update bagian stok dulu
			//ambil data induk
			$ambildatainduk = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM trkasir WHERE id_trkasir='$id_trkasir'");
			$r1 = mysqli_fetch_array($ambildatainduk);
			$kd_trkasir = esc_trkasir($r1['kd_trkasir']);

			//loop data detail
			$ambildatadetail = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM trkasir_detail WHERE kd_trkasir='$kd_trkasir'");
			while (!$gagal && ($r = mysqli_fetch_array($ambildatadetail))) {

				$id_dtrkasir = esc_trkasir($r['id_dtrkasir']);
				$id_barang = esc_trkasir($r['id_barang']);
				$qty_dtrkasir = $r['qty_dtrkasir'];

				$okrestore = mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO trkasir_restore(
						kd_trkasir, petugas, shift, tgl_trkasir, nm_pelanggan, tlp_pelanggan, alamat_pelanggan,
						ttl_trkasir, dp_bayar, diskon1, diskon2, sisa_bayar, ket_trkasir, id_carabayar, id_barang,
						kd_barang, nmbrg_dtrkasir, qty_dtrkasir, sat_dtrkasir, hrgjual_dtrkasir, hrgttl_dtrkasir)
					VALUES(
						'" . esc_trkasir($r1['kd_trkasir']) . "','" . esc_trkasir($r1['petugas']) . "','" . esc_trkasir($r1['shift']) . "','" . esc_trkasir($r1['tgl_trkasir']) . "','" . esc_trkasir($r1['nm_pelanggan']) . "','" . esc_trkasir($r1['tlp_pelanggan']) . "','" . esc_trkasir($r1['alamat_pelanggan']) . "','" . esc_trkasir($r1['ttl_trkasir']) . "','" . esc_trkasir($r1['dp_bayar']) . "','" . esc_trkasir($r1['diskon1']) . "','" . esc_trkasir($r1['diskon2']) . "','" . esc_trkasir($r1['sisa_bayar']) . "','" . esc_trkasir($r1['ket_trkasir']) . "','" . esc_trkasir($r1['id_carabayar']) . "','$id_barang','" . esc_trkasir($r['kd_barang']) . "','" . esc_trkasir($r['nmbrg_dtrkasir']) . "','" . esc_trkasir($r['qty_dtrkasir']) . "','" . esc_trkasir($r['sat_dtrkasir']) . "','" . esc_trkasir($r['hrgjual_dtrkasir']) . "','" . esc_trkasir($r['hrgttl_dtrkasir']) . "')");

				//update stok
				$cekstok = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_barang, stok_barang FROM barang
			WHERE id_barang='$id_barang'");
				$rst = mysqli_fetch_array($cekstok);

				$stok_barang = $rst['stok_barang'];
				$stokakhir = $stok_barang + $qty_dtrkasir;

				$okstok = mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE barang SET stok_barang = '$stokakhir' WHERE id_barang = '$id_barang'");

				$okdetail = mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM trkasir_detail WHERE id_dtrkasir = '$id_dtrkasir'");

				$okkomisi = mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM komisi_pegawai WHERE id_dtrkasir = '$id_dtrkasir'");

				if (!$okrestore || !$okstok || !$okdetail || !$okkomisi) {
					$gagal = true;
				}
			}

			if (!$gagal) {
				$okheader = mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM trkasir WHERE id_trkasir = '$id_trkasir'");
				$okkartustok = mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM kartu_stok WHERE kode_transaksi = '$kd_trkasir'");

				if (!$okheader || !$okkartustok) {
					$gagal = true;
				}
			}

			if ($gagal) {
				mysqli_rollback($GLOBALS["___mysqli_ston"]);
				echo "<script type='text/javascript'>alert('Gagal menghapus data, semua perubahan dibatalkan.');window.location='../../media_admin.php?module=" . $module . "'</script>";
			} else {
				mysqli_commit($GLOBALS["___mysqli_ston"]);
				echo "<script type='text/javascript'>alert('Data berhasil dihapus !');window.location='../../media_admin.php?module=" . $module . "'</script>";
			}
		}
	}
}
