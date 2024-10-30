<?php

class iNIC_TestimonialWidget extends WP_Widget {

  var $wpdb = false;

  public function __construct() {
    parent::__construct('iNIC_testimonial_widget', 'IndiaNIC Testimonial Widget', array('description' => __('Use this widget to add one of your IndiaNIC Testimonial as a widget.', 'text_domain')));

    global $wpdb;
    $this->wpdb = $wpdb;
  }

  public function form($instance) {

    if (isset($instance['title'])) {
      $title = $instance['title'];
      $widget_template_id = $instance['widget_template_id'];
    } else {
      $title = __('Widget Title', 'text_domain');
      $widget_template_id = false;
    }
    ?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
      <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
    </p>

    <p>
      <label for="<?php echo esc_attr($this->get_field_id('widget_template_id')); ?>"><?php _e('Select Widget Template:'); ?></label> 
      <select class="widefat" id="<?php echo esc_attr($this->get_field_id('widget_template_id')); ?>" name="<?php echo esc_attr($this->get_field_name('widget_template_id')); ?>">
        <?php
        $available_widget_tpl = $this->wpdb->get_results("SELECT * FROM {$this->wpdb->prefix}inic_testimonial_widget");
        if ($available_widget_tpl) {
          foreach ($available_widget_tpl as $available_widget_tpl) {
            $_selected = $widget_template_id == $available_widget_tpl->id ? ' selected="selected"' : '';
            echo "<option value=\"".  esc_attr($available_widget_tpl->id)."\"{$_selected}>".  esc_html($available_widget_tpl->title)."</option>";
          }
        } else {
          echo "<option value=\"\">Please Create first widget template.</option>";
        }
        ?>
      </select>
    </p>
    <?php
  }

  public function update($new_instance, $old_instance) {
    /* $instance = array();
      $instance['title'] = strip_tags($new_instance['title']);
     */
    return $new_instance;
  }

  public function widget($args, $instance) {

    extract($args);
    $_template_id = $instance['widget_template_id'];

    $_template_data = $this->wpdb->get_results("SELECT * FROM {$this->wpdb->prefix}inic_testimonial_widget WHERE id='{$_template_id}'");
    if ($_template_data) {
      $title = apply_filters('widget_title', $instance['title']);

      echo $before_widget;
      if (!empty($title))
        echo $before_title . $title . $after_title;

      $no_of_testimonial = $_template_data[0]->no_of_testimonial ? " LIMIT {$_template_data[0]->no_of_testimonial}" : "";
      $only_featured = $_template_data[0]->only_featured ? " is_featured='1'" : false;
      $display_randomly = $_template_data[0]->display_randomly ? " ORDER BY RAND()" : "";
      
      $filter_by_country = false;
      if($_template_data[0]->filter_by_country) {
        $filter_by_country = explode(",", $_template_data[0]->filter_by_country);
        $filter_by_country = implode("', '", $filter_by_country);
        $filter_by_country = " country IN('$filter_by_country')";
      }
      
      $filter_by_tags = false;
      if($_template_data[0]->filter_by_tags) {
        $filter_by_tags = explode(",", $_template_data[0]->filter_by_tags);
        $filter_by_tag = "(";
        foreach($filter_by_tags as $_current_tag) {
          $filter_by_tag .= "tags LIKE '%{$_current_tag}%' OR ";
        }
        $filter_by_tag = rtrim($filter_by_tag, " OR ");
        $filter_by_tag .= ")";
        $filter_by_tags = $filter_by_tag;
      }
      $html_template = $_template_data[0]->html_template;

      $_where = "";
      if ($only_featured || $filter_by_country || $filter_by_tags) {
        $_where = " WHERE 1=1";
        $_where .= $only_featured ? " AND {$only_featured}" : "";
        $_where .= $filter_by_country ? " AND {$filter_by_country}" : "";
        $_where .= $filter_by_tags ? " AND {$filter_by_tags}" : "";
      }
      
      $_testimonial_listing = $this->wpdb->get_results("SELECT * FROM {$this->wpdb->prefix}inic_testimonial{$_where}{$display_randomly}{$no_of_testimonial}");
      if ($_testimonial_listing) {
        foreach ($_testimonial_listing as $_testimonial_listing) {
          $_search = array("{#ID}", "{#ProjectName}", "{#ProjectUrl}", "{#ClientName}", "{#City}", "{#State}", "{#Country}", "{#Description}", "{#Tags}", "{#VideoUrl}", "{#ThumbImgUrl}", "{#LargeImgUrl}", "{#Counter}");
          $_replace = array(esc_html($_testimonial_listing->id), esc_html($_testimonial_listing->project_name), esc_html($_testimonial_listing->project_url), esc_html($_testimonial_listing->client_name), esc_html($_testimonial_listing->city), esc_html($_testimonial_listing->state), esc_html($_testimonial_listing->country), esc_html($_testimonial_listing->description), esc_html($_testimonial_listing->tags), esc_html($_testimonial_listing->video_url), esc_html($_testimonial_listing->thumb_img_url), esc_html($_testimonial_listing->large_img_url), $i);

          $_code_value = array("{#ID}" => esc_html($_testimonial_listing->id), "{#ProjectName}" => esc_html($_testimonial_listing->project_name), "{#ProjectUrl}" => esc_html($_testimonial_listing->project_url), "{#ClientName}" => esc_html($_testimonial_listing->client_name), "{#City}" => esc_html($_testimonial_listing->city), "{#State}" => esc_html($_testimonial_listing->state), "{#Country}" => esc_html($_testimonial_listing->country), "{#Description}" => esc_html($_testimonial_listing->description), "{#Tags}" => esc_html($_testimonial_listing->tags), "{#VideoUrl}" => esc_html($_testimonial_listing->video_url), "{#ThumbImgUrl}" => esc_html($_testimonial_listing->thumb_img_url), "{#LargeImgUrl}" => esc_html($_testimonial_listing->large_img_url), "{#Counter}" => $i);
          $inic_testimonial_html_template = $html_template;
          preg_match('#\[IF\s(.+?)](.+?)\[/IF]#s', $inic_testimonial_html_template, $matches_if);
          if (!empty($matches_if)) {

            if ($_code_value[$matches_if[1]]) {
              $inic_testimonial_html_template = str_replace($matches_if[0], $matches_if[2], $inic_testimonial_html_template);
            } else {
              $inic_testimonial_html_template = str_replace($matches_if[0], "", $inic_testimonial_html_template);
            }
          }
          echo stripslashes(str_replace($_search, $_replace, $inic_testimonial_html_template));
        }
      }

      echo $after_widget;
    }
  }

}
?>
