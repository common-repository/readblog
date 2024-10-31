<?php

/**
 * Posts API class
 */
class readBlog_Admin {
  
  private static $instance;
  function __construct() {
    $this->ajax = readBlog_Ajax::get_instance();
    add_action( 'init', array( $this, 'readblog_add_rewrites' ) );
  }

  function readblog_add_rewrites(){
	  $urlFinalAdmin = str_replace(home_url()."/","",admin_url('admin-ajax.php?readblogtoken=$1&action=readblog_$2&id=$3&next=$4'));
    add_rewrite_rule( 'readblog/([^/]*)/([^/]*)/([^/]*)/([^/]*)/?', $urlFinalAdmin, 'top');
  }
  
  public static function get_instance(){
    if( null === self::$instance ){
      self::$instance = new readBlog_Admin();
    }
    return self::$instance;
  }
}

?>