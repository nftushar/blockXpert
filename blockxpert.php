<?php
/**
 * Plugin Name: BlockXpert
 * Description: A powerful set of AI-driven Gutenberg blocks, including an AI FAQ and Product Recommend AI for WooCommerce, with comprehensive admin controls.
 * Version:      1.1.0
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

// Load interfaces
require_once __DIR__ . '/includes/interfaces/BlockManagerInterface.php';
require_once __DIR__ . '/includes/interfaces/CacheInterface.php';
require_once __DIR__ . '/includes/interfaces/AIProviderInterface.php';
require_once __DIR__ . '/includes/interfaces/LoggerInterface.php';
require_once __DIR__ . '/includes/interfaces/ServiceInterface.php';

// Load core classes
require_once __DIR__ . '/includes/classes/class-service-container.php';
require_once __DIR__ . '/includes/classes/class-blockxpert-logger.php';
require_once __DIR__ . '/includes/classes/class-blockxpert-cache.php';
require_once __DIR__ . '/includes/classes/class-blockxpert-service.php';
require_once __DIR__ . '/includes/classes/class-blockxpert-openai-provider.php';
require_once __DIR__ . '/includes/classes/class-blockxpert-blocks.php';
require_once __DIR__ . '/includes/classes/class-blockxpert-rest.php';
require_once __DIR__ . '/includes/admin/class-settings.php';
require_once __DIR__ . '/includes/class-plugin.php';

// Load text domain for translations
add_action('init', function() {
    load_plugin_textdomain('BlockXpert', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// On plugin activation, initialize all blocks as active
register_activation_hook(__FILE__, function() {
    /** Get all blocks from src/blocks directory */
    $blocks_dir = BLOCKXPERT_PATH . 'src/blocks';
    $all_blocks = [];
    if (is_dir($blocks_dir)) {
        $items = scandir($blocks_dir);
        if ($items !== false) {
            foreach ($items as $item) {
                if ($item !== '.' && $item !== '..' && is_dir($blocks_dir . '/' . $item)) {
                    $all_blocks[] = sanitize_key($item);
                }
            }
        }
    }
    
    // Set all blocks as active by default
    if (!empty($all_blocks)) {
        update_option('blockxpert_blocks_active', $all_blocks);
    }
    
    // Flush rewrite rules
    flush_rewrite_rules();
});

// Initialize the plugin
$blockxpert = new BlockXpert();