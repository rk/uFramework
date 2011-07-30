<?php

/* HTML HELPERS --------------------------------------------------------------------------------- */

define('REQ_PATH', dirname($_SERVER['PHP_SELF']) . '/');

function absolute_url($url) {
  return REQ_PATH . urlencode($url);
}

function link_to($url, $text, $class='') {
  $uri = absolute_url($url);
  if(empty($class)) {
    return "<a href=\"$uri\">$text</a>";
  } else {
    return "<a href=\"$uri\" class=\"$class\">$text</a>";
  }
}

?>