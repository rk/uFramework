<?php

/* ROUTING -------------------------------------------------------------------------------------- */

// it's my personal naming convention to prefix routing callbacks with _r
function _r_generic_route($matches) {
  return array(
    $matches['controller'],
    either($matches['action'], 'index'),
    empty($matches['glob']) ? explode('/', ltrim('/',$matches['glob'])) : null
  );
}

// Route priority is in the reverse of the order it is registered;
// that is, the last route is tested first
Micro::add_route('', array('welcome', 'index'), 'match');
Micro::add_route('#^(?P<controller>[\w][-_\w]*)(?:/(?P<action>[\w][-_\w]*))?(?P<glob>(?:/[\w][-_\w]*)*)$#', '_r_generic_route');

/* HTML HELPERS --------------------------------------------------------------------------------- */

define('REQ_PATH', dirname($_SERVER['PHP_SELF']) . '/');

function absolute_url($url) {
  return REQ_PATH . $url;
}

function link($url, $text, $class='') {
  $uri = absolute_url($url);
  if(empty($class)) {
    return "<a href=\"$uri\">$text</a>";
  } else {
    return "<a href=\"$uri\" class=\"$class\">$text</a>";
  }
}