<?php

/*
  Plugin Name: Testimonials
  Plugin URI: http://wordpress.org/extend/plugins/indianic-testimonial/
  Description: Add testimonial to your blog posts.
  Author: IndiaNIC
  Version: 2.3
  Author URI: http://profiles.wordpress.org/indianic
 */

class iNIC_Testimonial {

  var $pluginPath;
  var $pluginUrl;
  var $rootPath;
  var $wpdb;
  var $blog_page_pagination_key = "testimonial_page";

  public function __construct() {

    global $wpdb;
    $this->wpdb = $wpdb;
    $this->ds = DIRECTORY_SEPARATOR;
    $this->pluginPath = dirname(__FILE__) . $this->ds;
    $this->rootPath = dirname(dirname(dirname(dirname(__FILE__))));
    $this->pluginUrl = WP_PLUGIN_URL . '/indianic-testimonial/';

    add_action('admin_menu', array($this, 'testimonial_register_menu'));
    add_shortcode('iNICtestimonial', array($this, 'shortcode'));
    add_action('wp_ajax_iNIC_testimonial_save', array($this, 'iNIC_testimonial_save'));
    add_action('wp_ajax_iNIC_testimonial_save_setting', array($this, 'iNIC_testimonial_save_setting'));
    add_action('wp_ajax_iNIC_testimonial_save_widget', array($this, 'iNIC_testimonial_save_widget'));
    add_action('wp_ajax_iNIC_testimonial_delete_widget', array($this, 'iNIC_testimonial_delete_widget'));
    add_action('wp_ajax_iNIC_testimonial_save_listing_template', array($this, 'iNIC_testimonial_save_listing_template'));
    add_action('wp_ajax_iNIC_testimonial_delete_listing_template', array($this, 'iNIC_testimonial_delete_listing_template'));

    if (isset($_GET['page']) && $_GET['page'] == 'inic_testimonial_add') {
      add_action('admin_print_scripts', array($this, 'wp_gear_manager_admin_scripts'));
      add_action('admin_print_styles', array($this, 'wp_gear_manager_admin_styles'));
      add_action('admin_head', array($this, 'include_js'));
    }
  }

  function get_template_data($_data, $_tpl_data = false) {

    $_data->counter = isset($_data->counter) ? $_data->counter : 0;
    $_tpl_data = $_tpl_data ? $_tpl_data : get_option("inic_testimonial_html_template");

    $_search = array("{#ID}", "{#ProjectName}", "{#ProjectUrl}", "{#ClientName}", "{#City}", "{#State}", "{#Country}", "{#Description}", "{#Tags}", "{#VideoUrl}", "{#ThumbImgUrl}", "{#LargeImgUrl}", "{#Counter}");
    $_replace = array(esc_html($_data->id), esc_html($_data->project_name), esc_html($_data->project_url), esc_html($_data->client_name), esc_html($_data->city), esc_html($_data->state), esc_html($_data->country), esc_html($_data->description), esc_html($_data->tags), esc_html($_data->video_url), esc_html($_data->thumb_img_url), esc_html($_data->large_img_url), esc_html($_data->counter));
    $_code_value = array("{#ID}" => esc_html($_data->id), "{#ProjectName}" => esc_html($_data->project_name), "{#ProjectUrl}" => esc_html($_data->project_url), "{#ClientName}" => esc_html($_data->client_name), "{#City}" => esc_html($_data->city), "{#State}" => esc_html($_data->state), "{#Country}" => esc_html($_data->country), "{#Description}" => esc_html($_data->description), "{#Tags}" => esc_html($_data->tags), "{#VideoUrl}" => esc_html($_data->video_url), "{#ThumbImgUrl}" => esc_html($_data->thumb_img_url), "{#LargeImgUrl}" => esc_html($_data->large_img_url), "{#Counter}" => esc_html($_data->counter));

    preg_match('#\[IF\s(.+?)](.+?)\[/IF]#s', $_tpl_data, $matches_if);
    if (!empty($matches_if)) {

      if ($_code_value[$matches_if[1]]) {
        $_tpl_data = str_replace($matches_if[0], $matches_if[2], $_tpl_data);
      } else {
        $_tpl_data = str_replace($matches_if[0], "", $_tpl_data);
      }
    }

    return stripslashes(str_replace($_search, $_replace, $_tpl_data));
  }

