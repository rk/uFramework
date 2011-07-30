<?php

/*
  uFramework
  ==========

  uFramework was written by Robert Kosek, and is available free online at
    https://github.com/rk/uframework/tree/master

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
define('MICRO', '1.0RC3');
define('REQUEST_URI', isset($_GET['p']) ? $_GET['p'] : '');
define('MICRO_PATH', dirname(__FILE__));

ini_set("display_errors", 1);
error_reporting(E_ALL | E_STRICT);

function __autoload($class) {
  $file = MICRO_PATH . "/application/includes/" . strtolower($class) . '.class.php';
  
  if(file_exists($file)) {
    include $file;
  }
}

/**
 * This function redirects the user's browser to the first parameter (must be a valid address),
 * with a status code of the second parameter (defaults to 303). Supports status codes 301-307,
 * see the HTTP 1.1 standard for documentation.
 *
 * @param string $to 
 * @param integer $code 
 * @return void
 * @author Robert Kosek
 */
function redirect($to, $code=303) {
  $statuses = array(
    // Cached client-side; always redirects to the target URL.
    301 => 'HTTP/1.1 301 Moved Permanently',
    // Most common redirect code, but it can be cached and forces resubmission of forms.
    302 => 'HTTP/1.1 302 Found',
    // This lets us redirect w/o caching the page, and no form resubmits!
    303 => 'HTTP/1.1 303 See Other',
    // Outputs no body (sorta like die but w/headers only).
    304 => 'HTTP/1.1 304 Not Modified',
    // Redirects to a proxy that must be used to access the redirecting page.
    305 => 'HTTP/1.1 305 Use Proxy',
    // This may or may not be cached depending on Cache-Control / Expires; may alert user
    // for POST requests.
    307 => 'HTTP/1.1 307 Temporary Redirect'
  );
  
  header($statuses[$code]);
  
  // the Not Modified response shouldn't output anything.
  if($code == 304) exit(0);
  
  header('Location: ' . $to);
  exit(0);
}

/**
 * This helper function immediately halts the output and returns a 304 Not Modified status code
 * to the browser.
 *
 * @return void
 * @author Robert Kosek
 **/
function not_modified() {
  redirect(null, 304);
}

class TemplateException extends Exception {
  
  // PHP 5.2 Compatibility
  public function __construct($message, $code, $prev=null) {
    parent::__construct($message, $code);
    if(isset($prev)) { $this->previous = $prev; }
  }
  
  public function __toString() {
    return <<<HTML
<p>
  <strong>Template Exception</strong><br/>
  {$this->message}
</p>
<p>
  <strong>Child Exception</strong><br/>
  {$this->prev}
</p>
HTML;
  }
}

class FourOhFourException extends Exception {
  public function __construct($message, $uri) {
    parent::__construct($message);
    $this->uri = $uri;
    $this->code = 404;
  }
  
  public function __toString() {
    return "<p><strong>{$this->message}</strong><br/>Couldn't find /{$this->uri}</p>";
  }
}

class RoutingException extends FourOhFourException {
  public $controller;
  public $action;
  public $parameters;
  
  public function __construct($message, $routeData) {
    parent::__construct($message, REQUEST_URI);
    
    $this->controller = ucfirst($routeData[0]);
    $this->action = ucfirst($routeData[1]);
    $this->parameters = isset($routeData[2]) ? $routeData[2] : array();
  }
  
  public function __toString() {
    $params = '"' . join('", "', $this->parameters) . '"';
    return <<<HTML
<p>
  <strong>{$this->message}</strong><br/>
  Controller: <code>{$this->controller}</code><br/>
  Action: <code>{$this->action}</code><br/>
  Parameters: <code>{$params}</code>
</p>
HTML;
  }
}

abstract class Controller {
  private static $loaded_modules = array();
  
  /**
   * Pass in names of modules under application/modules, and the file 'module.php' will be
   * included. This file must include all other files required by the library/package/module.
   * Modules will only be required once, and can be safely used in a customized controller
   * defined within the includes directory and inherited from.
   *
   * @return boolean
   * @author Robert Kosek
   **/
  public function require_modules() {
    foreach(get_func_args() as $name) {
      if(empty(self::$loaded_modules[$name])) {
        self::$loaded_modules[$name] = (require MICRO_PATH . "/application/modules/{$name}/module.php");
      }
    }
  }
  
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
    // $route_data = Micro::$missing_route;
    $route_data = null;

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
    
    if($route_data === null) { throw new FourOhFourException("404: Unknown Route", $uri); }
    
    return $route_data;
  }

  // Dispatcher, call for internal redirect; params is optional
  public function dispatch($info) {
    if(count($info) === 3) {
      list($controller, $action, $params) = $info;
    } elseif(count($info)) {
      $params = null;
      list($controller, $action) = $info;
    }
    
    $class = ucfirst($controller);

    $file = MICRO_PATH . "/application/controllers/{$controller}.php";
    
    if(is_file($file) && class_exists($class) == false) {
      include $file;

      if(!method_exists($class, $action)) {
        throw new RoutingException("404: Unknown Action", $info);
      }
      
      $control = new $class();
      // Pass the params as an array so we don't hit an "expected parameter" error from the
      // method we're calling if we use call_user_func_array().
      $control->$action($params);
      
      return $control;
    }
    
    throw new RoutingException("404: Unknown Controller", $info);
  }

  /**** MAGIC STUFF ****/
  function __construct() {
    // $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    // Micro::$missing_route = array(Micro::$default_controller, 'missing', array(REQUEST_URI, $referrer));
  }
}

include MICRO_PATH . '/application/includes/config.php';
include MICRO_PATH . '/application/includes/helpers.php';

try {
  $micro = new Micro();
  $controller = $micro->dispatch(Micro::handle_route(REQUEST_URI));
} catch (Exception $e) {
  // a very rudimentary error page...
  echo $e;
}

?>