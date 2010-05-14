<?php

class wpCSL_cache {

  function __construct($params) {
    foreach ($params as $name => $value) {
      $this->$name = $value;
    }
  }

  function initialize_options() {

    $this->settings->add_section(array(
                                            'name' => 'Cache Settings',
                                            'description' => "<p>Your cache directory can be found at: <code>".$this->path."</code></p>"
                                            ));

    $this->settings->add_item('Cache Settings', 'Enable Caching', $this->prefix.'-cache_enable', 'checkbox');
    $this->settings->add_item('Cache Settings', 'Retain Time', $this->prefix.'-cache_retain-time', 'custom', false, null,
                                   "<select name=\"".$this->prefix."-cache_retain-time\">
<option value=\"0\"".((get_option($this->prefix.'-cache_retain-time') == 0) ? ' selected' : '').">None</option>
<option value=\"3600\"".((get_option($this->prefix.'-cache_retain-time') == 3600) ? ' selected' : '').">1 Hour</option>
<option value=\"18000\"".((get_option($this->prefix.'-cache_retain-time') == 18000) ? ' selected' : '').">5 Hours</option>
<option value=\"86400\"".((get_option($this->prefix.'-cache_retain-time') == 86400) ? ' selected' : '').">1 Day</option>
<option value=\"604800\"".((get_option($this->prefix.'-cache_retain-time') == 604800) ? ' selected' : '').">1 Week</option>
</select>"
                                   );

  }

  function load($filename) {
    if (!$this->enabled) return false;
    $cache_file = $this->path . '/' . $filename;

    // 0 is an acceptable param, so we can't just do a boolean check
    if ( !($retain_time = get_option($this->prefix.'-cache_retain-time')) && ($retain_time != 0) ) {
      $retain_time = 3600;
    }

    if (file_exists($cache_file)) {

      if ( (time() - filemtime($cache_file)) <= $retain_time) {
        $contents = file_get_contents( $cache_file );
        return unserialize($contents);
      }
    }

    return false;
  }

  function save($filename, $data) {
    if (!$this->enabled) return false;
    $cache_file = $this->path . '/' . $filename;

    return file_put_contents($cache_file, serialize($data));
  }

  function check_cache() {
    if ( isset($this->enabled)) return;

    $is_cachable = false;
    if (get_option($this->prefix.'-cache_enable')) {
      $cache_file = $this->path . '/' . $filename;

      if (!file_exists($this->path)) {
        if (isset($this->notifications)) {
          $this->notifications->add_notice(2,
                                           "You do not have a cache directory<br>
                                            If you would like to implement caching, please create the cache directory: <code>" . $this->path . "</code>",
                                           "options-general.php?page={$this->prefix}-options#cache_settings");
        }
      } else if (!is_writable($this->path)) {
        if (isset($this->notifications)) {
          $this->notifications->add_notice(2,
                                           "Your cache directory is not writable<br>
                                            If you would like to implement caching, please change the permissions on the cache directory: <code>" . $this->path . "</code>",
                                           "options-general.php?page={$this->prefix}-options#cache_settings");
        }
      } else $is_cachable = true; // looks like we can cache stuff
    }

    $this->enabled = $is_cachable;

    return (isset($notices)) ? $notices : false;
  }

}

?>