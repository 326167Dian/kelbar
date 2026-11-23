<?php
session_start();
include_once '../../../configurasi/koneksi.php';
include_once '../../../configurasi/fungsi_rupiah.php';

if (empty($_SESSION['username']) || empty($_SESSION['passuser'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Anda harus login.']);
    exit;
}

if ($_GET['action'] == 'table_data') {

    $columns = array(
        0 => 'a.id_trkasir',
        1 => 'a.kd_trkasir',
        2 => 'a.petugas',
        3 => 'a.tgl_trkasir',
        4 => 'a.nm_pelanggan',
        5 => 'b.nm_carabayar',
        6 => 'a.ttl_trkasir',
        7 => 'a.shift',
        8 => 'a.id_trkasir',
    );

    $db = $GLOBALS["___mysqli_ston"];
    $aksi = 'modul/mod_trkasir/aksi_trkasir.php';
    $tgl_awal = date('Y-m-d');

    $limit = isset($_POST['length']) ? (int) $_POST['length'] : 10;
    $start = isset($_POST['start']) ? (int) $_POST['start'] : 0;
    $orderIndex = isset($_POST['order'][0]['column']) ? (int) $_POST['order'][0]['column'] : 0;
    $order = isset($columns[$orderIndex]) ? $columns[$orderIndex] : 'a.id_trkasir';
    $dir = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'asc') ? 'ASC' : 'DESC';
    $search = isset($_POST['search']['value']) ? mysqli_real_escape_string($db, trim($_POST['search']['value'])) : '';

    $baseFrom = ' FROM trkasir a JOIN carabayar b ON a.id_carabayar = b.id_carabayar ';
    $baseWhere = " WHERE a.tgl_trkasir = '$tgl_awal' ";

    $searchWhere = '';
    if ($search !== '') {
        $searchWhere = " AND (
            a.kd_trkasir LIKE '%$search%'
            OR a.petugas LIKE '%$search%'
            OR a.tgl_trkasir LIKE '%$search%'
            OR a.nm_pelanggan LIKE '%$search%'
            OR b.nm_carabayar LIKE '%$search%'
            OR a.ttl_trkasir LIKE '%$search%'
            OR a.shift LIKE '%$search%'
        )";
    }

    $whereSql = $baseWhere . $searchWhere;

    $countResult = mysqli_query($db, 'SELECT COUNT(a.id_trkasir) AS jumlah' . $baseFrom . $baseWhere);
    $totalData = (int) mysqli_fetch_assoc($countResult)['jumlah'];

    $filteredResult = mysqli_query($db, 'SELECT COUNT(a.id_trkasir) AS jumlah' . $baseFrom . $whereSql);
    $totalFiltered = (int) mysqli_fetch_assoc($filteredResult)['jumlah'];

    // Satu query agregat untuk semua total ringkasan, menggantikan 12 query terpisah yang
    // sebelumnya dijalankan ulang di setiap baris transaksi (sumber utama lambatnya halaman ini).
    $summarySql = 'SELECT
            COALESCE(SUM(a.ttl_trkasir), 0) AS total_kasir,
            COALESCE(SUM(CASE WHEN a.id_carabayar = 1 THEN a.ttl_trkasir ELSE 0 END), 0) AS total_tunai,
            COALESCE(SUM(CASE WHEN a.id_carabayar = 1 AND a.shift = 1 THEN a.ttl_trkasir ELSE 0 END), 0) AS total_tunai_pagi,
            COALESCE(SUM(CASE WHEN a.id_carabayar = 1 AND a.shift = 2 THEN a.ttl_trkasir ELSE 0 END), 0) AS total_tunai_sore,
            COALESCE(SUM(CASE WHEN a.id_carabayar = 2 THEN a.ttl_trkasir ELSE 0 END), 0) AS total_transfer,
            COALESCE(SUM(CASE WHEN a.id_carabayar = 2 AND a.shift = 1 THEN a.ttl_trkasir ELSE 0 END), 0) AS total_transfer_pagi,
            COALESCE(SUM(CASE WHEN a.id_carabayar = 2 AND a.shift = 2 THEN a.ttl_trkasir ELSE 0 END), 0) AS total_transfer_sore,
            COALESCE(SUM(CASE WHEN a.id_carabayar = 3 THEN a.ttl_trkasir ELSE 0 END), 0) AS total_tempo,
            COALESCE(SUM(CASE WHEN a.id_carabayar = 3 AND a.shift = 1 THEN a.ttl_trkasir ELSE 0 END), 0) AS total_tempo_pagi,
            COALESCE(SUM(CASE WHEN a.id_carabayar = 3 AND a.shift = 2 THEN a.ttl_trkasir ELSE 0 END), 0) AS total_tempo_sore,
            COALESCE(SUM(CASE WHEN a.shift = 1 THEN a.ttl_trkasir ELSE 0 END), 0) AS total_shift1,
            COALESCE(SUM(CASE WHEN a.shift = 2 THEN a.ttl_trkasir ELSE 0 END), 0) AS total_shift2'
        . $baseFrom . $whereSql;
    $summaryResult = mysqli_query($db, $summarySql);
    $summary = mysqli_fetch_assoc($summaryResult);

    $querySql = 'SELECT a.*, b.nm_carabayar'
        . $baseFrom . $whereSql
        . ' ORDER BY ' . $order . ' ' . $dir . ' LIMIT ' . $limit . ' OFFSET ' . $start;
    $query = mysqli_query($db, $querySql);

    $data = array();
    $no = $start + 1;

    while ($value = mysqli_fetch_assoc($query)) {
        $nestedData = array();
        $nestedData['no'] = $no;
        $nestedData['kd_trkasir'] = $value['kd_trkasir'];
        $nestedData['petugas'] = $value['petugas'];
        $nestedData['tgl_trkasir'] = $value['tgl_trkasir'];
        $nestedData['nm_pelanggan'] = $value['nm_pelanggan'];
        $nestedData['nm_carabayar'] = $value['nm_carabayar'];
        $nestedData['ttl_trkasir'] = $value['ttl_trkasir'];
        $nestedData['shift'] = $value['shift'];
        $nestedData['tempo'] = ($value['id_carabayar'] == 3) ? 1 : 0;

        $pilih = '';
        if ($_SESSION['level'] == 'pemilik') {
            $pilih .= "<a href='?module=trkasir&act=ubah&id=" . $value['id_trkasir'] . "' title='EDIT' class='glyphicon glyphicon-pencil'>&nbsp</a>
                       <a href=\"javascript:confirmdelete('" . $aksi . "?module=trkasir&act=hapus&id=" . $value['id_trkasir'] . "')\" title='HAPUS' class='glyphicon glyphicon-remove'>&nbsp</a>";
        }
        $pilih .= "<a class='glyphicon glyphicon-print' onclick=\"window.open('modul/mod_laporan/struk.php?kd_trkasir=" . $value['kd_trkasir'] . "','nama window','width=500,height=600,toolbar=no,location=no,directories=no,status=no,menubar=no, scrollbars=no,resizable=yes,copyhistory=no')\">&nbsp</a>
            <a class='btn btn-success btn-xs' onclick=\"window.open('modul/mod_laporan/strukresep.php?kd_trkasir=" . $value['kd_trkasir'] . "','nama window','width=500,height=600,toolbar=no,location=no,directories=no,status=no,menubar=no, scrollbars=no,resizable=yes,copyhistory=no')\">Resep</a>
            <a href='modul/mod_laporan/kwitansi.php?kd_trkasir=" . $value['kd_trkasir'] . "' target='_blank' title='KWITANSI' class='btn btn-warning btn-xs'>KWITANSI</a>";

        $nestedData['pilih'] = $pilih;

        $data[] = $nestedData;
        $no++;
    }

    $json_data = array(
        'draw' => intval($_POST['draw']),
        'recordsTotal' => $totalData,
        'recordsFiltered' => $totalFiltered,
        'data' => $data,
        'totalKasir' => (float) $summary['total_kasir'],
        'totalTunai' => (float) $summary['total_tunai'],
        'totalTunaiPagi' => (float) $summary['total_tunai_pagi'],
        'totalTunaiSore' => (float) $summary['total_tunai_sore'],
        'totalTransfer' => (float) $summary['total_transfer'],
        'totalTransferPagi' => (float) $summary['total_transfer_pagi'],
        'totalTransferSore' => (float) $summary['total_transfer_sore'],
        'totalTempo' => (float) $summary['total_tempo'],
        'totalTempoPagi' => (float) $summary['total_tempo_pagi'],
        'totalTempoSore' => (float) $summary['total_tempo_sore'],
        'totalShift1' => (float) $summary['total_shift1'],
        'totalShift2' => (float) $summary['total_shift2'],
    );

    echo json_encode($json_data);
}
