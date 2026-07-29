<?php
/**
 * Panggil setelah include config.php pada endpoint AJAX penjualan.
 */
if (!function_exists('pos_require_mysqli_safe')) {
  function pos_require_mysqli_safe() {
    $path = __DIR__ . '/mysqli_safe.php';
    if (!is_readable($path)) {
      while (ob_get_level() > 0) {
        ob_end_clean();
      }
      if (!headers_sent()) {
        header('Content-Type: application/json; charset=UTF-8');
      }
      echo json_encode(array(
        'hasil' => '',
        'error' => 'File mysqli_safe.php tidak ada di folder admin. Upload mysqli_safe.php.',
      ), JSON_UNESCAPED_UNICODE);
      exit;
    }
    require_once $path;
  }
}
