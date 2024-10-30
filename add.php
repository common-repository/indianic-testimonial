<?php
if (isset($_REQUEST['id']) && $_REQUEST['id']) {
  $_result = $this->wpdb->get_results("SELECT * FROM {$this->wpdb->prefix}inic_testimonial WHERE id='{$_REQUEST['id']}'");
  $_result = $_result[0];
  
  $id = esc_attr($_result->id);
  $project_name = esc_attr($_result->project_name);
  $project_url = esc_attr($_result->project_url);
  $client_name = esc_attr($_result->client_name);
  $city = esc_attr($_result->city);
  $state = esc_attr($_result->state);
  $country = esc_attr($_result->country);
  $description = esc_attr($_result->description);
  $tags = esc_attr($_result->tags);
  $video_url = esc_attr($_result->video_url);
  $thumb_img_url = esc_attr($_result->thumb_img_url);
  $large_img_url = esc_attr($_result->large_img_url);
  $is_featured = esc_attr($_result->is_featured);
}
?>

<div class="wrap">
  <div id="icon-options-general" class="icon32 icon32-posts-post"><br></div>
  <h2>Save Testimonial</h2>

  <div id="message" class="below-h2"></div>
  <form name="testimonial_add" method="post" action="" enctype="multipart/form-data">
    <?php wp_nonce_field('save_testimonial','_nonce'); ?>
    <input type="hidden" name="action" value="iNIC_testimonial_save" />
    <?php
    if (isset($_REQUEST['id']) && $_REQUEST['id']) {
      echo '<input type="hidden" name="id" value="' . esc_attr($_REQUEST['id']) . '" />';
    }
    ?>

    <table class="form-table">
      <tbody>
        <tr>
          <th><label for="project_name">Project Name</label></th>
          <td><input name="project_name" type="text" id="project_name" value="<?php echo $project_name; ?>" class="regular-text required" /></td>
        </tr>

        <tr>
          <th><label for="project_url">Project URL</label></th>
          <td><input name="project_url" type="text" id="project_url" value="<?php echo $project_url; ?>" class="regular-text required" /></td>
        </tr>

        <tr>
          <th><label for="client_name">Client Name</label></th>
          <td><input name="client_name" type="text" id="client_name" value="<?php echo $client_name; ?>" class="regular-text required" /></td>
        </tr>

        <tr>
          <th>&nbsp;</th>
          <td>
            <div style="float:left;">
              <label for="client_city">City</label><br />
              <input name="client_city" type="text" id="client_city" value="<?php echo $city; ?>" class="normal-text" />
            </div>

            <div style="float:left; margin-left:20px;">
              <label for="client_state">State</label><br />
              <input name="client_state" type="text" id="client_state" value="<?php echo $state; ?>" class="normal-text" />
            </div>

            <div style="float:left; margin-left:20px;">
              <label for="client_country">Country</label><br />
              <select name="client_country" id="client_country" class="required">
                <option value="">Select Country</option>
                <?php
                $_country_list = $this->get_country_list();
                foreach ($_country_list as $_country_list) {
                  $_is_selected = $_country_list == $country ? ' selected="selected"' : '';
                  echo "<option value=\"{$_country_list}\"{$_is_selected}>{$_country_list}</option>";
                }
                ?>
              </select>

<!--<input name="client_country" type="text" id="client_country" value="<?php //echo $country;   ?>" class="normal-text required" />-->
            </div>
            <div style="clear:both;"></div>
          </td>
        </tr>

        <tr>
          <th><label for="tags">Description</label></th>
          <td><?php wp_editor($description, 'description', array('media_buttons' => false, 'textarea_rows' => 10, 'tinymce' => false)); ?></td>
        </tr>

        <tr>
          <th><label for="tags">Tags</label></th>
          <td><input name="tags" type="text" id="tags" value="<?php echo $tags; ?>" class="regular-text required" /> Comma Separated. <code>tag1,tag2,tag3</code></td>
        </tr>

        <tr>
          <th><label for="video_url">Video URL</label></th>
          <td><input name="video_url" type="text" id="video_url" value="<?php echo $video_url; ?>" class="regular-text" /></td>
        </tr>

        <tr>
          <th><label for="thumb_img">Thumb Image</label></th>
          <td><div class="iNICfaqsUploader" id="thumb_img" name="Upload Thumb Image" value="<?php echo $thumb_img_url; ?>"></div></td>
        </tr>

        <tr>
          <th><label for="large_img">Large Image</label></th>
          <td><div class="iNICfaqsUploader" id="large_img" name="Upload Large Image" value="<?php echo $large_img_url; ?>"></div></td>
        </tr>

        <tr>
          <th><label for="is_featured">Is Featured?</label></th>
          <td><input name="is_featured" type="checkbox" id="is_featured" value="1"<?php echo $is_featured ? ' checked="checked"' : '' ?> /></td>
        </tr>

      </tbody>
    </table>
    <p class="submit">
      <input type="submit" name="submit" id="submit" class="button-primary" value="Save Testimonial">
      <a href="admin.php?page=inic_testimonial_view" class="button-secondary">Cancel</a>
    </p>

  </form>


</div>
<style>
  tr.error th, tr.error td {background-color: #FFEBE8!important; border-bottom: solid 1px #CCC!important;}
  tr.error td .required {border-color: #C00!important;}
</style>

<script type="text/javascript" >
  jQuery(document).ready(function($) {
    jQuery("form[name=testimonial_add]").submit(function(e){        
      $('form[name=testimonial_add] tr.error').removeClass("error");
      var hasError = false;
      $('.required').each(function() {
        if(jQuery.trim($(this).val()) == '') {
          $(this).closest('tr').addClass("error");
          hasError = true;
        } else if($(this).hasClass('email')) {
          var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
          if(!emailReg.test(jQuery.trim($(this).val()))) {
            $(this).closest('tr').addClass("error");
            hasError = true;
          }
        }
      });
      if(hasError == false) {
        
        var _this = jQuery(this);
        _this.find("input[type=submit]").addClass("button-disabled").attr("disabled", "disabled").removeClass("button-primary");
        jQuery.post(ajaxurl, _this.serialize(), function(data) {
          if(data.error) {
            jQuery("#message").show().addClass("error").removeClass('updated').html("<p>"+data.error+"</p>");
          } else if(data.msg) {
            jQuery("#message").removeClass('error')
            jQuery("#message").show().addClass('updated').html("<p>"+data.msg+"</p>");
            
            if(data.form_reset) {
              jQuery("form[name=testimonial_add]")[0].reset();
              jQuery('#thumb_img_img_src, #large_img_img_src').css('display', 'none').html('');
              jQuery('input[name=thumb_img], input[name=large_img]').val('');
            }
            
          }
          _this.find("input[type=submit]").addClass("button-primary").removeClass("button-disabled").attr("disabled", false);
        }, 'json');
        
      } 
      e.preventDefault();
    });

  });
</script>