  public function shortcode($atts) {

    $_testimonial_html = "";
    $_current_featured_testimonial_id = array(0);
    $_template_data = $this->wpdb->get_results("SELECT * FROM {$this->wpdb->prefix}inic_testimonial_template WHERE id='{$atts['tpl']}'");

    if ($_template_data) {
      $atts['id'] = $atts['tpl'];
      $_template_data_array = array();
      foreach ($_template_data[0] as $key => $val) {
        $_template_data_array[$key] = $val;
      }

      $_template_data = array_merge($_template_data_array, $atts);
      $_template_data['listing_template_even'] = $_template_data['listing_template_even'] ? $_template_data['listing_template_even'] : $_template_data['listing_template_odd'];


      $_featured_testimonials = false;
      if ($_template_data['show_featured_at'] != 'no' && $_template_data['featured_template']) {

        $_featured_testimonials_data = $this->wpdb->get_results("SELECT * FROM {$this->wpdb->prefix}inic_testimonial WHERE is_featured='1' ORDER BY RAND() LIMIT {$_template_data['no_of_featured']}");
        if ($_featured_testimonials_data) {

          $i = 0;
          foreach ($_featured_testimonials_data as $_featured_testimonials_data) {
            $_featured_testimonials_data->counter = $i;
            array_push($_current_featured_testimonial_id, $_featured_testimonials_data->id);
            $_featured_testimonials .= $this->get_template_data($_featured_testimonials_data, $_template_data['featured_template']);
            ++$i;
          }
        }
      }

      if ($_template_data['show_featured_at'] == 'top') {
        $_testimonial_html .= $_featured_testimonials;
      }

      $filter_by = false;
      if ($_template_data['filter_by_country']) {
        $filter_by_country = explode(",", $_template_data['filter_by_country']);
        $filter_by_country = implode("', '", $filter_by_country);
        $filter_by = " AND country IN('$filter_by_country')";
      }

      if ($_template_data['filter_by_tags']) {
        $filter_by_tags = explode(",", $_template_data['filter_by_tags']);
        $filter_by .= " AND (";
        foreach ($filter_by_tags as $_current_tag) {
          $filter_by .= "tags LIKE '%{$_current_tag}%' OR ";
        }
        $filter_by = rtrim($filter_by_tag, " OR ");
        $filter_by .= ")";
        $filter_by = $filter_by_tag;
      }

      if ($_template_data['custom_query']) {
        $filter_by = " AND ({$_template_data['custom_query']})";
      }

      $_no_of_testimonial = $_template_data['no_of_testimonial'] ? " LIMIT {$_template_data['no_of_testimonial']}" : false;
      $_testimonial_result = $this->wpdb->get_results("SELECT * FROM {$this->wpdb->prefix}inic_testimonial WHERE (id NOT IN(" . implode(",", $_current_featured_testimonial_id) . ")){$filter_by} ORDER BY {$_template_data['ord_by']}{$_no_of_testimonial}");
      if (!$_no_of_testimonial || $_template_data['list_per_page']) {

        $_record_per_page = $_template_data['list_per_page'] ? $_template_data['list_per_page'] : 20;
        $_array_chunk = array_chunk($_testimonial_result, $_record_per_page);
        $_total_pages = count($_array_chunk);
        $_current_page = (isset($_GET[$this->blog_page_pagination_key]) && $_GET[$this->blog_page_pagination_key] > 0) ? $_GET[$this->blog_page_pagination_key] : 1;
        $_current_page = $_total_pages < $_current_page ? $_total_pages : $_current_page;

        $_testimonial_result = isset($_array_chunk[$_current_page - 1]) && $_array_chunk[$_current_page - 1] ? $_array_chunk[$_current_page - 1] : $_array_chunk[0];
        $_pagination = "";

        if ($_total_pages > 1) {
          $_pagination .= "<div class='testimonial_pagination'>";

          if ($_current_page > 1) {
            $_pagination .= "<a href=\"?" . build_query(array_merge($_REQUEST, array($this->blog_page_pagination_key => 1))) . "\" title=\"Go to the first page\" class=\"first-page\">&laquo;</a> ";
            $_pagination .= "<a href=\"?" . build_query(array_merge($_REQUEST, array($this->blog_page_pagination_key => ($_current_page - 1)))) . "\" title=\"Go to the previous page\" class=\"prev-page\">&lsaquo;</a> ";
          }

          for ($i = 1; $i <= $_total_pages; ++$i) {
            $_url_prefix = build_query(array_merge($_REQUEST, array($this->blog_page_pagination_key => $i)));
            $_is_active = $_current_page == $i ? ' class="current-page"' : '';
            $_pagination .= "<a href=\"?{$_url_prefix}\"{$_is_active}>{$i}</a> ";
          }

          if ($_current_page < $_total_pages) {
            $_pagination .= "<a href=\"?" . build_query(array_merge($_REQUEST, array($this->blog_page_pagination_key => ($_current_page + 1)))) . "\" title=\"Go to the next page\" class=\"next-page\">&rsaquo;</a> ";
            $_pagination .= "<a href=\"?" . build_query(array_merge($_REQUEST, array($this->blog_page_pagination_key => $_total_pages))) . "\" title=\"Go to the last page\" class=\"last-page\">&raquo;</a> ";
          }

          $_pagination .= "</div>";
        }
      }

      if ($_testimonial_result) {
        $i = 0;
        foreach ($_testimonial_result as $_testimonial_result) {
          $_testimonial_result->counter = $i;

          if ($i % 2 == 0) {
            $_testimonial_html .= $this->get_template_data($_testimonial_result, $_template_data['listing_template_odd']);
          } else {
            $_testimonial_html .= $this->get_template_data($_testimonial_result, $_template_data['listing_template_even']);
          }
          ++$i;
        }
        $_testimonial_html .= $_pagination;
      }

      if ($_template_data['show_featured_at'] == 'bottom') {
        $_testimonial_html .= $_featured_testimonials;
      }
    } else {
      $_testimonials = $this->wpdb->get_results("SELECT * FROM {$this->wpdb->prefix}inic_testimonial");
      if ($_testimonials) {
        $i = 0;
        foreach ($_testimonials as $_testimonials) {
          $_testimonials->counter = $i;
          $_testimonial_html .= $this->get_template_data($_testimonials);
          ++$i;
        }
      }
    }

    return $_testimonial_html;
  }

