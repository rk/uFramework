<?php

/**************************************************************************************************
** µFramework                                                                                    **
** ==========                                                                                    **
**                                                                                               **
** Author:                                                                                       **
** ------                                                                                        **
**                                                                                               **
** The original author of µFramework is Robert D. Kosek (thewickedflea@gmail.com).               **
** The original repository of µFramework is: http://github.com/rk/uframework                     **
**                                                                                               **
** License:                                                                                      **
** -------                                                                                       **
**                                                                                               **
** µFramework is licensed under the terms of the CreativeCommons Attribution-Share Alike v3 US   **
** license.  What this means is that you may:                                                    **
**   # share: copy, distribute, display, perform the work, or use the code                       **
**   # remix: create derivative works                                                            **
** But you are also required to:                                                                 **
**   # Attribute µFramework to myself as specified in the section below.                         **
**   # Share your changes with others if you make any changes to the original work.              **
**   # Not change this license header without my express written permission.                     **
**                                                                                               **
** Full Summary & License: http://creativecommons.org/licenses/by-sa/3.0/us/                     **
**                                                                                               **
** Alternative licensing available by request. (Email: thewickedflea@gmail.com)                  **
**                                                                                               **
** Attribution:                                                                                  **
** -----------                                                                                   **
**                                                                                               **
** To satisfy the terms of the above license, you must state somewhere in either your            **
** documentation, readme, website, or derivative work a "powered by" or "built on" notice that   **
** refers to µFramework as my property and provides the latest web address to the project's      **
** website.                                                                                      **
**                                                                                               **
** This attribution needn't be conspicuous or in large, bold letters.  But try to make it able   **
** to be found and read.  After all, you're using my pet project and I'd love to know.           **
***************************************************************************************************/

define('uFRAMEWORK', '0.3.5');

class Input {
  private $_post = null;
  public $request_url = null;
  public $segments = array();
  public $method;
  private static $instance = null;
  
  private function __construct() {
    // The following line does:
    //   1. forces all slashes to the Unix-style
    //   2. splits the file called on index.php;
    //   3. takes the last item from the array;
    //   4. and then trims any outside slashes.
    $uri = trim(array_pop(explode('index.php', str_replace("\\", '/', $_SERVER['REQUEST_URI']))), '/');
    $this->request_url = $uri;
    if(isset($uri[0])) {
      $this->segments = explode('/', $uri);
    }
    
    $this->_post = $_POST;
    unset($_POST);
    
    // Sets access method, $this->method, to one of: get/post/put/delete
    $this->method = 'get';
    if(!empty($this->_post)) {
      $this->method = 'post';
      if($this->post('_method') == 'put' || $this->post('_method') == 'delete') {
        $this->method = $this->_post['_method'];
      }
    }
  }
  
  static function getInstance() {
    if(!self::$instance) { self::$instance =& new Input(); }
    return self::$instance;
  }
  
  function post($name) {
    return isset($this->_post[$name]) ? $this->_post[$name] : null;
  }
  
  function uri($number) {
    return isset($this->segments[$number]) ? $this->segments[$number] : null;
  }
  
  function debug_post() {
    print_r($this->_post);
  }
}

class Output {
  private static $instance = null;
  static function getInstance() {
    if(!self::$instance) { self::$instance =& new Output(); }
    return self::$instance;
  }
  
  // declared for the sake of privacy
  private function __construct($isStillDressing = TRUE) {}
  
  public $template = 'template';
  function finalize($variables = array()) {
    $variables['elapsed_time'] = sprintf('%0.6F', microtime(true) - START_TIME);
    echo $this->view($this->template, $variables);
  }
  
  public function view($name, $bind=array()) {
    ob_start();
    if(count($bind) > 0) { extract($bind); }
    include 'views/'.strtolower($name).'.php';
    return ob_get_clean();
  }
}

// Cache Controller...
abstract class Cache {
  const path = 'cache/';
  
  // No smart remarks about premature optimizations please...
  private static $filenames = array();
  private static function filename($id) {
    if(empty(self::$filenames[$id])) {
      self::$filenames[$id] = Cache::path . sprintf('%u', crc32($id));
    }
    return self::$filenames[$id];
  }
  
  /* This isn't premature!  I am caching the relevancy to permit checking if a section must
     be regenerated prior to output within the controller, so that when the view checks it
     gets the same result as the controller did.  (And with multiple sections per-page being
     possible, that means it must be stored in an array.) */
  private static $relevance = array();
  static function is_relevant($id, $minutes) {
    if(empty(self::$relevance[$id])) {
      self::$relevance[$id] = time() - filemtime(self::filename($id)) <= $time * 60;
    }
    return self::$relevance[$id];
  }
  
  static function start()  { ob_start(); }
  static function end($id, $text = null) {
    if($text === null) {
      
    }
    $filename = sprintf('%s%u', Cache::path, Cache::filename($id));
    file_put_contents($filename, $text);
  }
  static function cached($id, $minutes = 5) {
    if(!self::is_relevant($id, $minutes)) {
      return true;
    } else {
      echo file_get_contents(self::filename($id));
      return false;
    }
  }
}

// Session Object...
class Session {
  private static $instance = null;
  static function getInstance() {
    if(!self::$instance) { self::$instance =& new Session(); }
    return self::$instance;
  }
  private function __construct() {}
  
