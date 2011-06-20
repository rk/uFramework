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
define('MICRO_PATH', dirname(__FILE__))
error_reporting(E_ALL | E_STRICT);

function __autoload($class) {
  $paths = array('includes', 'controllers');
  
  foreach($paths as $path) {
    $file = MICRO_PATH . "/application/${path}/" . strtolower($class) . '.class.php';
    
    if(file_exists($file)) {
      include $file;
    }
  }
}

function redirect($to) {
  header('Location: '.$to);
  exit(0);
}

// returns $b if $a is empty; works best when $b is a literal and is unevaluated.
function either($a,$b) {
  return empty($a) ? $b : $a;
}

class TemplateException extends Exception {}

abstract class Controller {
  /**
   * Returns the rendered template. If the template is not found, the method throws an exception.
   * If the method is called with a $layout param, that "view" is called with a $yield variable
   * containing the rendered view included in the local variables.
   * 
   * @param string $name The name of the template under /application/views/ minus the .php extension.
   * @param array $bind An associative array containing the template's local variables.
   * @param string $layout The layout view to wrap the rendered view in.
   * @return string
   * @author Robert Kosek
   **/
  protected function view($name, $bind=array(), $layout=null) {
    $file = './application/views/'.strtolower($name).'.php';
    if(file_exists($file)) {
      try {
        ob_start();
        extract($bind);
        include $file; // faster than require or _once variations
        
        $result = ob_get_contents();
        ob_end_clean();
      } catch (Exception $e) {
        throw new TemplateException("Error rendering template \"${name}.php\".", 0, $e);
      }
      
      if(isset($layout)) {
        try {
          $bind['yield'] = $result;
          return $this->view($layout, $bind);
        } catch (Exception $e) {
          throw new TemplateException("Error rendering layout \"${layout}.php\".", 0, $e);
        }
      }
      
      return $result;
    }
    
    // No valid template
    throw new TemplateException("The template \"${name}.php\" does not exist.");
    return "";
  }
}

final class Micro {
  /**** ROUTE HANDLING ****/
  static $routes = array();
  static $default_controller = 'welcome';
  private static $missing_route;

  /**
   * Adds a route to the end of the list; items are now checked in the order that they
   * were added in.
   *
   * @param string $pattern Contains either a route string with starting slash, or a regular expression.
   * @param callback $callback Contains either an array for the route (non-regex), or a function callback which takes the matches array and returns path info.
   * @param string $type Either 'regex' or 'match' for route types.
   * @author Robert Kosek
   */
  static function add_route($pattern, $callback, $type = 'regex') {
    array_push(Micro::$routes, array($type, $pattern, $callback));
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
            return $callback; // actually an array of the controller/action/params
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

    $file = MICRO_PATH . "/application/controllers/$controller.php";
    
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

foreach(glob(MICRO_PATH . '/application/includes/*.php') as $file) { include $file; }
$micro = new Micro();
$micro->dispatch(Micro::handle_route(REQUEST_URI));