  public function iNIC_testimonial_save() {

    if (!wp_verify_nonce($_POST['_nonce'], 'save_testimonial')) {
      echo json_encode(array('error' => "Sorry, your nonce did not verify."));
      die();
    }

    $_search = array("http://", "https://");
    $_replace = array("", "");

    $_data = array(
        "project_name" => esc_html($this->clean_text($_POST['project_name'])),
        "project_url" => esc_html($this->clean_text($_POST['project_url'])),
        "client_name" => esc_html($this->clean_text($_POST['client_name'])),
        "city" => esc_html($this->clean_text($_POST['client_city'])),
        "state" => esc_html($this->clean_text($_POST['client_state'])),
        "country" => esc_html($this->clean_text($_POST['client_country'])),
        "description" => esc_html($this->clean_text($_POST['description'])),
        "tags" => esc_html($this->clean_text($_POST['tags'])),
        "video_url" => esc_html($this->clean_text($_POST['video_url'])),
        "thumb_img_url" => str_replace($_SERVER['HTTP_HOST'], "", str_replace($_search, $_replace, esc_html($this->clean_text($_POST['thumb_img'])))),
        "large_img_url" => str_replace($_SERVER['HTTP_HOST'], "", str_replace($_search, $_replace, esc_html($this->clean_text($_POST['large_img'])))),
        "is_featured" => esc_html($this->clean_text($_POST['is_featured']))
    );

    if (isset($_POST['id']) && $_POST['id']) {
      $this->wpdb->update("{$this->wpdb->prefix}inic_testimonial", $_data, array("id" => $_POST['id']));
    } else {
      $this->wpdb->insert("{$this->wpdb->prefix}inic_testimonial", $_data);
    }

    if (mysql_error()) {
      $data['error'] = mysql_error();
    } else {
      if ($this->wpdb->insert_id) {
        $data['form_reset'] = true;
      }
      $data['msg'] = "The testimonial has been saved successfully.";
    }

    echo json_encode($data);
    die();
  }

  public function iNIC_testimonial_save_listing_template() {

    if ($_POST['action'] == 'iNIC_testimonial_save_listing_template') {

      if (!wp_verify_nonce($_POST['_nonce'], 'save_testimonial_listing_template')) {
        echo json_encode(array('error' => "Sorry, your nonce did not verify."));
        die();
      }

      if ($_POST['title'] && $_POST['listing_template_odd']) {
        $_POST['ord_by'] = "{$_POST['ord_by']} {$_POST['ord_type']}";
        $_POST['no_of_featured'] = $_POST['no_of_featured'] ? $_POST['no_of_featured'] : "1";
        $_POST['filter_by_country'] = $_POST['filter_by_country'] ? str_replace(", ", ",", $this->clean_text($_POST['filter_by_country'])) : "";
        $_POST['filter_by_tags'] = str_replace(", ", ",", $_POST['filter_by_tags']);
        $_POST['custom_query'] = $_POST['custom_query'] ? str_replace("WHERE ", "", stripslashes($this->clean_text($_POST['custom_query']))) : "";
        $_POST['featured_template'] = $this->clean_text(stripslashes($_POST['featured_template']));
        $_POST['listing_template_odd'] = $this->clean_text(stripslashes($_POST['listing_template_odd']));
        $_POST['listing_template_even'] = $this->clean_text(stripslashes($_POST['listing_template_even']));

        unset($_POST['action'], $_POST['ord_type'], $_POST['_nonce'], $_POST['_wp_http_referer']);

        if (isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id']) {
          $this->wpdb->update("{$this->wpdb->prefix}inic_testimonial_template", $_POST, array("id" => $_POST['id']));
          if (mysql_error()) {
            $data['error'] = mysql_error();
          } else {
            $data['success'] = "The Testimonial template has been updated successfully.";
          }
        } else {
          if ($this->wpdb->insert("{$this->wpdb->prefix}inic_testimonial_template", $_POST)) {
            $data['success'] = "The Testimonial template has been added successfully.";
          } else {
            $data['error'] = mysql_error();
          }
        }
      } else {
        $data['error'] = "<strong>Title</strong> and <strong>HTML Template (Odd)</strong> must be required.";
      }


      echo json_encode($data);
      die();
    }
  }

