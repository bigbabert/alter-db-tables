jQuery.noConflict();
jQuery(document).ready(function() {
    jQuery('#alter-db-tables-upload_image_bg_button').click(function() {
         targetfield = jQuery(this).prev('#upload_image1');
         tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
         return false;
    });
    jQuery('#alter-db-tables-upload_image_button').click(function() {
         targetfield = jQuery(this).prev('#upload_image');
         tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
         return false;
    });
 
    window.send_to_editor = function(html) {
         imgurl = jQuery('img',html).attr('src');
         jQuery(targetfield).val(imgurl);
         tb_remove();
    }
 
});
