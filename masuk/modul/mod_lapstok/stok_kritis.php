<?php
session_start();
if (empty($_SESSION['username']) and empty($_SESSION['passuser'])) {
	echo "<link href=../css/style.css rel=stylesheet type=text/css>";
	echo "<div class='error msg'>Untuk mengakses Modul anda harus login.</div>";
} else {

	switch ($_GET['act']) {
			// Menu utama
		default:
?>
			<div class="box box-primary box-solid">
				<div class="box-header with-border">
					<h3 class="box-title">STOK KRITIS</h3>
					<div class="box-tools pull-right">
						<button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
					</div><!-- /.box-tools -->
				</div>
				<div class="box-body table-responsive">
					<CENTER><h2><strong>STOK KRITIS</strong></h2></CENTER><br>
					<div class="col-sm-12" style="text-align:center">
						Analisa estimasi pengadaan barang berdasarkan frekuensi transaksi 30 hari terakhir <br>
						<a class='btn btn-primary' href='?module=stok_kritis&act=kritis30'>PROSES ANALISA DATA</a><br><br>
						<a class='btn btn-success' href='?module=stok_kritis&act=tampil30'>TAMPILKAN ESTIMASI STOK KRITIS</a><br><br>
						<a class='btn btn-warning' href='?module=stok_kritis&act=over30'>TAMPILKAN OVERSTOK</a>
					</div>
				</div>
			</div>
<?php
			break;

		case "kritis30":
			include "../../../configurasi/koneksi.php";

			$tgl_awal = date('Y-m-d');
			$tgl_akhir = date('Y-m-d', strtotime('-30 days', strtotime($tgl_awal)));
			$tgl_awal_esc = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $tgl_awal);
			$tgl_akhir_esc = mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $tgl_akhir);

			// Satu query untuk menghitung ulang t30 (jumlah transaksi) dan q30 (jumlah qty terjual)
			// 30 hari terakhir, untuk SEMUA barang sekaligus - bukan per baris seperti sebelumnya.
			mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE barang b
				LEFT JOIN (
					SELECT td.kd_barang,
						   COUNT(*) AS t30,
						   COALESCE(SUM(td.qty_dtrkasir), 0) AS q30
					FROM trkasir_detail td
					JOIN trkasir t ON t.kd_trkasir = td.kd_trkasir
					WHERE t.tgl_trkasir BETWEEN '$tgl_akhir_esc' AND '$tgl_awal_esc'
					GROUP BY td.kd_barang
				) s ON s.kd_barang = b.kd_barang
				SET b.t30 = COALESCE(s.t30, 0),
					b.q30 = COALESCE(s.q30, 0)");

			echo '<script>window.location.href = "?module=stok_kritis&act=tampil30";</script>';
			break;

		case "tampil30":
			include "../../../configurasi/koneksi.php";
