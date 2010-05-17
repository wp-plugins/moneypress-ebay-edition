<?php

class wpCSL_settings {

  function __construct($params) {
    foreach ($params as $name => $value) {
      $this->$name = $value;
    }

    $this->add_section(array(
                             'name' => 'Product Settings',
                             'description' => "<p>
                                               To obtain a key, please purchase this plugin from
                                               <a href=\"{$this->url}\" target=\"_new\">{$this->url}</a>
                                               </p>",
                             'auto' => false
                             ));

    $this->add_section(array(
                             'name' => 'Product Info',
                             'description' => '<img src="'. $this->plugin_url .'/images/CSL_Logo_Only.jpg" style="float: left; padding: 5px;"/>
                                               <h4>This plugin was written and created by Cyber Sprocket Labs</h4>
                                               <p>
                                                 Cyber Sprocket Labs provides technical consulting
                                                 services for small-to-medium sized businesses.  If you’ve got an
                                                 online business concept, a new piece of cool software you want
                                                 written, or just need some help getting your technical team organized
                                                 and pointed in the right direction, we can help.
                                               </p>
                                               <p>
                                                 For more information, please visit our website at
                                                 <a href="http://www.cybersprocket.com" target="_new">www.cybersprocket.com</a>
                                               </p>
                                               <p>
                                                 Visit the product page for this plugin <a href="'. $this->url .'" target="_new">here</a>.
                                               </p>',
                             'auto' => false
                             ));

  }

  function add_section($params) {
    $this->sections[$params['name']] = new wpCSL_settings_section($params);
  }

  function add_item($section, $display_name, $name, $type = 'text', $required = false, $description = null, $custom = null) {
    $this->sections[$section]->add_item(array(
                                              'display_name' => $display_name,
                                              'name' => $name,
                                              'type' => $type,
                                              'required' => $required,
                                              'description' => $description,
                                              'custom' => $custom
                                              ));

    if ($required) {
      if (get_option($name) == '') {
        if (isset($this->notifications)) {
          $this->notifications->add_notice(1,
                                           "Please provide a value for <em>$display_name</em>",
                                           "options-general.php?page={$this->prefix}-options#".strtolower(strtr($display_name, ' ', '_')));
        }
      }
    }
  }

  /* This function should via an admin_init action */
  function register() {

    if (isset($this->license)) {
      $this->license->initialize_options();
    }
    if (isset($this->cache)) {
      $this->cache->initialize_options();
    }

    foreach ($this->sections as $section) {
      $section->register($this->prefix);
    }
  }

  function render_settings_page() {
    $this->header();

    $this->sections['Product Settings']->header();

    $this->show_product_settings();

    $this->sections['Product Settings']->footer();

    foreach ($this->sections as $section) {
      if ($section->auto) {
        $section->display();
      }
    }

    $this->sections['Product Info']->display();

    $this->render_javascript();

    $this->footer();
  }


  /* This is a function specifically for showing the licensing stuff,
   * should probably be moved over to the licensing submodule
   */
  function show_product_settings() {

    $content = "<tr valign=\"top\">\n";
    $content .= "  <th scope=\"row\">License Key *</th>";
    $content .= "    <td>";
    $content .= "<input type=\"text\"". ((!get_option($this->prefix.'-purchased')) ? "name=\"{$this->prefix}-license_key\"" : '') ." value=\"". get_option($this->prefix.'-license_key') ."\"". ((get_option($this->prefix.'-purchased')) ? 'disabled' : '') ." />";
    if (get_option($this->prefix.'-purchased')) {
      $content .= "<input type=\"hidden\" name=\"{$this->prefix}-license_key\" value=\"". get_option($this->prefix.'-license_key')."\"/>";
      $content .= "<span><font color=\"green\">Valid license key.  Thanks for your purchase!</font></span>";
    }
    $content .= (get_option($this->prefix.'-license_key') == '') ? '<span><font color="red">Without a license key, this plugin will only function for Admins</font></span>' : '';
    $content .= ( !(get_option($this->prefix.'-license_key') == '') && !get_option($this->prefix.'-purchased') ) ? '<span><font color="red">Your license key could not be verified</font></span>' : '';

    if (!get_option($this->prefix.'-purchased')) {
      $content .= "<div>
  <a href=\"https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id={$this->paypal_button_id}\" target=\"_new\">
    <img alt=\"PayPal - The safer, easier way to pay online!\" src=\"https://www.paypal.com/en_US/i/btn/btn_buynowCC_LG.gif\" />
  </a>
</div>";
    }

    $content .= "<div>
  <p>Your license key is simply your PayPal transaction key</p>
</div>
</td>
</tr>";

    echo $content;
  }

