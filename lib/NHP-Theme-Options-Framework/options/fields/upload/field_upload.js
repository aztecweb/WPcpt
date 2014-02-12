jQuery(document).ready(function(){

	
	/*
	 *
	 * NHP_Options_upload function
	 * Adds media upload functionality to the page
	 *
	 */
	 
	var header_clicked = false;
	var $preview;
	var $bt_browse;
	var $bt_remove;
	
	
	jQuery("img[src='']").attr("src", nhp_upload.url);
	
	jQuery('.nhp-opts-upload').click(function() {
		header_clicked = true;
		
		formfield = jQuery(this).attr('rel-id');
		$bt_browse = jQuery(this);
		$bt_remove = jQuery(this).parent().find(".nhp-opts-upload-remove");
		$preview = jQuery(this).parent().find(".preview-upload");
		
		tb_show('', 'media-upload.php?type=image&amp;post_id=0&amp;TB_iframe=true');
		return false;
	});
	
	
	// Store original function
	window.original_send_to_editor = window.send_to_editor;
	
	
	window.send_to_editor = function(html) {
		if ( header_clicked ) {
			var fileurl = jQuery(html).attr('src');
			var preview = html;
			if( typeof fileurl === "undefined" ) {
				fileurl = jQuery(html).attr("href");
				preview = jQuery(html).text(fileurl);
			}
			
			$bt_remove.show();
			$bt_browse.hide();
			$preview
				.show()
				.html(html);
			
			jQuery('#' + formfield).val(fileurl);
			tb_remove();
			header_clicked = false;
		} else {
			window.original_send_to_editor(html);
		}
	}
	
	jQuery('.nhp-opts-upload-remove').click(function(){
		$relid = jQuery(this).attr('rel-id');
		jQuery('#'+$relid).val('');
		
		jQuery(this).parent().find(".nhp-opts-upload").show();
		jQuery(this).parent().find(".preview-upload").hide();		
		jQuery(this).hide();
	});
});