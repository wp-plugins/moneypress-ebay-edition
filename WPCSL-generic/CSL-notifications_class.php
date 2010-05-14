<?php

class wpCSL_notifications {

  function __construct($params) {
    foreach ($params as $name => $value) {
      $this->$name = $value;
    }
  }

  function add_notice($level = 1, $content, $link = null) {
    $this->notices[] = new wpCSL_notifications_notice(array(
                                                            'level' => $level,
                                                            'content' => $content,
                                                            'link' => $link
                                                            ));

  }

  function display() {

    // No need to do anything if there aren't any notices
    if (!isset($this->notices)) return;

    foreach ($this->notices as $notice) {
      $levels[$notice->level][] = $notice;
    }

    ksort($levels, SORT_NUMERIC);
    $difference = max(array_keys($levels));

    foreach ($levels as $key => $value) {
      $color = round((($key-1)*(255/$difference)));
      $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade' style='background-color: rgb(255, ".$color.", 25);'>\n";
      $notice_output .= sprintf(__('<p><strong><a href="%1$s">'.$this->name.'</a> needs attention: </strong>'), $this->url);
      $notice_output .= "<ul>\n";
      foreach ($value as $notice) {
        $notice_output .= "<li>{$notice->display()}</li>\n";
      }
      $notice_output .= "</ul>\n";
      $notice_output .= "</p></div>\n";
    }

    echo $notice_output;
  }
}

class wpCSL_notifications_notice {

  function __construct($params) {
    foreach($params as $name => $value) {
      $this->$name = $value;
    }
  }

  function display() {
    return $this->content . ((isset($this->link)) ? " (<a href=\"{$this->link}\">Details</a>)" : '');
  }
}

?>