<?php

/*
  uFramework
  ==========

  uFramework was written by Robert Kosek, and is available free online at
    http://github.com/rk/uframework/tree/master

  LICENSE
  -------

  uFramework is licensed under the CreativeCommons Attribution-Share Alike v3 US license.

  You may:  share, derive-from, use the code commercially.
  You must: attribute this framework to me, share all changes to the framework,
    not tamper with this header.

  For the full summary & license see: http://creativecommons.org/licenses/by-sa/3.0/us/

  For alternative licensing write to: thewickedflea [AT] gmail (DOT) com
*/

define('START', microtime(true));
define('MICRO', '1.0RC');
define('REQUEST_URI', isset($_GET['p']) ? $_GET['p'] : '');
error_reporting(E_ALL | E_STRICT);

function redirect($to) {
  header('Location: '.$to);
  exit(0);
}

// returns $b if $a is empty; works best when $b is a literal and is unevaluated.
function either($a,$b) {
  return empty($a) ? $b : $a;
}

abstract class Controller {
  protected function view($name, $bind=array()) {
    ob_start();
    $base = './application/views/'.strtolower($name);
    foreach(array('.php', '.html', '.htm', '.txt') as $ext) {
      if(file_exists($base.$ext)) {
        if($ext == '.php') {
          extract($bind);
          include $base.$ext;
        } else {
          readfile($base.$ext);
        }
        return ob_get_clean();
      }
    }
    ob_end_flush();
    return "<p><b>Missing Template:</b> application/views/{$name}</p>";
  }
}

final class Micro {
  /**** ROUTE HANDLING ****/
  static $routes = array();
  static $default_controller = 'welcome';
  private static $missing_route;

  static function add_route($pattern, $callback, $type = 'regex') {
    array_unshift(Micro::$routes, array($type, $pattern, $callback));
  }

  static function handle_route($uri) {
    $route_data = Micro::$missing_route;

    foreach(Micro::$routes as $group) {
      list($type, $pattern, $callback) = $group;

      switch($type) {
        case 'regex':
          if(preg_match($pattern, $uri, $matches)) {
            return call_user_func($callback, $matches);
          }
          break;
        case 'match':
          if(strcasecmp($uri, $pattern) == 0) {
            return $callback; // array of the controller/action/params
          }
          break;
      }
    }
    return $route_data;
  }

  // Dispatcher, call for internal redirect; params is optional
  public function dispatch($info) {
    $params = null;
    if(isset($info[2])) {
      list($controller, $action, $params) = $info;
    } else {
      list($controller, $action) = $info;
    }
    $class = ucfirst($controller);

    $file = "./application/controllers/$controller.php";
    if(is_file($file)) {
      if(class_exists($class) == false) { include $file; }
      $control = new $class($this);
      if(!method_exists($control, $action)) { return $this->dispatch(Micro::$missing_route); }
      $control->$action($params);
      return $control;
    } else {
      if($info === Micro::$missing_route) { exit(0); }
      return $this->dispatch(Micro::$missing_route);
    }
  }

  /**** MAGIC STUFF ****/
  function __construct() {
    $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    Micro::$missing_route = array(Micro::$default_controller, 'missing', array(REQUEST_URI, $referrer));
  }
}

foreach(glob('./application/includes/*.php') as $file) { include $file; }
$micro = new Micro();
$micro->dispatch(Micro::handle_route(REQUEST_URI));