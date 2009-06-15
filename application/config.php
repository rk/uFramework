<?php

// set to true to autoload the database on-create with the below function
$config['autoload_db'] = FALSE;

function load_database() {
  // customize this function with your parameters/PDO, etc.
  $config = array('host' => 'localhost', 'user' => 'root', 'pass' => '', 'name' => '');
  return new mysqli($config['host'], $config['user'], $config['pass'], $config['name']);
}

// the name of the cookie, keep it short and mixed case.
$config['sess_cookie'] = 'doNOTkeepTh1S';

// This needs to be the absolute path to your website w/o a trailing slash
define('ROOT_URL', 'http://example.com/wherever');

// Static Routes:
//-----------------------------------------------------------------------------

Routing::addRoute('*default', 'welcome', 'index');

//uncomment the following line to associate "/about" with welcome->about()
//Routing::addRoute('about', 'welcome', 'about');