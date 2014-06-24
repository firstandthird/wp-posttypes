<?php
/**
 * Plugin Name: First+Third Post Types
 * Description: Wordpress Custom Post Types Plugin
 */

if(!class_exists('Spyc')) {
  require_once('lib/spyc.php');
}

class ftPostTypes {

  private $config = array();

  function __construct() {
    $this->config_path =   WP_CONTENT_DIR . '/posttypes';

    // We want the init to run after the default 10 to give themes a chance to config
    add_action('init', array($this, 'init'), 20);
    add_action('admin_menu', array($this, 'admin_menu'));
    add_action('pre_get_posts', array($this, 'pre_get_posts'));
    add_action("admin_print_styles-post-new.php", array($this, 'edit_styles'));
    add_action("admin_print_styles-post.php", array($this, 'edit_styles'));

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

    foreach (glob($this->config_path . "/*.yaml") as $filename) {
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

    // Remove Submenu for singular items.
    foreach($this->config as $type) {
      if(isset($type['_special']) && isset($type['_special']['singular']) && $type['_special']['singular']) {
        remove_submenu_page('edit.php?post_type=' . $type['slug'], 'post-new.php?post_type=' . $type['slug']);
      }
    }
  }

  function pre_get_posts($query) {
    if(!is_admin() || !$query->is_main_query() || !isset($_GET['post_type'])) {
      return;
    }

    foreach($this->config as $type) {
      if(isset($type['_special']) && isset($type['_special']['singular']) && $type['_special']['singular']) {
        if($_GET['post_type'] === $type['slug']) {
          $count = wp_count_posts($type['slug']);

          if($count->publish || $count->future || $count->pending || $count->draft || $count->private) {
            $page = get_page_by_path($type['slug'], OBJECT, $type['slug']);
            wp_redirect('post.php?post=' . $page->ID . '&action=edit');
            exit;
          } else {
            wp_redirect('post-new.php?post_type=' . $type['slug'] . '&post_title=' . $type['slug']);
            exit;
          }
        }
      }
    }
  }

  function edit_styles() {
    foreach($this->config as $type) {
      if(get_post_type() === $type['slug']) {
        echo '<style>';
        echo '.add-new-h2 { display: none;  }';
        echo '#edit-slug-box { display: none; }';
        echo '#duplicate-action { display: none; }';
        echo '#delete-action { display: none; }';
        echo '#preview-action { display: none; }';
        echo '#visibility-radio-password, label[for=visibility-radio-password], label[for=visibility-radio-password] + br { display: none; }';
        echo '</style>';

        break;
      }
    }
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
