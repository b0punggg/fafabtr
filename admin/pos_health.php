<?php
/**
 * Diagnostik POS — buka via browser setelah deploy, hapus jika sudah tidak perlu.
 * Contoh: https://tokofafa.dhe51.id/admin/pos_health.php
 */
header('Content-Type: text/html; charset=UTF-8');
$checks = array();

function pos_health_row($label, $ok, $detail = '') {
  global $checks;
  $checks[] = array('label' => $label, 'ok' => $ok, 'detail' => $detail);
}

pos_health_row('PHP version', version_compare(PHP_VERSION, '8.0.0', '>='), PHP_VERSION);

$ms_path = __DIR__ . '/mysqli_safe.php';
pos_health_row('mysqli_safe.php ada', is_readable($ms_path), $ms_path);

$config_path = __DIR__ . '/config.php';
pos_health_row('config.php ada', is_readable($config_path), $config_path);

if (is_readable($config_path)) {
  include $config_path;
}

if (is_readable($ms_path)) {
  require_once $ms_path;
}

$funcs = array('mysqli_is_result', 'mysqli_count_rows', 'mysqli_fetch_count_row', 'ajax_json_hasil', 'opendtcek');
foreach ($funcs as $fn) {
  pos_health_row("fungsi $fn()", function_exists($fn));
}

pos_health_row('ext json', extension_loaded('json'));
pos_health_row('ext mysqli', extension_loaded('mysqli'));

$connect = function_exists('opendtcek') ? @opendtcek() : false;
pos_health_row('koneksi database', $connect instanceof mysqli, $connect ? 'OK' : (function_exists('opendtcek') ? 'opendtcek() gagal' : 'opendtcek tidak ada'));

$tables = array('dum_jual', 'pelanggan', 'seting', 'paket_mas', 'beli_brg', 'mas_brg');
if ($connect) {
  foreach ($tables as $tbl) {
    $q = @mysqli_query($connect, "SHOW TABLES LIKE '$tbl'");
    pos_health_row("tabel $tbl", mysqli_is_result($q) && mysqli_num_rows($q) > 0);
  }

  $cols = array('discrp', 'id_bag', 'discvo', 'panding');
  foreach ($cols as $col) {
    $q = @mysqli_query($connect, "SHOW COLUMNS FROM dum_jual LIKE '$col'");
    pos_health_row("kolom dum_jual.$col", mysqli_is_result($q) && mysqli_num_rows($q) > 0);
  }

  $q = @mysqli_query($connect, "SELECT COUNT(*) AS c FROM dum_jual LIMIT 1");
  if (mysqli_is_result($q)) {
    $row = mysqli_fetch_assoc($q);
    pos_health_row('query dum_jual', true, 'rows sample OK, count=' . (isset($row['c']) ? $row['c'] : '?'));
  } else {
    pos_health_row('query dum_jual', false, mysqli_error($connect));
  }
}

$ajax_files = array(
  'f_jualcari.php', 'f_jual_caribrg.php', 'f_jualcaribrg.php',
  'f_jual_carisat.php', 'f_jual_listpaket.php', 'f_jualcaripanding.php',
  'mysqli_safe.php', 'pos_ajax_bootstrap.php',
);
foreach ($ajax_files as $f) {
  pos_health_row("file $f", is_readable(__DIR__ . '/' . $f));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>POS Health Check</title>
  <style>
    body { font-family: sans-serif; margin: 24px; background: #f5f5f5; }
    h1 { font-size: 1.25rem; }
    table { border-collapse: collapse; background: #fff; width: 100%; max-width: 720px; }
    th, td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; font-size: 14px; }
    th { background: #333; color: #fff; }
    .ok { color: #0a0; font-weight: bold; }
    .fail { color: #c00; font-weight: bold; }
    .note { margin-top: 16px; font-size: 13px; color: #555; max-width: 720px; }
  </style>
</head>
<body>
  <h1>POS Health Check</h1>
  <p>Server: <?= htmlspecialchars(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost', ENT_QUOTES, 'UTF-8') ?> · <?= date('Y-m-d H:i:s') ?></p>
  <table>
    <tr><th>Check</th><th>Status</th><th>Detail</th></tr>
    <?php foreach ($checks as $c): ?>
    <tr>
      <td><?= htmlspecialchars($c['label'], ENT_QUOTES, 'UTF-8') ?></td>
      <td class="<?= $c['ok'] ? 'ok' : 'fail' ?>"><?= $c['ok'] ? 'OK' : 'GAGAL' ?></td>
      <td><?= htmlspecialchars($c['detail'], ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <p class="note">
    Jika ada baris GAGAL, perbaiki dulu sebelum uji penjualan.<br>
    Upload minimal: <code>mysqli_safe.php</code>, <code>pos_ajax_bootstrap.php</code>, dan semua <code>f_jual*.php</code> yang diperbaiki.<br>
    Hapus file ini setelah masalah selesai (keamanan).
  </p>
</body>
</html>
