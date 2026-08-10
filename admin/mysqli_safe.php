<?php
/**
 * Helper mysqli aman PHP 8.1+ — require setelah config.php:
 * require_once __DIR__ . '/mysqli_safe.php';
 */
if (!function_exists('mysqli_is_result')) {
  function mysqli_is_result($result) {
    return $result instanceof mysqli_result;
  }
}
if (!function_exists('mysqli_count_rows')) {
  function mysqli_count_rows($result) {
    return mysqli_is_result($result) ? mysqli_num_rows($result) : 0;
  }
}
if (!function_exists('mysqli_fetch_count_row')) {
  function mysqli_fetch_count_row($result, $default_key, $default_val) {
    if (!mysqli_is_result($result)) {
      return array($default_key => $default_val);
    }
    $row = mysqli_fetch_assoc($result);
    if (!$row || !isset($row[$default_key])) {
      return array($default_key => $default_val);
    }
    return $row;
  }
}
if (!function_exists('pos_json_flags')) {
  function pos_json_flags() {
    $flags = JSON_UNESCAPED_UNICODE;
    if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
      $flags |= JSON_INVALID_UTF8_SUBSTITUTE;
    }
    return $flags;
  }
}
if (!function_exists('ajax_json_hasil')) {
  function ajax_json_hasil($html, $extra) {
    while (ob_get_level() > 0) {
      ob_end_clean();
    }
    if (!headers_sent()) {
      header('Content-Type: application/json; charset=UTF-8');
    }
    $payload = array('hasil' => is_string($html) ? $html : '');
    if (is_array($extra)) {
      foreach ($extra as $k => $v) {
        $payload[$k] = $v;
      }
    }
    $flags = pos_json_flags();
    $json = json_encode($payload, $flags);
    if ($json === false && is_array($extra)) {
      unset($payload['cartPrint']);
      $json = json_encode($payload, $flags);
    }
    if ($json === false) {
      $payload = array('hasil' => '', 'error' => 'json_encode gagal');
      $json = json_encode($payload, $flags);
    }
    if ($json === false) {
      $json = '{"hasil":"","error":"json_encode gagal"}';
    }
    echo $json;
    exit;
  }
}