  public function iNIC_testimonial_delete_listing_template() {

    if ($_POST['action'] == "iNIC_testimonial_delete_listing_template" && $_POST['id']) {
      $this->wpdb->delete("{$this->wpdb->prefix}inic_testimonial_template", array('id' => $_POST['id']));
      if (mysql_error()) {
        $data['error'] = mysql_error();
      } else {
        $data['success'] = "The Testimonial template has been deleted successfully.";
      }
      echo json_encode($data);
    }
    die();
  }

  public function iNIC_testimonial_delete_widget() {
    if (isset($_POST['id']) && $_POST['id']) {

      $this->wpdb->delete("{$this->wpdb->prefix}inic_testimonial_widget", array('id' => $_POST['id']));
      if (mysql_error()) {
        $data['error'] = mysql_error();
      } else {
        $data['success'] = "The testimonial widget template has been delete successfully.";
      }
      echo json_encode($data);
    }
    die();
  }

  public function iNIC_testimonial_save_widget() {
    if ($_POST['action'] == "iNIC_testimonial_save_widget") {

      if (!wp_verify_nonce($_POST['_nonce'], 'save_testimonial_widget_template')) {
        echo json_encode(array('error' => "Sorry, your nonce did not verify."));
        die();
      }

      if ($_POST['widget_title']) {
        $_POST['no_of_testimonials'] = $_POST['no_of_testimonials'] && is_numeric($_POST['no_of_testimonials']) ? $_POST['no_of_testimonials'] : "";
        $_POST['filter_by_country'] = str_replace(", ", ",", $_POST['filter_by_country']);
        $_POST['filter_by_tags'] = str_replace(", ", ",", $_POST['filter_by_tags']);
        $_POST['list_only_featured_testimonials'] = isset($_POST['list_only_featured_testimonials']) && $_POST['list_only_featured_testimonials'] ? $_POST['list_only_featured_testimonials'] : "0";

        $_data = array(
            "title" => esc_html($this->clean_text($_POST['widget_title'])),
            "no_of_testimonial" => $this->clean_text($_POST['no_of_testimonials']),
            "only_featured" => $this->clean_text($_POST['list_only_featured_testimonials']),
            "filter_by_country" => $this->clean_text($_POST['filter_by_country']),
            "filter_by_tags" => $this->clean_text($_POST['filter_by_tags']),
            "display_randomly" => $this->clean_text($_POST['display_randomly']),
            "html_template" => $this->clean_text($_POST['widget_template']),
        );

        if (isset($_POST['id']) && $_POST['id']) {
          $this->wpdb->update("{$this->wpdb->prefix}inic_testimonial_widget", $_data, array('id' => $_POST['id']));
          $_success_msg = "The testimonial widget template has been updated successfully.";
        } else {
          $this->wpdb->insert("{$this->wpdb->prefix}inic_testimonial_widget", $_data);
          $_success_msg = "The testimonial widget template has been added successfully.";
        }

        if (mysql_error()) {
          $data['error'] = mysql_error();
        } else {
          $data['success'] = $_success_msg;
        }
      } else {
        $data['error'] = "Widget title must be required.";
      }
      echo json_encode($data);
      die();
    }
  }

