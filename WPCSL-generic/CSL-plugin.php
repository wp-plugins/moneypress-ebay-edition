<?php
require_once('CSL-settings_class.php');
require_once('CSL-notifications_class.php');
require_once('CSL-license_class.php');
require_once('CSL-cache_class.php');

class wpCSL_plugin {

  function __construct($params) {
    foreach ($params as $name => $value) {
      $this->$name = $value;
    }


    $this->notifications_config = array(
                                        'prefix' => $this->prefix,
                                        'name' => $this->name,
                                        'url' => 'options-general.php?page='.$this->prefix.'-options',
                                        );

    $this->settings_config = array(
                                   'prefix' => $this->prefix,
                                   'plugin_url' => $this->plugin_url,
                                   'name' => $this->name,
                                   'url' => $this->url,
                                   'paypal_button_id' => $this->paypal_button_id
                                   );

    $this->license_config = array(
                                  'prefix' => $this->prefix
                                  );

    $this->cache_config = array(
                                'prefix' => $this->prefix,
                                'path' => $this->cache_path
                                );

    $this->initialize();
  }

  function create_notifications($class = 'none') {
    switch ($class) {
    case 'none':
      break;

    case 'wpCSL_notifications':
    case 'default':
    default:
      $this->notifications = new wpCSL_notifications($this->notifications_config);

    }
  }

  function create_settings($class = 'none') {
    switch ($class) {
    case 'none':
      break;

    case 'wpCSL_settings':
    case 'default':
    default:
      $this->settings = new wpCSL_settings($this->settings_config);

    }
  }

  function create_license($class = 'none') {
    switch ($class) {
    case 'none':
      break;

    case 'wpCSL_license':
    case 'default':
    default:
      $this->license = new wpCSL_license($this->license_config);

    }
  }

  function create_cache($class = 'none') {
    switch ($class) {
    case 'none':
      break;

    case 'wpCSL_cache':
    case 'default':
    default:
      $this->cache = new wpCSL_cache($this->cache_config);

    }
  }



  function create_options_page() {
    add_options_page($this->name . ' Options', $this->name, 'administrator', $this->prefix . '-options', array($this->settings, 'render_settings_page'));
  }

  function create_objects() {
    if (isset($this->use_obj_defaults) && $this->use_obj_defaults) {
      $this->create_notifications('default');
      $this->create_settings('default');
      $this->create_license('default');
      $this->create_cache('default');
    } else {
      if (isset($this->notifications_obj_name))
        $this->create_notifications($this->notifications_obj_name);
      if (isset($this->settings_obj_name))
        $this->create_settings($this->settings_obj_name);
      if (isset($this->license_obj_name))
        $this->create_license($this->license_obj_name);
      if (isset($this->cache_obj_name))
        $this->create_cache($this->cache_obj_name);
    }
  }

  // What did you say? Refactoring what now? I don't know what that is
  function add_refs() {
    // Notifications doesn't require any other objects yet

    // Settings
    if (isset($this->settings)) {
      if (isset($this->notifications) && !isset($this->settings->notifications))
        $this->settings->notifications = &$this->notifications;
      if (isset($this->license) && !isset($this->settings->license))
        $this->settings->license = &$this->license;
      if (isset($this->cache) && !isset($this->settings->cache))
        $this->settings->cache = &$this->cache;
    }

    // Cache
    if (isset($this->cache)) {
      if (isset($this->settings) && !isset($this->cache->settings))
        $this->cache->settings = &$this->settings;
      if (isset($this->notifications) && !isset($this->cache->notifications))
        $this->cache->notifications = &$this->notifications;
    }

    // License
    if (isset($this->license)) {
      if (isset($this->notifications) && !isset($this->license->notifications))
        $this->license->notifications = &$this->notifications;
    }

  }

  function initialize() {
    $this->create_objects();
    $this->add_refs();
    $this->add_wp_actions();
  }

  function add_wp_actions() {
    if ( is_admin() ) {
      add_action('admin_menu', array($this, 'create_options_page'));
      add_action('admin_init', array($this, 'admin_init'));
      add_action('admin_notices', array($this->notifications, 'display'));
    } else {
      // non-admin enqueues, actions, and filters
      add_action('wp_head', array($this, 'checks'));
    }
  }

  function admin_init() {
    $this->settings->register();
    $this->checks();
  }

  function checks() {
    if (isset($this->cache)) {
      $this->cache->check_cache();
    }

    if (isset($this->license)) {
      $this->license->check_product_key();
    }
  }

}

?>