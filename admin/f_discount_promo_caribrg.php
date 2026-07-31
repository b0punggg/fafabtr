<?php
session_start();
include 'config.php';

// File ini dapat diakses oleh semua user termasuk operator (otoritas 1) dan administrator (otoritas 2)
// Tidak ada pembatasan berdasarkan otoritas
$kd_toko = isset($_SESSION['id_toko']) ? $_SESSION['id_toko'] : '';

// Pastikan kd_toko ada
if (empty($kd_toko)) {
  echo json_encode(array('hasil' => '<tr><td colspan="6" style="text-align: center; padding: 20px;"><i class="fa fa-exclamation-triangle"></i> Session tidak valid. Silakan login kembali.</td></tr>'));
  exit;
}

$connect = opendtcek();

$by_nama    = isset($_POST['by_nama'])   ? mysqli_real_escape_string($connect, trim($_POST['by_nama']))   : '';
$by_bagian  = isset($_POST['by_bagian']) ? mysqli_real_escape_string($connect, trim($_POST['by_bagian'])) : '';
$disc_rupiah = isset($_POST['disc_rupiah']) ? floatval($_POST['disc_rupiah']) : 0;
$disc_persen = isset($_POST['disc_persen']) ? floatval($_POST['disc_persen']) : 0;

$hasil = '';
$no = 1;

if (!empty($by_nama) || !empty($by_bagian)) {

  $where = array();
  $where[] = "mas_brg.kd_toko='$kd_toko'";

  if (!empty($by_nama)) {
    $where[] = "(mas_brg.nm_brg LIKE '%$by_nama%' OR mas_brg.kd_brg LIKE '%$by_nama%')";
  }
  if (!empty($by_bagian)) {
    // id_bag diambil dari riwayat beli_brg milik barang tsb (pola sama seperti f_setbag_cari.php)
    $where[] = "beli_brg.id_bag='$by_bagian'";
  }

  $where_sql = implode(' AND ', $where);

  $query = "SELECT mas_brg.kd_brg, mas_brg.nm_brg, mas_brg.kd_bar
            FROM mas_brg
            LEFT JOIN beli_brg ON mas_brg.kd_brg = beli_brg.kd_brg AND beli_brg.kd_toko='$kd_toko'
            WHERE $where_sql
            GROUP BY mas_brg.kd_brg
            ORDER BY mas_brg.nm_brg ASC";

  $result = mysqli_query($connect, $query);

  if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
      $kd_brg = $row['kd_brg'];
      $nm_brg = $row['nm_brg'];
      $sku = isset($row['kd_bar']) && !empty($row['kd_bar']) ? $row['kd_bar'] : $kd_brg;

      $disc_rp = $disc_rupiah > 0 ? $disc_rupiah : 0;
      $disc_pr = $disc_persen > 0 ? $disc_persen : 0;

      $hasil .= '<tr id="row_' . $no . '">';
      $hasil .= '<td>' . $no . '</td>';
      $hasil .= '<td>' . htmlspecialchars($sku) . '</td>';
      $hasil .= '<td>' . htmlspecialchars($nm_brg) . '</td>';
      $hasil .= '<td>';
      $hasil .= '<input type="hidden" name="kd_brg[]" value="' . htmlspecialchars($kd_brg) . '">';
      $hasil .= '<input type="number" name="disc_rupiah_item[]" class="form-control" value="' . number_format($disc_rp, 2, '.', '') . '" min="0" step="0.01" style="width: 120px; text-align: right;">';
      $hasil .= '</td>';
      $hasil .= '<td>';
      $hasil .= '<input type="number" name="disc_persen_item[]" class="form-control" value="' . number_format($disc_pr, 2, '.', '') . '" min="0" max="100" step="0.01" style="width: 100px; text-align: right;">';
      $hasil .= '</td>';
      $hasil .= '<td>';
      $hasil .= '<button type="button" onclick="hapusBarang(' . $no . ')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
      $hasil .= '</td>';
      $hasil .= '</tr>';

      $no++;
    }
  } else {
    $hasil = '<tr><td colspan="6" style="text-align: center; padding: 20px;"><i class="fa fa-exclamation-triangle"></i> Tidak ada barang ditemukan</td></tr>';
  }
} else {
  $hasil = '<tr><td colspan="6" style="text-align: center; padding: 20px;"><i class="fa fa-info-circle"></i> Masukkan nama brand/kategori atau pilih bagian terlebih dahulu</td></tr>';
}

mysqli_close($connect);

echo json_encode(array('hasil' => $hasil));
?>