?>
			<div class="box-body table-responsive">
				<form method="post" action="modul/mod_lapstok/aksi_simpan_orders.php" id="frmAddOrder">
					<span class="btn btn-primary">LAKU</span> Jumlah Transaksi > 10 per 30 hari
					<span class="btn btn-success">LANCAR</span> Jumlah Transaksi 6 - 10 per 30 hari
					<span class="btn btn-warning">SLOW</span> Jumlah Transaksi 1 - 5 per 30 hari
					<hr>

					<button class='btn btn-success btn-flat' type='submit' style="margin-bottom: 10px">ADD TO ORDER</button>
					<a class='btn btn-default btn-flat' href='?module=stok_kritis'>KEMBALI</a>
					<hr>

					<table id="example1" class="table table-bordered table-striped table-responsive">
						<thead>
							<tr style="text-align:center;">
								<th>No</th>
								<th>Kategori</th>
								<th>Nama Barang</th>
								<th style="text-align: right; ">Qty/Stok</th>
								<th style="text-align: right; ">T30</th>
								<th style="text-align: center; ">Q30</th>
								<th style="text-align: right; ">Satuan</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$hasil30 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_barang, kd_barang, nm_barang, stok_barang, sat_barang, t30, q30
								FROM barang
								WHERE t30 > 0 AND stok_barang <= (0.25 * t30)
								ORDER BY nm_barang ASC");
							$no = 1;
							while ($tp30 = mysqli_fetch_assoc($hasil30)) {
								echo "
								<tr>
									<td><input type='checkbox' name='check[]' value='$tp30[kd_barang]'> $no</td>";
								if ($tp30['t30'] <= 0) {
									echo " <td style='background-color:#ff003f;' align='center'><strong>MACET</strong></td> ";
								} elseif ($tp30['t30'] > 0 && $tp30['t30'] <= 5) {
									echo "  <td style='background-color:#FFA500;' align='center'><strong>SLOW</strong></td>";
								} elseif ($tp30['t30'] > 5 && $tp30['t30'] <= 10) {
									echo "  <td style='background-color:#00ff3f;' align='center'><strong>LANCAR</strong></td>";
								} else {
									echo "  <td style='background-color:#00bfff;' align='center'><strong>LAKU</strong></td>";
								}
								echo "
									<td>$tp30[nm_barang]</td>
									<td>$tp30[stok_barang]</td>
									<td style='text-align:center;'>$tp30[t30]</td>
									<td style='text-align:center;'>$tp30[q30]</td>
									<td style='text-align:center;'>$tp30[sat_barang]</td>
								</tr>";
								$no++;
							}
							?>
						</tbody>
					</table>
				</form>
			</div>
<?php
			break;

		case "over30":
			include "../../../configurasi/koneksi.php";
?>
			<div class="box-body table-responsive">
				<a class='btn btn-default btn-flat' href='?module=stok_kritis'>KEMBALI</a>
				<hr>
				<table id="example1" class="table table-bordered table-striped table-responsive">
					<thead>
						<tr style="text-align:center;">
							<th>No</th>
							<th>Kategori</th>
							<th>Nama Barang</th>
							<th style="text-align: right; ">Qty/Stok</th>
							<th style="text-align: right; ">T30</th>
							<th style="text-align: center; ">Q30</th>
							<th style="text-align: center; ">On-&gt;T</th>
							<th style="text-align: right; ">Satuan</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$hasil30 = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id_barang, kd_barang, nm_barang, stok_barang, sat_barang, t30, q30
							FROM barang
							WHERE t30 >= 0 AND stok_barang > (2 * q30)
							ORDER BY nm_barang ASC");
						$no = 1;
						while ($tp30 = mysqli_fetch_assoc($hasil30)) {
							$OnT = $tp30['stok_barang'] - $tp30['t30'];
							echo "
							<tr>
								<td>$no</td>";
							if ($tp30['t30'] <= 0) {
								echo " <td style='background-color:#ff003f;' align='center'><strong>MACET</strong></td> ";
							} elseif ($tp30['t30'] > 0 && $tp30['t30'] <= 5) {
								echo "  <td style='background-color:#EDFF00;' align='center'><strong>SLOW</strong></td>";
							} elseif ($tp30['t30'] > 5 && $tp30['t30'] <= 10) {
								echo "  <td style='background-color:#00ff3f;' align='center'><strong>LANCAR</strong></td>";
							} else {
								echo "  <td style='background-color:#00bfff;' align='center'><strong>LAKU</strong></td>";
							}
							echo "
								<td>$tp30[nm_barang]</td>
								<td>$tp30[stok_barang]</td>
								<td>$tp30[t30]</td>
								<td>$tp30[q30]</td>
								<td>$OnT</td>
								<td>$tp30[sat_barang]</td>
							</tr>";
							$no++;
						}
						?>
					</tbody>
				</table>
			</div>
<?php
			break;
	}
}
?>