  private static $started = false;
  static function hasSession() {
    global $config;
    if(self::$started) {
      return true;
    } elseif(isset($_COOKIE[$config['sess_cookie']])) {
      return Session::start(); // if we have a session try starting it and checking its security (and return that)
    }
    return false;
  }
  
  static function destroy() {
    global $config;
    session_destroy();
    unset($_COOKIE[$config['sess_cookie']]);
    self::$started = false;
  }
  
  static function start() {
  global $config;
    $new = empty($_COOKIE[$config['sess_cookie']]);
    if(!self::$started) {
      session_start();
      self::$started = true;
      if($new) {
        $_SESSION['IP']    = $_SERVER['REMOTE_ADDR'];
        $_SESSION['AGENT'] = $_SERVER['HTTP_USER_AGENT'];
      } elseif($_SESSION['IP'] != $_SERVER['REMOTE_ADDR'] || $_SESSION['AGENT'] != $_SERVER['HTTP_USER_AGENT']) {
        Session::destroy();
        self::$started = false;
        return false;
      }
    }
    return true;
  }
  
  function __get($name) { return isset($_SESSION[$name]) ? $_SESSION[$name] : ''; }
  function __set($k,$v)  { $_SESSION[$k] = $v; }
  function __isset($n)  { return isset($_SESSION[$n]); }
  function __unset($n)  { unset($_SESSION[$n]); }
}

class Controller {
  public $db;
  public $input = null;
  public $output = null;
  public $session = null;
  
  function __construct() {
    $this->input   = Input::getInstance();
    $this->output  = Output::getInstance();
    $this->session = Session::getInstance();
    global $config;
    if(isset($config['autoload_db']) && $config['autoload_db'] == true && function_exists('load_database')) {
      $this->db = load_database();
    }
    if(method_exists($this, '_setup')) {
      $this->_setup();
    }
  }
}

// The routing class cannot, should not, be instantiated and is used
// as a global lookup class for routing. Currently supports only static
// routes, and no "controller/method/:named_param" methods.
abstract class Routing {
  static private $routes = array();
  static function addRoute($path, $controller, $action) {
    self::$routes[$path] = array(
      'controller' => $controller,
      'action'     => $action
    );
  }
  
  // This is specifically for matching the first two, or three depending on
  // logic, segments and ensuring that they are a valid route.  Otherwise an
  // error must be thrown in the caller.
  static private function is_secure_name($segment) {
    // matches a route that begins with an alphanumeric character, and further
    // characters are only alphanumeric, underscores, or dashes.
    return preg_match('#^([\w][\-_\w]+)$#', $segment) == 1;
  }
  
  // To prevent controller manipulations, etc.
  static private function is_secure_path($path) {
    return preg_match('#^controllers(\/[a-z][a-z_-]+)+.php$#i', $path) == 1;
  }

  // Public for testing purposes...
  static function recognizeRoute() {
    $i =& Input::getInstance();
    
    $params = null;
    $uri_count = count($i->segments);
    
    // TODO: Lots, and lots, of reliability testing!
    if(isset($i->request_url[1]) && !empty(self::$routes[$i->request_url])) {
      // if we have a key, and therefore a specific route, return it
      $class  = self::$routes[$i->request_url]['controller'];
      $method = self::$routes[$i->request_url]['action'];
      $controller = "controllers/$class.php";
      $class      = ucfirst($class);
    } elseif($uri_count == 0) {
      $class      = self::$routes['*default']['controller'];
      $method     = self::$routes['*default']['action'];
      $controller = "controllers/$class.php";
      $class      = ucfirst($class);
    } elseif($uri_count == 1) {
      $controller = 'controllers/'.$i->uri(0).'.php';
      $class      = ucfirst($i->uri(0));
      $method     = 'index';
    } else {
      $u1 = strtolower($i->uri(0));
      $u2 = strtolower($i->uri(1));
      $u3 = strtolower($i->uri(2) or 'index');
      if(is_dir("controllers/$u1") && is_file("controllers/$u1/$u2.php")) {
        $controller = "controllers/$u1/$u2.php";
        $class      = ucfirst($u2);
        $method     = $u3;
        if($uri_count > 3) {
          $params   = array_slice($i->segments, 3, $uri_count - 3);
        }
      } else {
        $controller = "controllers/$u1.php";
        $class      = ucfirst($u1);
        $method     = $u2;
        if($uri_count > 2) {
          $params   = array_slice($i->segments, 2, $uri_count - 2);
        }
      }
    }
    
    if(self::is_secure_path($controller) && self::is_secure_name($class) && self::is_secure_name($method)) {
      return array($controller, $class, $method, $params);
    } else {
      die('Attempted security breach!');
    }
  }
  
  static function dispatch() {
    list($controller, $class, $method, $params) = self::recognizeRoute();
    self::execute($controller, $class, $method, $params);
  }
  
  private static function execute($file, $class, $method, $params = null) {
    require $file;
    $controller = new $class();
    
    if(is_array($params)) {
      call_user_func_array(array($controller, $method), $params);
    } else {
      $controller->$method();
    }
    
    return $controller;
  }
}

function redirect($to) {
  header('Location: '.$to);
  exit(0);
}

require './application/config.php';

session_name($config['sess_cookie']);

Routing::dispatch();