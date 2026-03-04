<?php
/**
 * Plugin Name: BlockXpert
 * Description: A powerful set of AI-driven Gutenberg blocks, including an AI FAQ and Product Recommend AI for WooCommerce, with comprehensive admin controls.
 * Version:     1.1.0
 * Author:      NF Tushar
 * Author URI:  https://github.com/nftushar/
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: BlockXpert
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

define('BLOCKXPERT_PATH', plugin_dir_path(__FILE__));
define('BLOCKXPERT_URL', plugin_dir_url(__FILE__));

require_once BLOCKXPERT_PATH . 'includes/init.php';

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Load core classes
error_log( '[BlockXpert] Starting plugin initialization...' );

require_once __DIR__ . '/includes/classes/core/class-blockxpert-service.php';
require_once __DIR__ . '/includes/classes/core/class-blockxpert-cache.php';
require_once __DIR__ . '/includes/classes/core/class-blockxpert-blocks.php';

error_log( '[BlockXpert] BlockXpert_Blocks class loaded, initializing category...' );

// Register block category immediately after class is loaded
BlockXpert_Blocks::init_category();

error_log( '[BlockXpert] Category initialization complete, loading remaining classes...' );

require_once __DIR__ . '/includes/classes/api/class-blockxpert-rest.php';
require_once __DIR__ . '/includes/admin/class-settings.php';
require_once __DIR__ . '/includes/class-plugin.php';

// Load text domain for translations
add_action('init', function() {
    load_plugin_textdomain('BlockXpert', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// Ensure category is registered on plugins_loaded as well
add_action( 'plugins_loaded', function() {
    error_log( '[BlockXpert] plugins_loaded hook: ensuring category is registered...' );
    BlockXpert_Blocks::init_category();
}, 5 );

// Initialize the plugin
$blockxpert = new BlockXpert();