  public function iNIC_testimonial_save_setting() {

    if (!wp_verify_nonce($_POST['_nonce'], 'save_testimonial_options')) {
      echo json_encode(array('error' => "Sorry, your nonce did not verify."));
      die();
    }

    $_POST['inic_testimonial_admin_list_per_page'] = (is_numeric($_POST['inic_testimonial_admin_list_per_page']) && $_POST['inic_testimonial_admin_list_per_page'] > 0) ? $_POST['inic_testimonial_admin_list_per_page'] : "10";
    update_option('inic_testimonial_admin_list_per_page', is_numeric($_POST['inic_testimonial_admin_list_per_page']) ? $_POST['inic_testimonial_admin_list_per_page'] : 15);
    update_option('inic_testimonial_html_template', $this->clean_text($_POST['inic_testimonial_html_template']));
    update_option("inic_testimonial_list_ord_by", "{$_POST['ord_by']} {$_POST['ord_type']}");
    $_data['msg'] = "IndiaNIC Testimonial Setting has been saved successfully.";
    echo json_encode($_data);
    die();
  }

  public function testimonial_register_menu() {
    add_menu_page('Testimonial', 'Testimonial', 'administrator', "inic_testimonial_view", array($this, 'testimonial_view'), $this->pluginUrl . "icon.png");
    add_submenu_page("inic_testimonial_view", "All Testimonial", "All Testimonial", 'administrator', "inic_testimonial_view", array($this, 'testimonial_view'));
    add_submenu_page("inic_testimonial_view", "Add Testimonial", "Add Testimonial", 'administrator', "inic_testimonial_add", array($this, 'testimonial_add'));
    add_submenu_page("inic_testimonial_view", "Listing Template", "Listing Template", 'administrator', "inic_testimonial_listing_template", array($this, 'inic_testimonial_listing_template'));
    add_submenu_page("inic_testimonial_view", "Widget Template", "Widget Template", 'administrator', "inic_testimonial_widget_template", array($this, 'inic_testimonial_widget_template'));
    add_submenu_page("inic_testimonial_view", "Settings", "Settings", 'administrator', "inic_testimonial_settings", array($this, 'testimonial_settings'));

    require_once "{$this->pluginPath}/listing_data_table.php";
    $this->tbl = new listing_data_table();
  }

  public function testimonial_view() {
    require($this->pluginPath . "view.php");
  }

  public function testimonial_add() {
    require($this->pluginPath . "add.php");
  }

  public function inic_testimonial_listing_template() {
    require($this->pluginPath . "listing_template.php");
  }

  public function inic_testimonial_widget_template() {
    require($this->pluginPath . "widget_template.php");
  }

  public function testimonial_settings() {
    require($this->pluginPath . "settings.php");
  }

  function include_js() {
    echo "<script type='text/javascript'>
      jQuery(document).ready(function(){
        jQuery('div.iNICfaqsUploader').each(function(){
          var uploader_id = jQuery(this).attr('id');
          if(uploader_id != undefined) {
          
              var uploader_btn_name = jQuery(this).attr('name') ? jQuery(this).attr('name') : 'Upload Image';
              var uploader_old_file = jQuery(this).attr('value') ? jQuery(this).attr('value') : '';
              
              jQuery(this).html('<div id=\"'+uploader_id+'_img_src\" style=\"width:100px; height:100px; display:none; vertical-align:middle; border: 2px solid #BBB; border-radius:10px;\"></div><input type=\"hidden\" name=\"'+uploader_id+'\" id=\"'+uploader_id+'_field\" value=\"'+uploader_old_file+'\" /><input id=\"'+uploader_id+'_btn\" type=\"button\" value=\"'+uploader_btn_name+'\" class=\"button-secondary\" />');
            
              jQuery('#'+uploader_id+'_btn').click(function() {
                formfield = uploader_id;
                tb_show('', 'media-upload.php?type=image&TB_iframe=true');
                
                window.send_to_editor = function(html) {
                  imgurl = jQuery('img',html).attr('src');
                  jQuery('#'+uploader_id+'_field').val(imgurl);
                  jQuery('#'+uploader_id+'_field').trigger('change');
                  tb_remove();
                }                
                return false;
              });
              
              jQuery('#'+uploader_id+'_field').change(function(){
                  var _current_img = jQuery('#'+uploader_id+'_field').val();
                  if(_current_img.length > 0) {
                    jQuery('#'+uploader_id+'_img_src').css('display', 'block');
                    jQuery('#'+uploader_id+'_img_src').html('<img src=\"'+_current_img+'\" style=\"width:98%; height:98%; cursor:pointer; border: 1px solid #FFF; border-radius: 7px;\" title=\"Click to Remove\" />');
                  } else {
                    jQuery('#'+uploader_id+'_img_src').css('display', 'none');
                    jQuery('#'+uploader_id+'_img_src').html('');
                  }
                });
                
                jQuery('#'+uploader_id+'_img_src img').live('click', function(){
                  jQuery('#'+uploader_id+'_field').val('');
                  jQuery('#'+uploader_id+'_field').trigger('change');
                });
              
              jQuery('#'+uploader_id+'_field').trigger('change');

            }            
            });
      });
      </script>";
  }

