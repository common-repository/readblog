jQuery(document).ready(function () {
	var $j = jQuery.noConflict();
	$j('#readblog_notification').change(function() {
		if($j(this).is(":checked")) {
			$j(".readBlog-msg-force").show();	
		}else{
			$j(".readBlog-msg-force").hide();		
		}
	});
});