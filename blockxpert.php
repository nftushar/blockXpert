<?php
/**
 * Plugin Name: BlockXpert
 * Plugin URI: https://wordpress.org/plugins/blockxpert/
 * Description: Advanced Gutenberg blocks with AI-powered features including FAQ generation and product recommendations. Perfect for WooCommerce stores and content creators.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Author: BlockXpert Team
 * Author URI: https://blockxpert.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: blockxpert
 * Domain Path: /languages
 * Network: false
 * 
 * @package BlockXpert
 * @version 1.0.0
 * @author BlockXpert Team
 * @license GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BLOCKXPERT_VERSION', '1.0.0');
define('BLOCKXPERT_PATH', plugin_dir_path(__FILE__));
define('BLOCKXPERT_URL', plugin_dir_url(__FILE__));
define('BLOCKXPERT_BASENAME', plugin_basename(__FILE__));

// Load plugin files
require_once BLOCKXPERT_PATH . 'includes/init.php';
require_once BLOCKXPERT_PATH . 'includes/admin/settings-handler.php';

/**
 * Plugin activation hook
 */
function blockxpert_activate() {
    // Set default options
    $default_blocks = ['block-one', 'block-two', 'block-three', 'product-slider', 'ai-faq', 'ai-product-recommendations'];
    add_option('blockxpert_active', $default_blocks);
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'blockxpert_activate');

/**
 * Plugin deactivation hook
 */
function blockxpert_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'blockxpert_deactivate');

/**
 * Load text domain for internationalization
 */
function blockxpert_load_textdomain() {
    load_plugin_textdomain('blockxpert', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'blockxpert_load_textdomain');