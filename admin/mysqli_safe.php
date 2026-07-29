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
