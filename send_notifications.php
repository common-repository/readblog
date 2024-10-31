<?php
if (!function_exists('is_admin') || !is_admin()) {
    die('Invalid access.');
}
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<form name="webservices_form" method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<label>
Envia notificaciones a todos los usuarios de readBlog con tu blog añadido.
<input type="text" id="noti" name="readblog_notificacion" placeholder="Notificar..." />
</label>
<input type="button" onclick="return chk_form();"  value="Enviar Notificaciones" class="button-primary" />
</form>
<script type="text/javascript">
function chk_form() {
   var noti = document.getElementById("noti").value;
   if(noti!="") {
        document.webservices_form.submit();
        document.webservices_form.action = '';
    }
}
</script>