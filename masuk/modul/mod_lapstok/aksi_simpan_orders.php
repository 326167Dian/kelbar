<?php
session_start();
if (empty($_SESSION['username']) and empty($_SESSION['passuser'])) {
    echo "<script type='text/javascript'>alert('Anda harus login.');history.back();</script>";
    exit;
}

include "../../../configurasi/koneksi.php";

$db = $GLOBALS["___mysqli_ston"];

function esc_simpan_orders($v) {
    return mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $v);
}

$terpilih = isset($_POST['check']) ? $_POST['check'] : array();

if (empty($terpilih)) {
    echo "<script type='text/javascript'>alert('Belum ada barang yang dipilih.');history.back();</script>";
    exit;
}

$id_admin = esc_simpan_orders($_SESSION['idadmin']);

mysqli_begin_transaction($db);
$gagal = false;

// Cek apakah sudah ada kode transaksi pesanan (kdbm, id_resto='pesan') yang masih berjalan
// untuk user ini - kalau ada, barang baru digabung ke pesanan yang sama (konsisten dengan
// alur mod_orders/orders.php act=tambah).
$cekkd = mysqli_query($db, "SELECT * FROM kdbm WHERE id_admin='$id_admin' AND id_resto='pesan' AND stt_kdbm='ON'");
$ketemucekkd = mysqli_num_rows($cekkd);
$hcekkd = mysqli_fetch_assoc($cekkd);

if ($ketemucekkd > 0) {
    $kdtransaksi = $hcekkd['kd_trbmasuk'];
} else {
    $kdunik = date('dmyhis');
    $kdtransaksi = "ORD-" . $kdunik;

    $cekkd2 = mysqli_query($db, "SELECT * FROM kdbm WHERE kd_trbmasuk='$kdtransaksi'");
    if (mysqli_num_rows($cekkd2) > 0) {
        $kdunik = date('dmyhis') + 1;
        $kdtransaksi = "ORD-" . $kdunik;
    }

    $okkdbm = mysqli_query($db, "INSERT INTO kdbm(kd_trbmasuk, id_resto, id_admin) VALUES('$kdtransaksi', 'pesan', '$id_admin')");
    if (!$okkdbm) {
        $gagal = true;
    }
}

if (!$gagal) {
    foreach ($terpilih as $kd_barang_raw) {
        $kd_barang = esc_simpan_orders($kd_barang_raw);

        $cekbrg = mysqli_query($db, "SELECT id_barang, kd_barang, nm_barang, stok_barang, sat_barang, hrgsat_barang, t30
            FROM barang WHERE kd_barang='$kd_barang'");
        $brg = mysqli_fetch_assoc($cekbrg);

        if (!$brg) {
            continue; // barang sudah tidak ada di master, lewati
        }

        // Estimasi qty yang perlu dipesan: selisih frekuensi transaksi 30 hari terakhir dengan stok saat ini.
        // Tidak pernah negatif - kalau stok masih cukup, qty pesan dianggap 0.
        $qty_retail = max(0, $brg['t30'] - $brg['stok_barang']);
        if ($qty_retail <= 0) {
            continue;
        }

        $id_barang = $brg['id_barang'];
        $nm_barang = esc_simpan_orders($brg['nm_barang']);
        $sat_barang = esc_simpan_orders($brg['sat_barang']);
        $hrgsat_barang = $brg['hrgsat_barang'];
        $ttl_harga = $hrgsat_barang * $qty_retail;

        // Project ini belum menyimpan faktor konversi grosir per barang, jadi qty/satuan
        // grosir disamakan dengan qty/satuan ecer - staf tetap bisa menyesuaikan nanti
        // di halaman "Pesan Barang" sebelum transaksi disimpan.
        $qty_grosir = $qty_retail;
        $sat_grosir = $sat_barang;

        // Gabungkan ke baris yang sudah ada kalau barang ini sebelumnya sudah ditambahkan
        // ke pesanan yang sama (konsisten dengan mod_orders/simpandetail_tbm.php).
        $cekdetail = mysqli_query($db, "SELECT id_dtrbmasuk, qty_dtrbmasuk FROM ordersdetail
            WHERE kd_barang='$kd_barang' AND kd_trbmasuk='$kdtransaksi'");
        $rcek = mysqli_fetch_assoc($cekdetail);

        if ($rcek) {
            $ttlqty = $rcek['qty_dtrbmasuk'] + $qty_retail;
            $ttlharga = $ttlqty * $hrgsat_barang;
            $okdetail = mysqli_query($db, "UPDATE ordersdetail SET
                qty_dtrbmasuk = '$ttlqty',
                hrgsat_dtrbmasuk = '$hrgsat_barang',
                hrgttl_dtrbmasuk = '$ttlharga',
                qtygrosir_dtrbmasuk = '$ttlqty',
                satgrosir_dtrbmasuk = '$sat_grosir'
                WHERE id_dtrbmasuk = '" . $rcek['id_dtrbmasuk'] . "'");
        } else {
            $okdetail = mysqli_query($db, "INSERT INTO ordersdetail(
                    kd_trbmasuk, id_barang, kd_barang, nmbrg_dtrbmasuk, qty_dtrbmasuk,
                    sat_dtrbmasuk, hrgsat_dtrbmasuk, hrgttl_dtrbmasuk, qtygrosir_dtrbmasuk, satgrosir_dtrbmasuk)
                VALUES(
                    '$kdtransaksi', '$id_barang', '$kd_barang', '$nm_barang', '$qty_retail',
                    '$sat_barang', '$hrgsat_barang', '$ttl_harga', '$qty_grosir', '$sat_grosir')");
        }

        if (!$okdetail) {
            $gagal = true;
            break;
        }
    }
}

if ($gagal) {
    mysqli_rollback($db);
    echo "<script type='text/javascript'>alert('Gagal menambahkan barang ke pesanan, semua perubahan dibatalkan.');history.back();</script>";
} else {
    mysqli_commit($db);
    echo '<script type="text/javascript">window.location.href = "../../media_admin.php?module=orders&act=tambah";</script>';
}
?>
