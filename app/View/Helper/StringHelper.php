<?php

class StringHelper extends AppHelper {
  function format($message, $variables = array()) {
  	$replacements = array();
  	foreach($variables as $key => $value) {
      if (!is_string($value)) {
        continue;
      }
  		$replacements['!' . $key] = '<em><b>' . $value . '</b></em>';
  	}
    return strtr($message, $replacements);
  }
}