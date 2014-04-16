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

    // We want the init to run after the default 10 to give themes a chance to config
    add_action('init', array($this, 'init'), 20);
    add_action('admin_menu', array($this, 'admin_menu'));

    // Flushes rewrite cache when plugin is activated/deactivated.
    register_activation_hook(__FILE__, array($this, 'plugin_activate'));
    register_deactivation_hook(__FILE__, array($this, 'plugin_deactivate'));

    // Allow theme to override config path
    add_action('ft_posttypes_path', array($this, 'set_path'));

    $this->parse();
  }

  private function parse() {
    if(!file_exists($this->config_path)) {
      return false;
    }

    foreach (glob($this->config_path . "/**/*.yaml") as $filename) {
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

  function set_path($path) {
    if(!file_exists($path)) {
      return false;
    }

    $this->config_path = $path;

    $this->parse();
  }
}

$ftPostTypes = new ftPostTypes;