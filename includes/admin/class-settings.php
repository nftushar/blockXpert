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
        register_setting('blockxpert_settings', 'blockxpert_blocks_active', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_active_blocks'],
            'default' => ['product-slider', 'ai-faq', 'ai-product-recommendations', 'advanced-post-block'],
        ]);
    }

    /**
     * Sanitize active blocks
     */
    public function sanitize_active_blocks($input) {
        if (!is_array($input)) return [];
        return array_filter(array_map(function($block){
            return preg_replace('/[^a-z0-9_-]/', '', strtolower($block));
        }, $input));
    }

    /**
     * Render admin settings page
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) return;

        $all_blocks = ['product-slider', 'ai-faq', 'ai-product-recommendations', 'advanced-post-block'];
        $active_blocks = get_option('blockxpert_blocks_active', $all_blocks);
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
                            <input type="text" class="blockxpert-search-input" id="blockxpert-search" placeholder="<?php esc_attr_e('Search blocks...', 'blockxpert'); ?>">
                        </div>
                    </div>

                    <!-- Blocks list -->
                    <div class="blockxpert-block-list" id="blockxpert-block-list">
                        <?php foreach ($all_blocks as $block):
                            $checked = in_array($block, $active_blocks) ? 'checked' : '';
                            $status = in_array($block, $active_blocks) ? 'active' : 'inactive';
                            $yesno  = in_array($block, $active_blocks) ? 'Yes' : 'No';
                            ?>
                            <div class="blockxpert-block-card" data-block-name="<?php echo esc_attr($block); ?>" data-status="<?php echo esc_attr($status); ?>">
                                <span class="blockxpert-block-label"><?php echo esc_html(ucwords(str_replace('-', ' ', $block))); ?></span>
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

        $css_path = BLOCKXPERT_PATH . 'includes/assets/css/settings.css';
        $css_url  = BLOCKXPERT_URL . 'includes/assets/css/settings.css';
        if (file_exists($css_path)) {
            wp_enqueue_style('blockxpert-admin-settings', $css_url, [], filemtime($css_path));
        }

        $js_path = BLOCKXPERT_PATH . 'includes/assets/js/settings.js';
        $js_url  = BLOCKXPERT_URL . 'includes/assets/js/settings.js';
        if (file_exists($js_path)) {
            wp_enqueue_script('blockxpert-admin-settings', $js_url, ['jquery'], filemtime($js_path), true);
        }
    }
}

