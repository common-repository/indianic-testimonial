<?php

function _set_default_quicktags($qtInit) {
  $qtInit['buttons'] = 'strong,ul,ol,li,link,code,close';

  return $qtInit;
}

add_filter('quicktags_settings', '_set_default_quicktags', 10, 1);

function _add_inic_testimonials_quicktags() {
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
  <div id="icon-themes" class="icon32 icon32-posts-post"><br></div>
  <h2>Widget Template

    <?php if (isset($_GET['type']) && $_GET['type'] == "add") { ?>
      <a href="admin.php?page=inic_testimonial_widget_template" class="add-new-h2">All Widgets</a>
    <?php } else { ?>
      <a href="admin.php?page=inic_testimonial_widget_template&type=add" class="add-new-h2">Add New</a>
    <?php } ?>
  </h2>

  <div id="message" class="updated below-h2" style="display:none;"><p></p></div>

  <?php
  if (isset($_GET['type']) && $_GET['type'] == "add") {

    if (isset($_GET['id']) && $_GET['id'] && is_numeric($_GET['id'])) {
      $_current_widget = $this->wpdb->get_results("SELECT * FROM {$this->wpdb->prefix}inic_testimonial_widget WHERE id='{$_GET['id']}'");
      $_current_widget = $_current_widget[0];
      $id = esc_attr($_current_widget->id);
      $title = esc_attr($_current_widget->title);
      $no_of_testimonial = esc_attr($_current_widget->no_of_testimonial);
      $only_featured = esc_attr($_current_widget->only_featured);
      $filter_by_country = esc_attr($_current_widget->filter_by_country);
      $filter_by_tags = esc_attr($_current_widget->filter_by_tags);
      $display_randomly = esc_attr($_current_widget->display_randomly);
      $html_template = esc_attr($_current_widget->html_template);
    } else {
      $id = $title = $no_of_testimonial = $only_featured = $filter_by_country = $filter_by_tags = $display_randomly = $html_template = false;
    }
    ?>

    <form name="testimonial_widget_template" method="post" action="">
      <?php wp_nonce_field('save_testimonial_widget_template','_nonce'); ?>
      <input type="hidden" name="action" value="iNIC_testimonial_save_widget" />

      <?php
      if ($id) {
        echo "<input type=\"hidden\" name=\"id\" value=\"{$id}\" />";
      }
      ?>

      <table class="form-table">
        <tbody>
          <tr>
            <th><label for="widget_title">Widget Title</label></th>
            <td><input name="widget_title" type="text" id="widget_title" value="<?php echo $title; ?>" class="regular-text"></td>
          </tr>

          <tr>
            <th><label for="no_of_testimonials">No of Testimonials</label></th>
            <td><input name="no_of_testimonials" type="text" id="no_of_testimonials" value="<?php echo $no_of_testimonial; ?>" class="regular-text"></td>
          </tr>

          <tr>
            <th><label for="list_only_featured_testimonials">List only featured Testimonials</label></th>
            <td><input name="list_only_featured_testimonials" type="checkbox" id="list_only_featured_testimonials" value="1"<?php echo $only_featured ? ' checked="checked"' : '' ?>></td>
          </tr>

          <tr>
            <th><label for="filter_by_country">Filter by Country</label></th>
            <td><input name="filter_by_country" type="text" id="filter_by_country" value="<?php echo $filter_by_country; ?>" class="regular-text"> Comma Separated. <code>country1,country2,country3</code></td>
          </tr>

          <tr>
            <th><label for="filter_by_tags">Filter by Tags</label></th>
            <td><input name="filter_by_tags" type="text" id="filter_by_tags" value="<?php echo $filter_by_tags; ?>" class="regular-text"> Comma Separated. <code>tag1,tag2,tag3</code></td>
          </tr>
          
          <tr>
            <th><label for="display_randomly">Display Randomly</label></th>
            <td><input name="display_randomly" type="checkbox" id="display_randomly" value="1"<?php echo $display_randomly ? ' checked="checked"' : ''; ?> /></td>
          </tr>

          <tr>
            <th><label for="widget_template">Widget HTML Template</label></th>
            <td><?php wp_editor(stripslashes($html_template), 'widget_template', array('media_buttons' => false, 'textarea_rows' => 10, 'tinymce' => false)); ?></td>
          </tr>

        </tbody>
      </table>
      <p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php echo $id ? "Update Widget Template" : "Add Widget Template"; ?>"></p>

    </form>

    <script type="text/javascript" >
      jQuery(document).ready(function($) {
          
        jQuery("form[name=testimonial_widget_template]").submit(function(e){
          var _this = jQuery(this);
          _this.find("input[type=submit]").addClass("button-disabled").attr("disabled", "disabled").removeClass("button-primary");
          jQuery.post(ajaxurl, $(this).serialize(), function(data) {
    		
            if(data.error) {
              jQuery("#message").show().addClass("error").removeClass('updated').find('p').html(data.error);
            } else if(data.success) {
              jQuery("#message").removeClass('error')
              jQuery("#message").show().addClass('updated').find('p').html(data.success);
            }
            
            _this.find("input[type=submit]").addClass("button-primary").removeClass("button-disabled").attr("disabled", false);
          }, 'json');
          e.preventDefault();
        });
    	
      });
    </script>

  <?php } else { ?>    

    <table class="wp-list-table widefat fixed testimonials" cellspacing="0">
      <thead>
        <tr>
          <th>Title</th>
          <th width="150">No of Testimonials</th>
          <th width="150">Only Featured</th>
          <th>Filter by Country</th>
          <th>Filter by Tags</th>
        </tr>
      </thead>

      <tbody>
        <?php
        $_results = $this->wpdb->get_results("SELECT * FROM {$this->wpdb->prefix}inic_testimonial_widget");
        if ($_results) {
          foreach ($_results as $_results) {
            $_results->only_featured = ($_results->only_featured) ? 'Yes' : 'No';
            echo "<tr>
                    <td>".  esc_html($_results->title)."
                      <div class=\"row-actions\">
                        <span class=\"edit\"><a href=\"admin.php?page=inic_testimonial_widget_template&type=add&id=".  esc_attr($_results->id)."\" title=\"Edit this item\">Edit</a> | </span>
                        <span class=\"trash\"><a class=\"submitdelete\" title=\"Delete this item\" href=\"javascript:void(0)\" rel=\"".  esc_attr($_results->id)."\">Delete</a></span>
                      </div>
                    </td>
                    <td>". esc_html($_results->no_of_testimonial)."</td>
                    <td>".  esc_html($_results->only_featured)."</td>
                    <td>".  esc_html($_results->filter_by_country)."</td>
                    <td>".  esc_html($_results->filter_by_tags)."</td>
                  </tr>";
          }
        } else {
          echo "<tr><td colspan=\"5\">No Widget Template Found.</td></tr>";
        }
        ?>
      </tbody>
    </table>

    <style>
      tr:nth-child(even) td {background-color: #fff;}
    </style>

    <script>
      jQuery(document).ready(function(){
        jQuery("table.testimonials tr td span.trash a").click(function(e){
        
          var _this = jQuery(this);
        
          var r = confirm("Are you sure want to delete this template?");
          if(r == true) {
          
            jQuery.post(ajaxurl, {action:"iNIC_testimonial_delete_widget", id:jQuery(this).attr('rel')}, function(data){
            
              if(data.error) {
                jQuery("#message").show().addClass("error").removeClass('updated').find('p').html(data.error);
              } else if(data.success) {
                jQuery("#message").removeClass('error')
                jQuery("#message").show().addClass('updated').find('p').html(data.success);
                _this.closest("tr").remove();
              }
            }, 'json')
          }
          e.preventDefault();
        });
      });
    </script>

  <?php } ?>
</div>
