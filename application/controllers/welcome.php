<?php

class Welcome extends Controller {
  function index() {
    echo $this->view('template', array(
      'body' => "<p>Welcome to &micro;Framework, you can find the requisite files in:</p>\n<ul><li><code>application/controllers/*.php</code></li><li><code>application/includes/*.php</code></li><li><code>application/views/*.php</code></li></ul><p>This is the basic setup, everything else is up to you.</p>"
    ));
  }
}