<?php
/*
Plugin Name: readBlog
Description: Conexión con readBlog
Author: Daniel Riera
Version: 1.1.1
*/

if ( ! defined( 'ABSPATH' ) ) exit;
$dir = dirname( __FILE__ );
@include_once "$dir/ajax.php";
@include_once "$dir/api.php";
@include_once "$dir/GCM.php";
function readblog_init() {
  $readblog_instance = readBlog_Admin::get_instance();
}

function readblog_activation() {
  //activation
  	global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'readbloggcm';

		$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		TIME datetime DEFAULT  '0000-00-00 00:00:00' NOT NULL,
		gcm varchar(190) NOT NULL,
		PRIMARY KEY id (id),
		UNIQUE KEY gcm (gcm)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
}
function readblog_deactivation() {
  	// Deactivation hooks
  	//delete_option('readblog_mostrar_comentarios');
//	delete_option('readblog_colorTheme');
//	delete_option('readblog_token');
}


function readblog_panel_opciones(){
$opciones = get_option('readblog_opciones',$valores_default);
register_setting( 'readblog_opciones_connect', 'readblog_comentarios', 'readblog_validar' );



add_menu_page(
'Opciones - readBlog',
'readBlog',

'administrator',

'readblog',

'readblog_opciones_panel',

plugins_url( 'readblog/img/icon.png' )
);
 add_submenu_page('Enviar Notificaciones', 'Enviar Notificaciones', 'administrator', 'readblog_send_notifications', 'readblog_send_notifications');
}