  function header() {
    echo "<div class=\"wrap\">\n";
    echo "<h2>{$this->name}</h2>\n";
    echo "<form method=\"post\" action=\"options.php\">\n";
    echo settings_fields($this->prefix.'-settings');

    echo "\n<div id=\"poststuff\" class=\"metabox-holder\">
     <div class=\"meta-box-sortables\">
       <script type=\"text/javascript\">
         jQuery(document).ready(function($) {
             $('.postbox').children('h3, .handlediv').click(function(){
                 $(this).siblings('.inside').toggle();
             });
         });
       </script>\n";
  }

  function footer() {
    echo "</div>
          </div>
          <p class=\"submit\">
          <input type=\"submit\" class=\"button-primary\" value=\"";
    _e('Save Changes');
    echo "\" />
          </p>
          </form>

         </div>";
  }

  function render_javascript() {
    echo "<script type=\"text/javascript\">
            function swapVisibility(id) {
              var item = document.getElementById(id);
              item.style.display = (item.style.display == 'block') ? 'none' : 'block';
            }
          </script>";
  }

  function check_required($section = null) {
    if ($section == null) {
      foreach ($this->sections as $section) {
        foreach ($section->items as $item) {
          if ($item->required && get_option($item->name) == '') return false;
        }
      }
    } else {
      foreach ($this->sections[$section]->items as $item) {
        if ($item->required && get_option($item->name) == '') return false;
      }
    }

    return true;
  }

}

class wpCSL_settings_section {

  function __construct($params) {
    foreach ($params as $name => $value) {
      $this->$name = $value;
    }

    if (!isset($this->auto)) $this->auto = true;
  }

  function add_item($params) {
    $this->items[] = new wpCSL_settings_item($params);
  }

  function register($prefix) {
    if (!isset($this->items)) return false;
    foreach ($this->items as $item) {
      $item->register($prefix);
    }
  }

  function display() {
    $this->header();

    if (isset($this->items)) {
      foreach ($this->items as $item) {
        $item->display();
      }
    }

    $this->footer();
  }

  function header() {
    echo "<div class=\"postbox\">
         <div class=\"handlediv\" title=\"Click to toggle\"><br/></div>
         <h3 class=\"hndle\">
           <span>{$this->name}</span>
           <a name=\"".strtolower(strtr($this->name, ' ', '_'))."\"></a>
         </h3>
         <div class=\"inside\">
            {$this->description}
    <table class=\"form-table\" style=\"margin-top: 0pt;\">\n";

  }

  function footer() {
    echo "</table>
         </div>
       </div>\n";
  }

}

class wpCSL_settings_item {

  function __construct($params) {
    foreach ($params as $name => $value) {
      $this->$name = $value;
    }
  }

  function register($prefix) {
    register_setting( $prefix.'-settings', $this->name );
  }

  function display() {
    $this->header();

    switch ($this->type) {
    case 'textarea':
      echo "<textarea name=\"{$this->name}\" cols=\"50\" rows=\"5\">". get_option($this->name) ."</textarea>";
      break;

    case 'text':
      echo "<input type=\"text\" name=\"{$this->name}\" value=\"". get_option($this->name) ."\" />";
      break;

    case "checkbox":
      echo "<input type=\"checkbox\" name=\"{$this->name}\"".((get_option($this->name)) ? ' checked' : '').">";
      break;

    case "list":
        echo $this->create_option_list();
        break;

    default:
      echo $this->custom;
      break;

    }

    if ($this->required) {
      echo ((get_option($this->name) == '') ? ' <span><font color="red">This field is required</font></span> ' : '');
    }

    if ($this->description != null) {
      $this->display_description($this->description);
    }

    $this->footer();
  }

  /**
   * If $type is 'list' then $custom is a hash used to make a <select>
   * drop-down representing the setting.  This function returns a
   * string with the markup for that list.
   */
  function create_option_list() {
      $output_list = array("<select name=\"{$this->name}\">\n");

      foreach ($this->custom as $key => $value) {
          if (get_option($this->name) === $value) {
              $output_list[] = "<option value=\"$value\" selected=\"selected\">$key</option>\n";
          }
          else {
              $output_list[] = "<option value=\"$value\">$key</option>\n";
          }
      }

      $output_list[] = "</select>\n";

      return implode('', $output_list);
  }

  function header() {
    echo "<tr valign=\"top\">
          <th scope=\"row\"><a name=\"".strtolower(strtr($this->display_name, ' ', '_'))."\"></a> {$this->display_name}".(($this->required) ? ' *' : '')."</th>
          <td>";

  }

  function footer() {
    echo "</td>\n</tr>";
  }

  function display_description($content) {
    echo " <a href=\"javascript:;\" onclick=\"swapVisibility('{$this->name}_desc');\">Description</a> ";
    echo "<div style=\"display: none;\" id=\"{$this->name}_desc\">";
    echo $content;
    echo "</div>";
  }

}

?>