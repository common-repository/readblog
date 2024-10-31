<?php
class readBlog_Ajax {
  
  private static $instance;

  function __construct() { 
	 	$actions = array(
			'readblog_get_posts',
			'readblog_get_post',
			'readblog_get_categories',
			'readblog_get_home',
			'readblog_get_comments',
			'readblog_data',
			'readblog_savepush',
			'readblog_deltoken'
		);
    	foreach( $actions as $action ){
      		add_action( 'wp_ajax_nopriv_'.$action, array( $this, $action ) );
			add_action( 'wp_ajax_'.$action, array( $this, $action ) );
    	}
  }
  /******
  Devuelve loa datos básicos para el funcionamiento de la app
  ******/
  function readblog_data() {
	  	if(!$this->readblog_checksecure()) {
      		exit;
    	}
	  
	  	$name = get_bloginfo('name');
	  	$res = array(
			'name' => $name,
			'color' => get_option('readblog_colorTheme'),
			'categorias' => get_option('readblog_categoriasShow'),
			'publi' => get_option('readblog_googlepubli'),
			'analiticas' => get_option('readblog_googleanaliticas')
			
		);
		wp_send_json($res);
	  
  }
  /********
  Devuelve los post para mostrar la home
  *******/
	function readblog_get_home(){
		if(!$this->readblog_checksecure()) {
      				exit;
    			}
		$elementos = 5;
		$yaCargados = $_GET['next'];
		$args = array(
			'posts_per_page'	=> $elementos,
			'offset'           => $yaCargados,
			'category'			=> explode(",",get_option('readblog_categoriasShow')),
			'orderby'          => 'post_date',	
			'order'            => 'DESC',
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'suppress_filters' => true 
		);
		$posts_array = get_posts($args);
			if(0 < $posts_array) {
				if(!$this->readblog_checksecure()) {
      				exit;
    			}
				foreach( $posts_array as $term) {
					$res['posts'][] = $term;	
					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $term->ID ), 'medium' );
					$res['images'][]['imagen'] = $image;
  					$custom_fields = get_post_custom($term->ID);
  					$res['custom_field'][] = $custom_fields;
				}
			}else{
				$res = array(
				'code' => 013,
				'msg' => "No se han encontrado post"
				);	
			}
			$res['optiones'] = $args;
		wp_send_json($res);
	}
	function readblog_get_posts() {
		if(!$this->readblog_checksecure()) {
      				exit;
    			}
		$elementos = 5;
		$yaCargados = $_GET['next'];
		$categoria = $_GET['id'];
		$argsD = array(
			'posts_per_page'	=> $elementos,
			'offset'           	=> $yaCargados,
			'category'         	=> $categoria,
			'orderby'          	=> 'post_date',
			'order'            	=> 'DESC',
			'post_type'        	=> 'post',
			'post_status'      	=> 'publish',
			'suppress_filters' 	=> true
		);
		$posts_array = get_posts($argsD);
			if(0 < $posts_array) {
				foreach( $posts_array as $term) {
					$res['posts'][] = $term;	
					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $term->ID ), 'medium' );
					$res['images'][]['imagen'] = $image;
  					$custom_fields = get_post_custom($term->ID);
  					$res['custom_field'][] = $custom_fields;
				}
			}else{
			$res = array(
			'code' => 012,
			'msg' => "No se han encontrado post en esta categoría"
			);	
			}
		wp_send_json($res);
	}
	function readblog_savepush() {
		if(!$this->readblog_checksecure()) {
      				exit;
    	}
		global $wpdb;
	
		$gcmkey = $_GET['id'];
	
		$table_name = $wpdb->prefix . 'readbloggcm';
	
		$wpdb->insert( 
		$table_name, 
		array( 
			'time' => current_time( 'mysql' ), 
			'gcm' => $gcmkey, 
		));
		$datos = array(
		'msg' => "Se ha recibido el token push",
		'token' => $gcmkey,
		'resultado' => true);
		wp_send_json($datos);
	}
	function readblog_deltoken(){
		if(!$this->readblog_checksecure()) {
      				exit;
    	}
		global $wpdb;
	
		$gcmkey = $_GET['id'];
	
		$table_name = $wpdb->prefix . 'readbloggcm';
	
		$wpdb->delete( $table_name, array( 'gcm' => $gcmkey ) );
		$datos = array(
		'msg' => "Se ha recibido el token push",
		'token' => $gcmkey,
		'resultado' => true);
		wp_send_json($datos);
	}
	function readblog_get_comments() {
		if(!$this->readblog_checksecure()) {
      				exit;
    			}
		$args = array(
    		'post_id' => $_GET['id'],   // Use post_id, not post_ID
		);
		$res = get_comments( $args );
		wp_send_json($res);
	}
	function readblog_get_categories() {
		if(!$this->readblog_checksecure($nonceCheck, false)) {
      				exit;
    			}
		$args = array(
			'orderby'           => 'name', 
			'order'             => 'ASC',
			'include'			=>  explode(",",get_option('readblog_categoriasShow')),
			'exclude'			=> 'all',
			'hide_empty'        => 1,
			'fields'            => 'all',
			'pad_counts' => true
		);
		$terms = get_terms('category',$args);
		foreach( $terms as $term) {
			$res[] = $term;	
		}
		wp_send_json($res);
	}
	function readblog_get_post() {
		$args = array(
			'page_id'			=> $_GET['id'],
			'post_type'        	=> 'post',
			'post_status'      	=> 'publish',
			'suppress_filters' 	=> true 
		);
		$posts_array = get_posts($args);
			if(0 < $posts_array) {
				if(!$this->readblog_checksecure()) {
      				exit;
    			}
				foreach( $posts_array as $term) {
					$res['posts'][] = $term;	
					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $term->ID ), 'medium' );
					$res['images'][]['imagen'] = $image;
  					$custom_fields = get_post_custom($term->ID);
  					$res['custom_field'][] = $custom_fields;
				}
			}else{
				$res = array(
				'code' => 013,
				'msg' => "No se han encontrado post"
				);	
			}
		wp_send_json($res);	
	}
	/***********
	Check de seguridad
	***********/
	function readblog_checksecure() {
		if($_GET['readblogtoken']=="") {
			
			$return['status']['code'] = '401';
      		$return['status']['message'] = 'No token access';
      		wp_send_json( $return );
     		return false;

		}elseif($_GET['readblogtoken']!=get_option('readblog_token')){
			
			$return['status']['code'] = '401';
      		$return['status']['message'] = 'No token valid';
      		wp_send_json( $return );
     		return false;
			
			
		}else{
			return true;	
		}
	}
  /**
  * Create singleton for this class
  *
  * @return object instance of self
  */
  public static function get_instance(){
    if( null === self::$instance ){
      self::$instance = new readblog_Ajax();
    }
    return self::$instance;
  }
}

?>
