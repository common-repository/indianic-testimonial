<?php
function _set_default_quicktags( $qtInit )
{
    $qtInit['buttons'] = 'strong,ul,ol,li,link,code,close';
    
    return $qtInit;
}
add_filter('quicktags_settings', '_set_default_quicktags', 10, 1);


function _add_inic_testimonials_quicktags()
{
	echo '<script type="text/javascript">';
	echo "QTags.addButton( 'inic_testimonials_id', '{#ID}', '{#ID}' );";
	echo "QTags.addButton( 'inic_testimonials_project_name', '{#ProjectName}', '{#ProjectName}' );";
    echo "QTags.addButton( 'inic_testimonials_project_url', '{#ProjectUrl}', '{#ProjectUrl}' );";
	echo "QTags.addButton( 'inic_testimonials_client_name', '{#ClientName}', '{#ClientName}' );";
	echo "QTags.addButton( 'inic_testimonials_city', '{#City}', '{#City}' );";
	echo "QTags.addButton( 'inic_testimonials_state', '{#State}', '{#State}' );";
	echo "QTags.addButton( 'inic_testimonials_country', '{#Country}', '{#Country}' );";
	echo "QTags.addButton( 'inic_testimonials_description', '{#Description}', '{#Description}' );";
	echo "QTags.addButton( 'inic_testimonials_tags', '{#Tags}', '{#Tags}' );";
	echo "QTags.addButton( 'inic_testimonials_video_url', '{#VideoUrl}', '{#VideoUrl}' );";
	echo "QTags.addButton( 'inic_testimonials_thumb_img_url', '{#ThumbImgUrl}', '{#ThumbImgUrl}' );";
	echo "QTags.addButton( 'inic_testimonials_large_img_url', '{#LargeImgUrl}', '{#LargeImgUrl}' );";
    echo "QTags.addButton( 'inic_testimonials_counter', '{#Counter}', '{#Counter}' );";
    echo "QTags.addButton( 'inic_testimonials_if', '[IF]', '[IF]', '[/IF]');";
	echo "</script>";
}
add_action('admin_print_footer_scripts', '_add_inic_testimonials_quicktags');
?>

<div class="wrap">
	<div id="icon-options-general" class="icon32 icon32-posts-post"><br></div>
	<h2>Settings</h2>
	
	<div id="message" class="updated below-h2" style="display:none;"><p></p></div>
    
	<form name="testimonial_setting" method="post" action="">
    <?php wp_nonce_field('save_testimonial_options','_nonce'); ?>
    
		<input type="hidden" name="action" value="iNIC_testimonial_save_setting" />
		<table class="form-table">
			<tbody>
                <tr>
					<th><label>Short Code</label></th>
					<td>[iNICtestimonial tpl=1]</td>
				</tr>
                
                <tr>
					<th><label>Short Code Attributes</label></th>
					<td><code>tpl</code>, <code>no_of_testimonial</code>, <code>list_per_page</code>, <code>ord_by</code>, <code>filter_by_country</code>, <code>filter_by_tags</code>, <code>custom_query</code>, <code>show_featured_at</code>, <code>no_of_featured</code>, <code>featured_template</code>, <code>listing_template_odd</code>, <code>listing_template_even</code></td>
				</tr>
                
				<tr>
					<th><label for="inic_testimonial_admin_list_per_page">Admin pages show at most</label></th>
          <td><input name="inic_testimonial_admin_list_per_page" type="text" id="inic_testimonial_admin_list_per_page" value="<?php echo esc_attr(get_option("inic_testimonial_admin_list_per_page")); ?>" class="small-text"> Testimonials</td>
				</tr>
                
                <tr>
					<th><label for="inic_testimonial_list_ord_by">Admin pages List Order by</label></th>
                    <td>
                      <?php $_ord_by = get_option("inic_testimonial_list_ord_by") ? explode(" ", get_option("inic_testimonial_list_ord_by")) : array("id", "ASC"); ?>
                      <select name="ord_by">
                        <?php
                        $_table_field = $this->wpdb->get_results("SHOW COLUMNS FROM {$this->wpdb->prefix}inic_testimonial");
                        foreach($_table_field as $_table_field) {
                          $_is_selected = $_ord_by[0] == $_table_field->Field ? ' selected="selected"' : '';
                          echo "<option value=\"".  esc_attr($_table_field->Field)."\"{$_is_selected}>".  esc_attr($_table_field->Field)."</option>";
                        }
                        ?>
                      </select>
                      <select name="ord_type">
                        <option value="ASC"<?php echo $_ord_by[1] == "ASC" ? ' selected="selected"' : ''; ?>>ASC</option>
                        <option value="DESC"<?php echo $_ord_by[1] == "DESC" ? ' selected="selected"' : ''; ?>>DESC</option>
                      </select>
                    </td>
				</tr>             
				
				<tr>
					<th><label for="inic_testimonial_html_template">Testimonial Default HTML Template</label></th>
                    <td><?php wp_editor(stripslashes(get_option("inic_testimonial_html_template")), 'inic_testimonial_html_template', array('media_buttons' => false, 'textarea_rows' => 10, 'tinymce'=>false, 'class'=>'required') ); ?></td>
				</tr>
				
			</tbody>
		</table>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"></p>
		
	</form>
	
</div>

<script type="text/javascript" >
jQuery(document).ready(function($) {
//
	jQuery("form[name=testimonial_setting]").submit(function(e){
		var _this = jQuery(this);
		_this.find("input[type=submit]").addClass("button-disabled").attr("disabled", "disabled").removeClass("button-primary");
		jQuery.post(ajaxurl, $(this).serialize(), function(data) {
		
        if(data.error) {
          jQuery("#message").show().addClass("error").removeClass('updated').find('p').html(data.error);
        } else if(data.msg) {
            jQuery("#message").removeClass('error')
			jQuery("#message").show().addClass('updated').find('p').html(data.msg);
		}
        
		_this.find("input[type=submit]").addClass("button-primary").removeClass("button-disabled").attr("disabled", false);
		}, 'json');
		e.preventDefault();
	});
	
});
</script>
