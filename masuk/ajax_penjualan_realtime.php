<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['username']) || empty($_SESSION['passuser'])) {
    http_response_code(401);
    echo json_encode([
        'status' => false,
        'message' => 'Sesi login tidak valid'
    ]);
    exit;
}

if (empty($_SESSION['level']) || $_SESSION['level'] != 'pemilik') {
    http_response_code(403);
    echo json_encode([
        'status' => false,
        'message' => 'Hanya pemilik yang dapat melihat data ini'
    ]);
    exit;
}

include "../configurasi/koneksi.php";

try {
    $bulanRequest = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('n');
    $tahunRequest = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

    if ($bulanRequest < 1 || $bulanRequest > 12) {
        $bulanRequest = (int)date('n');
    }

    if ($tahunRequest < 2000 || $tahunRequest > 2100) {
        $tahunRequest = (int)date('Y');
    }

    $awalBulan = sprintf('%04d-%02d-01', $tahunRequest, $bulanRequest);
    $akhirBulanPenuh = date('Y-m-t', strtotime($awalBulan));

    $bulanSekarang = (int)date('n');
    $tahunSekarang = (int)date('Y');
    if ($bulanRequest === $bulanSekarang && $tahunRequest === $tahunSekarang) {
        $akhirPeriode = date('Y-m-d');
    } else {
        $akhirPeriode = $akhirBulanPenuh;
    }

    $awalBulanSebelumnya = date('Y-m-01', strtotime($awalBulan . ' -1 month'));
    $akhirBulanSebelumnya = date('Y-m-t', strtotime($awalBulanSebelumnya));

    $sql = "SELECT tgl_trkasir AS tanggal, SUM(ttl_trkasir) AS total_nilai
            FROM trkasir
            WHERE tgl_trkasir BETWEEN '$awalBulan' AND '$akhirPeriode'
            GROUP BY tgl_trkasir
            ORDER BY tgl_trkasir ASC";
    $hasil = mysqli_query($GLOBALS["___mysqli_ston"], $sql);
    if ($hasil === false) {
        throw new Exception(mysqli_error($GLOBALS["___mysqli_ston"]));
    }

    $mapNilai = [];
    while ($row = mysqli_fetch_assoc($hasil)) {
        $mapNilai[$row['tanggal']] = (float)$row['total_nilai'];
    }

    $sqlPrev = "SELECT tgl_trkasir AS tanggal, SUM(ttl_trkasir) AS total_nilai
                FROM trkasir
                WHERE tgl_trkasir BETWEEN '$awalBulanSebelumnya' AND '$akhirBulanSebelumnya'
                GROUP BY tgl_trkasir
                ORDER BY tgl_trkasir ASC";
    $hasilPrev = mysqli_query($GLOBALS["___mysqli_ston"], $sqlPrev);
    if ($hasilPrev === false) {
        throw new Exception(mysqli_error($GLOBALS["___mysqli_ston"]));
    }

    $mapNilaiPrevByHari = [];
    while ($rowPrev = mysqli_fetch_assoc($hasilPrev)) {
        $hari = date('d', strtotime($rowPrev['tanggal']));
        $mapNilaiPrevByHari[$hari] = (float)$rowPrev['total_nilai'];
    }

    $dataHarian = [];
    $dataBulanLalu = [];
    $tanggalLoop = $awalBulan;
    while ($tanggalLoop <= $akhirPeriode) {
        $total = 0;
        if (isset($mapNilai[$tanggalLoop])) {
            $total = $mapNilai[$tanggalLoop];
        }

        $hariLoop = date('d', strtotime($tanggalLoop));
        $totalPrev = 0;
        if (isset($mapNilaiPrevByHari[$hariLoop])) {
            $totalPrev = $mapNilaiPrevByHari[$hariLoop];
        }

        $dataHarian[] = [
            'tanggal' => $tanggalLoop,
            'nilai' => $total
        ];

        $dataBulanLalu[] = [
            'hari' => $hariLoop,
            'nilai' => $totalPrev
        ];

        $tanggalLoop = date('Y-m-d', strtotime($tanggalLoop . ' +1 day'));
    }

    echo json_encode([
        'status' => true,
        'judul_tipe' => 'Penjualan',
        'data' => $dataHarian,
        'data_bulan_lalu' => $dataBulanLalu,
        'periode_label' => date('F Y', strtotime($awalBulan)),
        'periode_sebelumnya_label' => date('F Y', strtotime($awalBulanSebelumnya)),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Gagal memuat data'
    ]);
}
