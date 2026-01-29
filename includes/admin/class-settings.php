<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class BlockXpert_Admin_Settings {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    /**
     * Add admin menu page
     */
    public function add_admin_page() {
        add_menu_page(
            esc_html__('BlockXpert Settings', 'blockxpert'),
            esc_html__('BlockXpert', 'blockxpert'),
            'manage_options',
            'blockxpert-settings',
            [$this, 'render_admin_page'],
            'dashicons-editor-code',
            60
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        $default_active = ['product-slider'];
        
        // Ensure option exists and is not autoloaded to avoid DB bloat
        if (false === get_option('blockxpert_blocks_active', false)) {
            add_option('blockxpert_blocks_active', $default_active, '', 'no');
        }

        register_setting('blockxpert_settings', 'blockxpert_blocks_active', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_active_blocks'],
            'default' => $default_active,
        ]);
    }

    /**
     * Sanitize active blocks
     */
    public function sanitize_active_blocks($input) {
        if (!is_array($input)) return [];

        $allowed = BlockXpert::get_all_blocks();

        $sanitized = array_map(function($block){
            return preg_replace('/[^a-z0-9_-]/', '', strtolower($block));
        }, $input);

        // Only keep blocks that are in the allowed list
        $filtered = array_values(array_intersect($allowed, $sanitized));

        return $filtered;
    }

    /**
     * Render admin settings page
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) return;

        $all_blocks = BlockXpert::get_all_blocks();
        $active_blocks = get_option('blockxpert_blocks_active', ['product-slider']);

        // Icon map for blocks (dashicons)
        $icons = [
            'product-slider' => 'dashicons-images-alt2',
            'ai-faq' => 'dashicons-editor-help',
            'ai-product-recommendations' => 'dashicons-heart',
            'advanced-post-block' => 'dashicons-admin-post',
            'ai-recommendations' => 'dashicons-heart',
            'post-grid' => 'dashicons-table',
            'product-carousel' => 'dashicons-images-alt',
        ];
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('BlockXpert Settings', 'blockxpert'); ?></h1>

            <!-- Tabs -->
            <h2 class="nav-tab-wrapper">
                <a href="#blocks" class="nav-tab nav-tab-active" data-tab="blocks"><?php esc_html_e('Blocks', 'blockxpert'); ?></a>
            </h2>

            <!-- Blocks section -->
            <div id="blocks-section" class="blockxpert-section active">
                <form method="post" action="options.php">
                    <?php settings_fields('blockxpert_settings'); ?>

                    <!-- Search and filter -->
                    <div class="blockxpert-tabs" id="blockxpert-tabs">
                        <div class="blockxpert-tabs-buttons">
                            <button type="button" class="blockxpert-tab active" data-tab="all"><?php esc_html_e('All', 'blockxpert'); ?></button>
                            <button type="button" class="blockxpert-tab" data-tab="active"><?php esc_html_e('Active', 'blockxpert'); ?></button>
                            <button type="button" class="blockxpert-tab" data-tab="inactive"><?php esc_html_e('Inactive', 'blockxpert'); ?></button>
                        </div>
                        <div class="blockxpert-search-bar">
                            <input type="text" class="blockxpert-search-input" id="blockxpert-search" placeholder="<?php esc_attr_e('Search blocks...', 'blockxpert'); ?>" />
                        </div>
                    </div>

                    <!-- Blocks list -->
                    <div class="blockxpert-block-list" id="blockxpert-block-list">
                        <?php foreach ($all_blocks as $block):
                            $checked = in_array($block, $active_blocks) ? 'checked' : '';
                            $status = in_array($block, $active_blocks) ? 'active' : 'inactive';
                            $yesno  = in_array($block, $active_blocks) ? 'Yes' : 'No';
                            $icon_class = isset($icons[$block]) ? $icons[$block] : 'dashicons-admin-generic';
                            ?>
                            <div class="blockxpert-block-card" data-block-name="<?php echo esc_attr($block); ?>" data-status="<?php echo esc_attr($status); ?>">
                                <span class="blockxpert-block-label"><span class="dashicons <?php echo esc_attr($icon_class); ?>" style="margin-right:8px;vertical-align:middle;"></span><?php echo esc_html(ucwords(str_replace('-', ' ', $block))); ?></span>
                                <div>
                                    <label class="blockxpert-toggle-switch">
                                        <input type="checkbox" name="blockxpert_blocks_active[]" value="<?php echo esc_attr($block); ?>" <?php echo esc_attr($checked); ?>>
                                        <span class="blockxpert-toggle-slider"></span>
                                    </label>
                                    <span class="blockxpert-toggle-yesno"><?php echo esc_html($yesno); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue admin CSS/JS
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_blockxpert-settings') return;
        // Ensure dashicons are available for block icons
        wp_enqueue_style('dashicons');
        // Ensure webpack chunks load from the plugin build/ URL when needed
        if ( defined('BLOCKXPERT_URL') ) {
            wp_register_script('blockxpert-publicpath', '');
            $inline = "window.__webpack_public_path__ = '" . esc_js( BLOCKXPERT_URL . 'build/' ) . "';";
            wp_add_inline_script('blockxpert-publicpath', $inline);
            wp_enqueue_script('blockxpert-publicpath');
        }
        // Prefer plugin includes assets, fall back to build/ paths if present
        $candidates = [
            ['path' => BLOCKXPERT_PATH . 'includes/assets/css/settings.css', 'url' => BLOCKXPERT_URL . 'includes/assets/css/settings.css'],
            ['path' => BLOCKXPERT_PATH . 'build/settings.css', 'url' => BLOCKXPERT_URL . 'build/settings.css'],
        ];

        foreach ($candidates as $c) {
            if (file_exists($c['path'])) {
                wp_enqueue_style('blockxpert-admin-settings', $c['url'], [], filemtime($c['path']));
                break;
            }
        }

        $js_candidates = [
            ['path' => BLOCKXPERT_PATH . 'includes/assets/js/settings.js', 'url' => BLOCKXPERT_URL . 'includes/assets/js/settings.js'],
            ['path' => BLOCKXPERT_PATH . 'build/settings.js', 'url' => BLOCKXPERT_URL . 'build/settings.js'],
        ];

        foreach ($js_candidates as $c) {
            if (file_exists($c['path'])) {
                wp_enqueue_script('blockxpert-admin-settings', $c['url'], ['jquery'], filemtime($c['path']), true);
                break;
            }
        }
    }

    /**
     * Return canonical list of all blocks managed by the plugin.
     * Centralizing this list avoids duplication and helps validation.
     */
    // get_all_blocks() moved to BlockXpert::get_all_blocks()
}

