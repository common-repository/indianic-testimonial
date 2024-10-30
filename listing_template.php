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
  <h2>Listing Template

    <?php if (isset($_GET['type']) && $_GET['type'] == "add") { ?>
      <a href="admin.php?page=inic_testimonial_listing_template" class="add-new-h2">All Template</a>
    <?php } else { ?>
      <a href="admin.php?page=inic_testimonial_listing_template&type=add" class="add-new-h2">Add New</a>
    <?php } ?>
  </h2>

  <div id="message" class="updated below-h2" style="display:none;"><p></p></div>

  <?php
  if (isset($_GET['type']) && $_GET['type'] == "add") {

    if (isset($_GET['id']) && $_GET['id'] && is_numeric($_GET['id'])) {
      $_current_template = $this->wpdb->get_results("SELECT * FROM {$this->wpdb->prefix}inic_testimonial_template WHERE id='{$_GET['id']}'");
      $_current_template_array = array();
      foreach($_current_template[0] as $key=>$val) {
        $_current_template_array[$key] = $val;
      }
      extract($_current_template_array);
    }
    ?>

    <form name="testimonial_listing_template" method="post" action="">
      <?php wp_nonce_field('save_testimonial_listing_template','_nonce'); ?>
      <input type="hidden" name="action" value="iNIC_testimonial_save_listing_template" />

      <?php
      if ($id) {
        echo "<input type=\"hidden\" name=\"id\" value=\"{$id}\" />";
      }
      ?>

      <table class="form-table">
        <tbody>

          <tr>
            <th><label for="title">Title</label></th>
            <td><input name="title" type="text" id="title" value="<?php echo esc_attr($title); ?>" class="regular-text"></td>
          </tr>


          <tr>
            <th><label for="no_of_testimonial">No. of Testimonials</label></th>
            <td><input name="no_of_testimonial" type="text" id="no_of_testimonial" value="<?php echo esc_attr($no_of_testimonial); ?>" class="small-text"></td>
          </tr>

          <tr>
            <th><label for="list_per_page">Listing Testimonials Per Page</label></th>
            <td><input name="list_per_page" type="text" id="list_per_page" value="<?php echo esc_attr($list_per_page); ?>" class="small-text"></td>
          </tr>

          <tr>
            <th><label for="ord_by">Order By</label></th>
            <td>
              <?php $_ord_by = $ord_by ? explode(" ", $ord_by) : array("id", "ASC"); ?>
              <select name="ord_by">
                <?php
                $_table_field = $this->wpdb->get_results("SHOW COLUMNS FROM {$this->wpdb->prefix}inic_testimonial");
                foreach ($_table_field as $_table_field) {
                  $_is_selected = $_ord_by[0] == $_table_field->Field ? ' selected="selected"' : '';
                  echo "<option value=\"".  esc_attr($_table_field->Field)."\"{$_is_selected}>{$_table_field->Field}</option>";
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
            <th><label for="filter_by_country">Filter by Country</label></th>
            <td><input name="filter_by_country" type="text" id="filter_by_country" value="<?php echo esc_attr($filter_by_country); ?>" class="regular-text"> Comma Separated. <code>country1,country2,country3</code></td>
          </tr>

          <tr>
            <th><label for="filter_by_tags">Filter by Tags</label></th>
            <td><input name="filter_by_tags" type="text" id="filter_by_tags" value="<?php echo esc_attr($filter_by_tags); ?>" class="regular-text"> Comma Separated. <code>tag1,tag2,tag3</code></td>
          </tr>

          <tr>
            <th><label for="custom_query">Custom SQL Where condition</label></th>
            <td><input name="custom_query" type="text" id="custom_query" value="<?php echo esc_attr($custom_query); ?>" class="regular-text">LIKE <code>feature='1' AND city != ''</code></td>
          </tr>

          <tr>
            <th><label for="show_featured_at">Show Featured at</label></th>
            <td>
              <select name="show_featured_at">
                <option value="no"<?php echo $show_featured_at == "no" ? ' selected="selected"' : ''; ?>>No</option>
                <option value="top"<?php echo $show_featured_at == "top" ? ' selected="selected"' : ''; ?>>Top</option>
                <option value="bottom"<?php echo $show_featured_at == "bottom" ? ' selected="selected"' : ''; ?>>Bottom</option>
              </select>
            </td>
          </tr>

          <tr class="hidewhennotfeatured">
            <th><label for="no_of_featured">No. of Featured</label></th>
            <td><input name="no_of_featured" type="text" id="no_of_featured" value="<?php echo $no_of_featured ? esc_attr($no_of_featured) : 1; ?>" class="small-text"></td>
          </tr>

          <tr class="hidewhennotfeatured">
            <th><label for="featured_template">Featured HTML Template</label></th>
            <td><?php wp_editor(esc_attr(stripslashes($featured_template)), 'featured_template', array('media_buttons' => false, 'textarea_rows' => 10, 'tinymce' => false)); ?></td>
          </tr>

          <tr>
            <th><label for="listing_template_odd">Listing HTML Template (Odd)</label></th>
            <td><?php wp_editor(esc_attr(stripslashes($listing_template_odd)), 'listing_template_odd', array('media_buttons' => false, 'textarea_rows' => 10, 'tinymce' => false)); ?></td>
          </tr>

          <tr>
            <th><label for="listing_template_even">Listing HTML Template (Even)</label></th>
            <td><?php wp_editor(esc_attr(stripslashes($listing_template_even)), 'listing_template_even', array('media_buttons' => false, 'textarea_rows' => 10, 'tinymce' => false)); ?></td>
          </tr>

        </tbody>
      </table>
      <p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php echo $id ? "Update Template" : "Add Template"; ?>"></p>

    </form>

    <script type="text/javascript" >
      jQuery(document).ready(function($) {
          
        jQuery("select[name=show_featured_at]").change(function(){
          if(jQuery(this).val() == 'no') {
            jQuery("tr.hidewhennotfeatured").hide();
          } else {
            jQuery("tr.hidewhennotfeatured").show();
          }
        });
        jQuery("select[name=show_featured_at]").trigger('change');
          
        jQuery("form[name=testimonial_listing_template]").submit(function(e){
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
          <th>No. of Testimonials</th>
          <th>Testimonials Per Page</th>
          <th>Order By</th>
          <th>Filter by</th>
          <th>Show Featured at / No. of Featured</th>
        </tr>
      </thead>

      <tbody>
        <?php
        $_results = $this->wpdb->get_results("SELECT * FROM {$this->wpdb->prefix}inic_testimonial_template");
        if ($_results) {
          foreach ($_results as $_results) {
            $_no_of_featured = ($_results->show_featured_at == 'no') ? "" : " / {$_results->no_of_featured}";
            $_filter_by = false;
            
            if($_results->filter_by_country) {
              $_filter_by .= "Country: <i>{$_results->filter_by_country}</i><br />";
            }
            
            if($_results->filter_by_tags) {
              $_filter_by .= "Tags: <i>{$_results->filter_by_tags}</i><br />";
            }
            
            if($_results->custom_query) {
              $_filter_by .= "Sql: <i>{$_results->custom_query}</i><br />";
            }
            
            echo "<tr>
                    <td>
                      ".  esc_html($_results->title)."<br /><i>[iNICtestimonial tpl=".  esc_html($_results->id)."]</i>
                      <div class=\"row-actions\">
                        <span class=\"edit\"><a href=\"admin.php?page=inic_testimonial_listing_template&type=add&id=".  esc_attr($_results->id)."\" title=\"Edit this item\">Edit</a> | </span>
                        <span class=\"trash\"><a class=\"submitdelete\" title=\"Delete this item\" href=\"javascript:void(0)\" rel=\"".  esc_attr($_results->id)."\">Delete</a></span>
                      </div>
                    </td>
                    <td>".  esc_html($_results->no_of_testimonial)."</td>
                    <td>".  esc_html($_results->list_per_page)."</td>
                    <td>".  esc_html($_results->ord_by)."</td>
                    <td>".  esc_html($_filter_by)."</td>
                    <td>".  esc_html($_results->show_featured_at)." ".  esc_html($_no_of_featured)."</td>
                  </tr>";
          }
        } else {
          echo "<tr><td colspan=\"6\">No Testimonial Template Found <a href='admin.php?page=inic_testimonial_listing_template&type=add'>Click here</a> to Create.</td></tr>";
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
              
            jQuery.post(ajaxurl, {action:"iNIC_testimonial_delete_listing_template", id:jQuery(this).attr('rel')}, function(data){
                
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
