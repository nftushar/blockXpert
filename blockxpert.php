<?php
/**
 * Plugin Name: BlockXpert
 * Plugin URI:  https://wordpress.org/plugins/blockxpert/
 * Description: A powerful set of AI-driven Gutenberg blocks, including an AI FAQ and AI Product Recommendations for WooCommerce, with comprehensive admin controls.
 * Version:     1.0.0
 * Author:      The WordPress Contributor Team
 * Author URI:  https://wordpress.org/
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: blockxpert
 */

defined('ABSPATH') || exit;

define('BLOCKXPERT_PATH', plugin_dir_path(__FILE__));
define('BLOCKXPERT_URL', plugin_dir_url(__FILE__));

require_once BLOCKXPERT_PATH . 'includes/init.php';
require_once BLOCKXPERT_PATH . 'includes/admin/settings-handler.php';