  function wp_gear_manager_admin_scripts() {
    wp_enqueue_script('media-upload');
    wp_enqueue_script('thickbox');
    wp_enqueue_script('jquery');
  }

  function wp_gear_manager_admin_styles() {
    wp_enqueue_style('thickbox');
  }

  public function get_country_list() {
    return array('AF' => 'Afghanistan', 'AL' => 'Albania', 'DZ' => 'Algeria', 'AS' => 'American Samoa', 'AD' => 'Andorra', 'AO' => 'Angola', 'AI' => 'Anguilla', 'AQ' => 'Antarctica', 'AG' => 'Antigua And Barbuda', 'AR' => 'Argentina', 'AM' => 'Armenia', 'AW' => 'Aruba', 'AU' => 'Australia', 'AT' => 'Austria', 'AZ' => 'Azerbaijan', 'BS' => 'Bahamas', 'BH' => 'Bahrain', 'BD' => 'Bangladesh', 'BB' => 'Barbados', 'BY' => 'Belarus', 'BE' => 'Belgium', 'BZ' => 'Belize', 'BJ' => 'Benin', 'BM' => 'Bermuda', 'BT' => 'Bhutan', 'BO' => 'Bolivia', 'BA' => 'Bosnia And Herzegovina', 'BW' => 'Botswana', 'BV' => 'Bouvet Island', 'BR' => 'Brazil', 'IO' => 'British Indian Ocean Territory', 'BN' => 'Brunei', 'BG' => 'Bulgaria', 'BF' => 'Burkina Faso', 'BI' => 'Burundi', 'KH' => 'Cambodia', 'CM' => 'Cameroon', 'CA' => 'Canada', 'CV' => 'Cape Verde', 'KY' => 'Cayman Islands', 'CF' => 'Central African Republic', 'TD' => 'Chad', 'CL' => 'Chile', 'CN' => 'China', 'CX' => 'Christmas Island', 'CC' => 'Cocos (Keeling) Islands', 'CO' => 'Columbia', 'KM' => 'Comoros', 'CG' => 'Congo', 'CK' => 'Cook Islands', 'CR' => 'Costa Rica',
        'CI' => 'Cote D\'Ivorie (Ivory Coast)', 'HR' => 'Croatia (Hrvatska)', 'CU' => 'Cuba', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'CD' => 'Democratic Republic Of Congo (Zaire)', 'DK' => 'Denmark', 'DJ' => 'Djibouti', 'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'TP' => 'East Timor', 'EC' => 'Ecuador', 'EG' => 'Egypt', 'SV' => 'El Salvador', 'GQ' => 'Equatorial Guinea', 'ER' => 'Eritrea', 'EE' => 'Estonia', 'ET' => 'Ethiopia', 'FK' => 'Falkland Islands (Malvinas)', 'FO' => 'Faroe Islands', 'FJ' => 'Fiji', 'FI' => 'Finland', 'FR' => 'France', 'FX' => 'France, Metropolitan', 'GF' => 'French Guinea', 'PF' => 'French Polynesia', 'TF' => 'French Southern Territories', 'GA' => 'Gabon', 'GM' => 'Gambia', 'GE' => 'Georgia', 'DE' => 'Germany', 'GH' => 'Ghana', 'GI' => 'Gibraltar', 'GR' => 'Greece', 'GL' => 'Greenland', 'GD' => 'Grenada', 'GP' => 'Guadeloupe', 'GU' => 'Guam', 'GT' => 'Guatemala', 'GN' => 'Guinea', 'GW' => 'Guinea-Bissau', 'GY' => 'Guyana', 'HT' => 'Haiti', 'HM' => 'Heard And McDonald Islands', 'HN' => 'Honduras', 'HK' => 'Hong Kong', 'HU' => 'Hungary', 'IS' => 'Iceland',
        'IN' => 'India', 'ID' => 'Indonesia', 'IR' => 'Iran', 'IQ' => 'Iraq', 'IE' => 'Ireland', 'IL' => 'Israel', 'IT' => 'Italy', 'JM' => 'Jamaica', 'JP' => 'Japan', 'JO' => 'Jordan', 'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 'KI' => 'Kiribati', 'KW' => 'Kuwait', 'KG' => 'Kyrgyzstan', 'LA' => 'Laos', 'LV' => 'Latvia', 'LB' => 'Lebanon', 'LS' => 'Lesotho', 'LR' => 'Liberia', 'LY' => 'Libya', 'LI' => 'Liechtenstein', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'MO' => 'Macau', 'MK' => 'Macedonia', 'MG' => 'Madagascar', 'MW' => 'Malawi', 'MY' => 'Malaysia', 'MV' => 'Maldives', 'ML' => 'Mali', 'MT' => 'Malta', 'MH' => 'Marshall Islands', 'MQ' => 'Martinique', 'MR' => 'Mauritania', 'MU' => 'Mauritius', 'YT' => 'Mayotte', 'MX' => 'Mexico', 'FM' => 'Micronesia', 'MD' => 'Moldova', 'MC' => 'Monaco', 'MN' => 'Mongolia', 'MS' => 'Montserrat', 'MA' => 'Morocco', 'MZ' => 'Mozambique', 'MM' => 'Myanmar (Burma)', 'NA' => 'Namibia', 'NR' => 'Nauru', 'NP' => 'Nepal', 'NL' => 'Netherlands', 'AN' => 'Netherlands Antilles', 'NC' => 'New Caledonia', 'NZ' => 'New Zealand', 'NI' => 'Nicaragua', 'NE' => 'Niger', 'NG' => 'Nigeria',
        'NU' => 'Niue', 'NF' => 'Norfolk Island', 'KP' => 'North Korea', 'MP' => 'Northern Mariana Islands', 'NO' => 'Norway', 'OM' => 'Oman', 'PK' => 'Pakistan', 'PW' => 'Palau', 'PA' => 'Panama', 'PG' => 'Papua New Guinea', 'PY' => 'Paraguay', 'PE' => 'Peru', 'PH' => 'Philippines', 'PN' => 'Pitcairn', 'PL' => 'Poland', 'PT' => 'Portugal', 'PR' => 'Puerto Rico', 'QA' => 'Qatar', 'RE' => 'Reunion', 'RO' => 'Romania', 'RU' => 'Russia', 'RW' => 'Rwanda', 'SH' => 'Saint Helena', 'KN' => 'Saint Kitts And Nevis', 'LC' => 'Saint Lucia', 'PM' => 'Saint Pierre And Miquelon', 'VC' => 'Saint Vincent And The Grenadines', 'SM' => 'San Marino', 'ST' => 'Sao Tome And Principe', 'SA' => 'Saudi Arabia', 'SN' => 'Senegal', 'SC' => 'Seychelles', 'SL' => 'Sierra Leone', 'SG' => 'Singapore', 'SK' => 'Slovak Republic', 'SI' => 'Slovenia', 'SB' => 'Solomon Islands', 'SO' => 'Somalia', 'ZA' => 'South Africa', 'GS' => 'South Georgia And South Sandwich Islands', 'KR' => 'South Korea', 'ES' => 'Spain', 'LK' => 'Sri Lanka', 'SD' => 'Sudan', 'SR' => 'Suriname', 'SJ' => 'Svalbard And Jan Mayen', 'SZ' => 'Swaziland',
        'SE' => 'Sweden', 'CH' => 'Switzerland', 'SY' => 'Syria', 'TW' => 'Taiwan', 'TJ' => 'Tajikistan', 'TZ' => 'Tanzania', 'TH' => 'Thailand', 'TG' => 'Togo', 'TK' => 'Tokelau', 'TO' => 'Tonga', 'TT' => 'Trinidad And Tobago', 'TN' => 'Tunisia', 'TR' => 'Turkey', 'TM' => 'Turkmenistan', 'TC' => 'Turks And Caicos Islands', 'TV' => 'Tuvalu', 'UG' => 'Uganda', 'UA' => 'Ukraine', 'AE' => 'United Arab Emirates', 'UK' => 'United Kingdom', 'US' => 'United States', 'UM' => 'United States Minor Outlying Islands', 'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VU' => 'Vanuatu', 'VA' => 'Vatican City (Holy See)', 'VE' => 'Venezuela', 'VN' => 'Vietnam', 'VG' => 'Virgin Islands (British)', 'VI' => 'Virgin Islands (US)', 'WF' => 'Wallis And Futuna Islands', 'EH' => 'Western Sahara', 'WS' => 'Western Samoa', 'YE' => 'Yemen', 'YU' => 'Yugoslavia', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe');
  }

  public function clean_text($script_str) {
    $script_str = htmlspecialchars_decode($script_str);
    $search_arr = array('<script', '</script>');
    $script_str = str_ireplace($search_arr, $search_arr, $script_str);
    $split_arr = explode('<script', $script_str);
    $remove_jscode_arr = array();

    foreach ($split_arr as $key => $val) {
      $newarr = explode('</script>', $split_arr[$key]);
      $remove_jscode_arr[] = ($key == 0) ? $newarr[0] : $newarr[1];
    }

    return implode('', $remove_jscode_arr);
  }

}

add_action("init", "register_inic_testimonial_plugin");

function register_inic_testimonial_plugin() {
  global $indianic_testimonial;
  $indianic_testimonial = new iNIC_Testimonial();
}

register_activation_hook(__FILE__, 'iNicTestimonialInstall');

global $jal_db_version;
$jal_db_version = "1.1";

function iNicTestimonialInstall() {

  global $wpdb;
  global $jal_db_version;

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta("CREATE TABLE {$wpdb->prefix}inic_testimonial (
		  id int(11) NOT NULL AUTO_INCREMENT,
		  project_name varchar(512) NOT NULL,
          project_url varchar(512) NOT NULL,
		  client_name varchar(512) NOT NULL,
		  city varchar(64) NOT NULL,
		  state varchar(64) NOT NULL,
		  country varchar(64) NOT NULL,
		  description text NOT NULL,
		  tags varchar(512) NOT NULL,
		  video_url varchar(1024) NOT NULL,
		  thumb_img_url varchar(1024) NOT NULL,
		  large_img_url varchar(1024) NOT NULL,
		  is_featured tinyint(1) NOT NULL DEFAULT '0',
		  date_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (id),
		  KEY project_name (project_name),
		  KEY client_name (client_name),
		  KEY city (city),
		  KEY state (state),
		  KEY country (country),
		  KEY tags (tags))");

  dbDelta("CREATE TABLE {$wpdb->prefix}inic_testimonial_widget (
		  id int(11) NOT NULL AUTO_INCREMENT,
		  title varchar(512) NOT NULL,
          no_of_testimonial int(11) NOT NULL,
		  only_featured tinyint(1) NOT NULL,
          display_randomly tinyint(1) NOT NULL DEFAULT 0,
		  filter_by_country varchar(512) NOT NULL,
		  filter_by_tags varchar(512) NOT NULL,
		  html_template text NOT NULL,
		  date_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (id))");

  dbDelta("CREATE TABLE {$wpdb->prefix}inic_testimonial_template (
		  id int(11) NOT NULL AUTO_INCREMENT,
		  title varchar(512) NOT NULL,
          no_of_testimonial int(11) NOT NULL,
          list_per_page int(11) NOT NULL,
          ord_by varchar(64) NOT NULL,
		  filter_by_country varchar(512) NOT NULL,
		  filter_by_tags varchar(512) NOT NULL,
          custom_query varchar(1024) NOT NULL,
          show_featured_at enum('no','top','bottom') NOT NULL DEFAULT 'no',
          no_of_featured int(11) NOT NULL DEFAULT '1',
		  featured_template text NOT NULL,
          listing_template_odd text NOT NULL,
          listing_template_even text NOT NULL,
		  date_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  PRIMARY KEY (id))");

  add_option("jal_db_version", $jal_db_version);

  if (!get_option("inic_testimonial_list_ord_by"))
    add_option('inic_testimonial_list_ord_by', "id DESC");
  if (!get_option("inic_testimonial_admin_list_per_page"))
    add_option('inic_testimonial_admin_list_per_page', "10");
  if (!get_option("inic_testimonial_html_template"))
    add_option('inic_testimonial_html_template', "<div style=\"margin-bottom:20px; display:block; border-bottom:solid 1px #ccc;\"><strong>{#ProjectName}</strong><br />{#Description}<br /><strong>{#ClientName} - [IF {#City}]{#City} - [/IF]{#Country}</strong></div>");
}

global $wpdb;
$installed_ver = get_option("jal_db_version");
if ($installed_ver != $jal_db_version) {
  iNicTestimonialInstall();
}

require_once 'widget.php';
add_action('widgets_init', create_function('', 'register_widget( "iNIC_TestimonialWidget" );'));


if (!function_exists('array_chunk')) {

  function array_chunk($input, $size, $preserve_keys = false) {
    @reset($input);

    $i = $j = 0;

    while (@list( $key, $value ) = @each($input)) {
      if (!( isset($chunks[$i]) )) {
        $chunks[$i] = array();
      }

      if (count($chunks[$i]) < $size) {
        if ($preserve_keys) {
          $chunks[$i][$key] = $value;
          $j++;
        } else {
          $chunks[$i][] = $value;
        }
      } else {
        $i++;

        if ($preserve_keys) {
          $chunks[$i][$key] = $value;
          $j++;
        } else {
          $j = 0;
          $chunks[$i][$j] = $value;
        }
      }
    }

    return $chunks;
  }

} 