<?php
/**
 * Helper mysqli aman PHP 8.1+ — include setelah config.php.
 * File terpisah agar deploy tidak perlu timpa config.php server.
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

/** Output JSON untuk endpoint AJAX {hasil: html} — aman UTF-8 / encode gagal */
if (!function_exists('ajax_json_hasil')) {
  function ajax_json_hasil($html, $extra) {
    while (ob_get_level() > 0) {
      ob_end_clean();
    }
    header('Content-Type: application/json; charset=UTF-8');
    $payload = array('hasil' => $html);
    if (is_array($extra)) {
      foreach ($extra as $k => $v) {
        $payload[$k] = $v;
      }
    }
    $flags = JSON_UNESCAPED_UNICODE;
    if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
      $flags |= JSON_INVALID_UTF8_SUBSTITUTE;
    }
    $json = json_encode($payload, $flags);
    if ($json === false) {
      $payload['hasil'] = is_string($html) ? mb_convert_encoding($html, 'UTF-8', 'UTF-8') : '';
      $json = json_encode($payload, $flags);
    }
    if ($json === false) {
      $json = '{"hasil":"","error":"json_encode gagal"}';
    }
    echo $json;
    exit;
  }
}
