<?php

class wpCSL_license {

  function __construct($params) {
    foreach ($params as $name => $value) {
      $this->$name = $value;
    }
  }

  /*
   * Currently only checks for an existing license key (PayPal
   * transaction ID).
   */
  function check_license_key() {
    $csl_url = 'http://cybersprocket.com/paypal/valid_transaction.php?';

    $query_string = http_build_query(array('id' => get_option($this->prefix.'-license_key')));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $csl_url . $query_string);
    curl_setopt($ch, CURLOPT_POST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    $response = curl_exec($ch);

    return ($response == 'true') ? true : false;
  }

  function check_product_key() {
    // Attempt to find old versions of the license
    if (!get_option($this->prefix.'-purchased') && (get_option('purchased') != '') ) {
      update_option($this->prefix.'-purchased', get_option('purchased'));
    }
    if (!get_option($this->prefix.'-license_key') && (get_option('license_key') != '') ) {
      update_option($this->prefix.'-license_key', get_option('license_key'));
    }

    if (!get_option($this->prefix.'-purchased')) {
      if (get_option($this->prefix.'-license_key') != '') {
        update_option($this->prefix.'-purchased', $this->check_license_key());
      }

      if (!get_option($this->prefix.'-purchased')) {
        if (isset($this->notifications)) {
          $this->notifications->add_notice(2,
                                           "You have not provided a valid license key for this plugin. Until you do so, it will only display content for Admin users.",
                                           "options-general.php?page={$this->prefix}-options#product_settings");
        }
        /* $notices['product'] = */
        /*   "You have not provided a valid license key for this plugin. Until you do so, it will only display content for Admin users."; */
      }
    }

    return (isset($notices)) ? $notices : false;
  }

  function initialize_options() {
    register_setting($this->prefix.'-settings', $this->prefix.'-license_key');
    register_setting($this->prefix.'-Settings', $this->prefix.'-purchased');
  }
}

?>