<?php
/**
 * Plugin Name: BlockXpert
 * Description: A powerful set of AI-driven Gutenberg blocks, including an AI FAQ and Product Recommend AI for WooCommerce, with comprehensive admin controls.
 * Version:     1.0.0
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
require_once __DIR__ . '/includes/classes/class-blockxpert-service.php';
require_once __DIR__ . '/includes/classes/class-blockxpert-cache.php';
require_once __DIR__ . '/includes/classes/class-blockxpert-blocks.php';
require_once __DIR__ . '/includes/classes/class-blockxpert-rest.php';
require_once __DIR__ . '/includes/admin/class-settings.php';
require_once __DIR__ . '/includes/class-plugin.php';

// Load text domain for translations
add_action('init', function() {
    load_plugin_textdomain('BlockXpert', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// Initialize the plugin
$blockxpert = new BlockXpert();