function readblog_send_notifications() {
	require 'send_notifications.php';	
}
function sendPushAll($mensajePush, $lugar) {
	global $wpdb;
	 
	$table_name = $wpdb->prefix . "readbloggcm";
	
	$registrosGcm = $wpdb->get_results("SELECT * FROM " . $table_name, ARRAY_A);
	$registatoin_ids = array();
	foreach ( $registrosGcm as $row ) {
		array_push($registatoin_ids,$row['gcm']);
	}
			$message = $mensajePush;
    		$gcm = new GCM(); 
			$nameblog = get_bloginfo('name') . ' - readBlog';
    		$result = $gcm->send_notification($registatoin_ids, $message, $nameblog, $lugar);
}
function readblog_opciones_panel(){
echo "<h1>readBlog Plugin</h1>";
echo "<p>Configuración Básica readBlog</p>";

	if(current_user_can('activate_plugins')==1) {
		/****
		Check if the user have access
		***/
 		if(isset($_POST['action']) && $_POST['action'] == "salvaropciones"){
				$referer = $_POST['_wp_http_referer'];
				$checkReferer = check_admin_referer('salvaropciones','nonce');
				if($checkReferer and wp_verify_nonce($_POST['nonce'], $_POST['action'])) {
					update_option('readblog_categoriasShow',implode(",", $_POST['post_category']));
					update_option('readblog_mostrar_comentarios',$_POST['comentarios']);
					//GoogleAnalytics
					update_option('readblog_googleanaliticas',$_POST['analiticas']);
					//GoogleAdsense
					update_option('readblog_googlepubli',$_POST['publi']);
					if($_POST['pushnotification']!="" or $_POST['pushnotification']!=NULL){
							sendPushAll($_POST['pushnotification'],0);
							echo ("<div class='notice notice-info' style='padding: 10px'>Notificaciones Enviadas</div>");
					}
					update_option('readblog_colorTheme',$_POST['color']);
					echo ("<div class='updated message' style='padding: 10px'>Opciones guardadas.</div><div class='notice notice-info' style='padding: 10px'>Recuerda que tienes que ir a Ajustes/Enlaces permanentes y pulsar en Guardar ( No es necesario modificar nada en esa página ) simplemente hacer clic en Guardar</div> ");
				}
        	
    	}
	}else{
	die("No tiene permisos para acceder a este espacio");	
	}
	
 
    ?>
<style>
table tr td {
	padding:10px; 
 }
.nota {
	color:#666;
	font-size:10px;	 
}
li {
	list-style:none;
}
 </style>
 <div>
 <?php
 	
 	function readblog_generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
 if(!get_option('readblog_token')) {
	 $token = readblog_generateRandomString(50);
	 update_option('readblog_token',$token);
 }else{
	$token = get_option('readblog_token');
 }
 ?>
    <form method='post'>
    <?php
    	wp_nonce_field('salvaropciones','nonce');
	?>
        <input type='hidden' name='action' value='salvaropciones'>
        <table width="1355">
        <tr>
                <td width="337">
                    Código QR
                    <div class="nota">Descarga la imagen y colócala donde quieras, con la aplicación readBlog podrán escanear el código QR para añadir tu sitio a readBlog</div>
                </td>
                <td width="468">
                <?php if(get_option('readblog_colorTheme')) { ?>
                    <img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=<?=$token."-|-".get_site_url()."-|-".get_option('readblog_colorTheme')."-|-".get_bloginfo()?>&choe=UTF-8" /> 
					<?php }else{
					echo "Selecciona un color para y pulsa en guardar para generar el código QR";
					}
					?>
                    
                </td>
                <td width="534" rowspan="5" align="left" valign="top">&nbsp;</td>
            </tr>
            <tr>
                <td>
                  Mostrar comentarios
                  <div class="nota">Mostrar los comentarios de los post, esto afecta tanto al listado de post en categoria como en la presentación del artículo.</div>
                </td>
                <td>
                   <input type='checkbox' name='comentarios' id='comentarios' value='1' <?php checked(1== get_option('readblog_mostrar_comentarios'));?> />
                </td>
            </tr>
            <tr>
                <td>
                  Color de la Aplicación
                  <div class="nota">Este ajuste cambiará por completo la aplicación, readBlog cambiará el color al entrar en tu sitio</div>
                </td>
                <td>
                    <?php $color = get_option('readblog_colorTheme');?>
                    <select name="color" id="color">
                    	<option <? if($color=="black"){ echo "selected='selected'"; } ?> value="black">Negro</option>
                        <option <? if($color=="pink"){ echo "selected='selected'"; } ?> value="pink">Rosa</option>
                        <option <? if($color=="purple"){ echo "selected='selected'"; } ?> value="purple">Púrpura</option>
                        <option <? if($color=="deeppurple"){ echo "selected='selected'"; } ?> value="deeppurple">Morado</option>
                        <option <? if($color=="indigo"){ echo "selected='selected'"; } ?> value="indigo">Azul Añil</option>
                        <option <? if($color=="blue"){ echo "selected='selected'"; } ?> value="blue">Azul</option>
                        <option <? if($color=="lightblue"){ echo "selected='selected'"; } ?> value="lightblue">Azul CLaro</option>
                        <option <? if($color=="cyan"){ echo "selected='selected'"; } ?> value="cyan">Cian</option>
                        <option <? if($color=="teal"){ echo "selected='selected'"; } ?> value="teal">Verde Azulado</option>
                        <option <? if($color=="lime"){ echo "selected='selected'"; } ?> value="lime">Lima</option>
                        <option <? if($color=="yellow"){ echo "selected='selected'"; } ?> value="yellow">Amarillo</option>
                        <option <? if($color=="amber"){ echo "selected='selected'"; } ?> value="amber">Ambar</option>
                        <option <? if($color=="orange"){ echo "selected='selected'"; } ?> value="orange">Naranja</option>
                        <option <? if($color=="deeporange"){ echo "selected='selected'"; } ?> value="deeporange">Naranja Oscuro</option>
                        <option <? if($color=="brown"){ echo "selected='selected'"; } ?> value="brown">Marron</option>
                        <option <? if($color=="gray"){ echo "selected='selected'"; } ?> value="gray">Gris</option>
                        <option <? if($color=="bluegray"){ echo "selected='selected'"; } ?> value="bluegray">Gris Azulado</option>
                        <option <? if($color=="white"){ echo "selected='selected'"; } ?> value="white">Blanco</option>
                    </select>
                </td>
            </tr>
             <tr>
                <td>
                  ID Google Analytics
                  <div class="nota">Como si de tu blog se tratara, verás el uso que tiene tu blog desde tu cuenta en Google Analytics</div>
                </td>
                <td>
                   <input type='text' name='analiticas' id='analiticas' placeholder='UA-XXXXXXXX-X' value="<?php if(get_option('readblog_googleanaliticas')){ echo get_option('readblog_googleanaliticas');} ?>" />
                </td>
             </tr>
             <tr>
                <td>
                  Google Admob
                  <div class="nota">Si esta opción es utilizada la aplicación mostrará anuncios con su cuenta de Google Admob</div>
                </td>
                <td>
                   <input type='text' name='publi' id='publi' placeholder='ca-app-pub-......' value="<?php if(get_option('readblog_googlepubli')){ echo get_option('readblog_googlepubli');} ?>" />
                </td>
             </tr>
             <tr>
             	<td>Notificacion Push</td>
                <td><input type="text" name="pushnotification" id="pushnotification"/></td>
             </tr>
             <tr>
              <td>Categorias en readBlog
              <div class="nota">Selecciona las categorias que quieres mostrar en la aplicación</div>
              <div class="nota" style="font-weight:bolder">Si ninguna es seleccionada se utilizarán todas</div>
              </td>
              <td>
              <?php 
			  	if(get_option('readblog_categoriasShow')!= NULL || get_option('readblog_categoriasShow') !="") {
					$catSelect = explode(",",get_option('readblog_categoriasShow'));
				}else{
					$catSelect = "";
				}
			  wp_category_checklist( 0, 0, $catSelect )?>
              </td>
            </tr>
             <!--<tr>
              <td>Campos Personalizados
              <div class="nota">Si tu contenido incluye campos personalizados selecciona donde quieres mostrar los campos personalizados dentro del contenido.</div>
              </td>
              <td>
              <select name="camposPersonalizados">
              	<option>No mostrar campos personalizados</option>
              	<option>Encima del contenido</option>
              	<option>Debajo del contenido</option>
              </select>
              </td>
            </tr>-->
            <tr>
                <td colspan='3'>
                    <input type='submit' value='Enviar'>
                </td>
            </tr>
            
            <tr>
                <td>
                  Derechos de autor
                </td>
                <td>
                   <p> Todos los contenidos de su blog serán mostrados en readBlog, al usar este servicio asume que tiene todo el poder y derechos legales sobre el contenido que publica, usted es el único responsable de su contenido, su contenido no es propiedad de readBlog en ningún momento, cualquier queja recibida por terceros sobre su contenido será su responsabilidad, readBlog se reserva el derecho de rechazar su blog en cualquier momento si no tiene los derechos legales sobre el contenido o es material sensible.</p>
                    
                    <p>readBlog renuncia a cualquier responsabilidad sobre su contenido.</p>
                </td>
            </tr>
        </table>
    </form></div>
	<?php echo "";
}
//ADD POST NOTIFICATION


