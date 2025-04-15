<?php
/**
 * Plugin Name: GBlocks
 * Description: Custom Gutenberg blocks with admin controls
 * Version: 1.0.0
 */

defined('ABSPATH') || exit;

define('GB_PATH', plugin_dir_path(__FILE__));
define('GB_URL', plugin_dir_url(__FILE__));

require_once GB_PATH . 'includes/init.php';
require_once GB_PATH . 'includes/admin/settings-handler.php';   