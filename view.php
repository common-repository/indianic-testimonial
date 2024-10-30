<?php

if (isset($_GET['id']) && $_GET['id'] && isset($_GET['action']) && $_GET['action'] == 'delete') {
  $this->wpdb->delete("{$this->wpdb->prefix}inic_testimonial", array('id' => $_GET['id']));
  if ($this->wpdb->rows_affected) {
    $_data['updated'] = "The testimonial has been deleted successfully.";
  }
}
    
$_ord_by = get_option("inic_testimonial_list_ord_by") ? " ORDER BY " . esc_html(get_option("inic_testimonial_list_ord_by")) : "";
$_result_per_page = esc_attr(get_option("inic_testimonial_admin_list_per_page"));
?>

<div class="wrap">
  <div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
  <h2>View Testimonials test</h2>

  <?php
  if(isset($_data) && is_array($_data)) {
    foreach($_data as $_message_type => $_message) {
      echo '<div id="message" class="'.  esc_attr($_message_type).' below-h2"><p>'.$_message.'</p></div>';
    }
  }
  ?>

  <div class="col-wrap">
    <div class="form-wrap">
      <?php
      if($_result_per_page > 0) {
        $this->tbl->row_per_page = $_result_per_page;
      }
      $this->tbl->set_mysql_query("SELECT * FROM {$this->wpdb->prefix}inic_testimonial{$_ord_by}");
      $this->tbl->set_mysql_search_query("SELECT * FROM {$this->wpdb->prefix}inic_testimonial WHERE project_name LIKE '%{#S}%' || client_name LIKE '%{#S}%' || city LIKE '%{#S}%' || state LIKE '%{#S}%' || country LIKE '%{#S}%'{$_ord_by}");

      $this->tbl->add_col('project_name', "Project Name", true);
      $this->tbl->display_col_function(function($item, $column_name) {
                $item['is_featured'] = $item['is_featured'] ? "<br /><code style=\"color:#C00;\"> Featured </code>" : "";
                return "<a href=\"".  esc_attr($item['project_url'])."\" target=\"blank\">".  esc_html($item['project_name'])."</a><br /><i>[ ".  esc_html($item['tags'])." ]{$item['is_featured']}</i>";
              });

      $this->tbl->add_col('client_name', "Client Name", true);
      $this->tbl->display_col_function(function($item, $column_name) {
                $item['city'] = $item['city'] ? "<br /><i>[ City: ".  esc_html($item['city'])." ]</i>" : "";
                $item['state'] = $item['state'] ? "<br /><i>[ State: ".  esc_html($item['state'])." ]</i>" : "";
                $item['country'] = $item['country'] ? "<br /><i>[ Country: ".  esc_html($item['country'])." ]</i>" : "";
                return esc_html($item['client_name']) .  "{$item['city']}{$item['state']}{$item['country']}";
              });

      $this->tbl->add_col_action('Edit', array('id' => false, 'url' => '?page=inic_testimonial_add'));
      $this->tbl->add_col_action('Delete', array('action' => 'delete', 'id' => false));

      $this->tbl->add_col('description', "Description");

      $this->tbl->add_col('video_url', "Video URL");

      $this->tbl->add_col('thumb_img_url', "Thumb Image");
      $this->tbl->display_col_function(function($item, $column_name) {
                return $item['thumb_img_url'] ? "<a href=\"".  esc_attr($item['large_img_url'])."\" target=\"_blank\"><img src=\"".  esc_attr($item['thumb_img_url'])."\" width=\"100\" height=\"100\" /></a>" : "";
              });


      $this->tbl->rander();
      ?>

    </div>
  </div>


</div>