function readblog_post_options_metabox() {
    add_meta_box( 'post_options', __( 'Envío de notificaciones push' ), 'readblog_post_options_code', 'post', 'normal', 'high' );
}

/**
 *  Prints the box content
 */
function readblog_post_options_code( $post ) { 
    wp_nonce_field( plugin_basename( __FILE__ ), $post->post_type . '_noncename' ); ?>
    <div class="alignleft">
     	<p><input type="checkbox" value="1" name="readblog_notification" id="readblog_notification" /> Marque esta casilla  si desea enviar a sus lectores de readBlog una notificación Push de esta publicación.</p>
        <?php
        if($_GET['action']=="edit") {
			echo "<div class='readBlog-msg-force' style='display:none'>";
			echo "<p>Por defecto al editar un post publicado no se permite enviar una nueva notificación, si es un cambio importante puede forzar el envio, pero piensalo detenidamente si merece la pena enviar una nueva notificaión por este cambio, ya que podrá perder lectores al recibir notificaciónes continuadas.</p>";
			echo '<input type="checkbox" value="1" name="readblog_notification_force" id="readblog_notification_force" /> Forzar notificación';
			echo '</div>';
		}
		?>
        <h3>No abuse de este servicio ya que podrá perder lectores en readBlog si las notificaciones son continuas</h3>
    </div>
    <div class="clear"></div>
    <hr /><?php
}

/** 
 * When the post is saved, saves our custom data 
 */
function readblog_save_post_options( $post_id ) {
  // verify if this is an auto save routine. 
  // If it is our form has not been submitted, so we dont want to do anything
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times
  if ( !wp_verify_nonce( @$_POST[$_POST['post_type'] . '_noncename'], plugin_basename( __FILE__ ) ) )
      return;

  // Check permissions
  if ( !current_user_can( 'edit_post', $post_id ) )
     return;

  // OK, we're authenticated: we need to find and save the data
  if( 'post' == $_POST['post_type'] ) {
      if ( !current_user_can( 'edit_post', $post_id ) ) {
          return;
      } else {
		  if($_POST['readblog_notification']==1) {
			  if($_POST['action']=="editpost" and $_POST['readblog_notification_force']==1){
			  		sendPushAll($_POST['post_title'],"post:".$post_id);
			  }
			  if($_GET['action']!="edit" and substr($_POST['_wp_http_referer'],-12)=="post-new.php" and get_option('readblog_notificaciones_rep')==0) {
			  	sendPushAll($_POST['post_title'],"post:".$ID);
				update_option('readblog_notificaciones_rep',1);
			  }elseif(get_option('readblog_notificaciones_rep')==1){
				 update_option('readblog_notificaciones_rep',0);
			  }
		  }
		 
      }
  } 

}

function readblog_scripts_basic($hook) {
	wp_enqueue_script( 'readblog-admin-script', plugins_url('/js/readblog-script.js',__FILE__));
}
add_action('admin_menu', 'readblog_panel_opciones');
add_action( 'plugins_loaded', 'readblog_init' );
add_action( 'add_meta_boxes', 'readblog_post_options_metabox' );
add_action( 'admin_init', 'readblog_post_options_metabox', 1 );
add_action( 'save_post', 'readblog_save_post_options' );
add_action( 'admin_enqueue_scripts', 'readblog_scripts_basic' );
register_activation_hook( "$dir/plugin.php", 'readblog_activation' );
register_deactivation_hook( "$dir/plugin.php", 'readblog_deactivation' );
