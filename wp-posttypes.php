<?php
/**
 * Plugin Name: wp-posttypes
 * Description: Wordpress Custom Post Types Plugin
 */

require_once('lib/spyc.php');

class ftPostTypes {

  private $config = array();

  function __construct() {
    $this->config_path =   WP_CONTENT_DIR . '/posttypes';
    $this->types_dir = $this->config_path . '/types';

    add_action('init', array($this, 'init'));
    add_action('admin_menu', array($this, 'admin_menu'));
    register_activation_hook(__FILE__, array($this, 'plugin_activate'));
    register_deactivation_hook(__FILE__, array($this, 'plugin_deactivate'));

    $this->parse();
  }

  private function parse() {
    foreach (glob($this->types_dir . "/*.yaml") as $filename) {
      $this->config[] = spyc_load_file($filename);
    }
  }

  function init() {
    foreach($this->config as $type) {
      register_post_type($type['slug'], $type);
    }
  }

  function plugin_activate() {
    flush_rewrite_rules();
  }

  function plugin_deactivate() {
    flush_rewrite_rules();
  }

  function admin_menu() {
    add_submenu_page('tools.php', 'Flush Rewrite Cache', 'Flush Rewrite Cache', 'activate_plugins', 'flush_rewrite_cache', array($this, 'update_rewrite'));
  }

  function update_rewrite() {
    flush_rewrite_rules();
    echo "<p>Rewrite cache flushed</p>";
  }
}

$ftPostTypes = new ftPostTypes;