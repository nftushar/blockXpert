<?php
/**
 * Plugin Name: BlockXpert
 * Description: Custom Gutenberg blocks with admin controls
 * Version: 1.0.0
 */

defined('ABSPATH') || exit;

define('BLOCKXPERT_PATH', plugin_dir_path(__FILE__));
define('BLOCKXPERT_URL', plugin_dir_url(__FILE__));

require_once BLOCKXPERT_PATH . 'includes/init.php';
require_once BLOCKXPERT_PATH . 'includes/admin/settings-handler.php';