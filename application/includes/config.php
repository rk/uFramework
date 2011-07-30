<?php

/* CONFIG --------------------------------------------------------------------------------------- */

$config = array(
  'sess_cookie' => 'micro'
);

/* ROUTING -------------------------------------------------------------------------------------- */

// it's my personal naming convention to prefix routing callbacks with _r
function _r_generic_route($matches) {
  $result = array($matches['controller'], isset($matches['action']) ? $matches['action'] : 'index');
  
  // The third element (parameters), if it exists, must always be an array.
  if(isset($matches['glob'])) {
    if(strpos($matches['glob'], '/') === false) {
      // singular parameter
      $result[] = array(urldecode($matches['glob']));
    } else {
      // multiple parameters
      $result[] = array_map('urldecode', explode('/', $matches['glob']));
    }
  }
  
  return $result;
}

// Route priority is in the reverse of the order it is registered;
// that is, the last route is tested first
Micro::add_route('', array('welcome', 'index'), 'match');
Micro::add_route('#^(?P<controller>[a-z][-_a-z]*)(?:/(?P<action>[a-z][-_a-z]*)/?(?P<glob>[^\#]*))?$#i', '_r_generic_route');

?>