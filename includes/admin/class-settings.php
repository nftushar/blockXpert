<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Handles the admin settings page and options for BlockXpert.
 */
class BlockXpert_Admin_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function add_admin_page() {
        add_menu_page(
            esc_html__('BlockXpert Settings', 'BlockXpert'),
            esc_html__('BlockXpert', 'BlockXpert'),
            'manage_options',
            'blockxpert-settings',
            [$this, 'render_admin_page'],
            'dashicons-editor-code',
            60
        );
    }

    public function register_settings() {
        register_setting('blockxpert_settings', 'blockxpert_blocks_active', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_active_blocks'],
            'default' => ['product-slider', 'ai-faq', 'ai-product-recommendations', 'advanced-post-block'],
        ]);
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        echo '<div class="wrap"><h1>' . esc_html__('BlockXpert Settings', 'BlockXpert') . '</h1>';
        
        // Add tabs navigation
        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="#blocks" class="nav-tab nav-tab-active" data-tab="blocks">' . esc_html__('Blocks', 'BlockXpert') . '</a>';
        echo '</h2>';
        
        // Blocks Settings Section
        echo '<div id="blocks-section" class="blockxpert-section active">';
    $blocks = ['product-slider', 'ai-faq', 'ai-product-recommendations', 'advanced-post-block'];
        $active_blocks = get_option('blockxpert_blocks_active', $blocks);
        
        echo '<form method="post" action="options.php">';
        settings_fields('blockxpert_settings');
echo '<div class="blockxpert-tabs" id="blockxpert-tabs">
            <div class="blockxpert-tabs-buttons">
                <button type="button" class="blockxpert-tab active" data-tab="all">All</button>
                <button type="button" class="blockxpert-tab" data-tab="active">Active</button>
                <button type="button" class="blockxpert-tab" data-tab="inactive">Inactive</button>
            </div>
            <div class="blockxpert-search-bar">
                <input type="text" class="blockxpert-search-input" id="blockxpert-search" placeholder="Search blocks...">
            </div>
        </div>';

        echo '<div class="blockxpert-block-list" id="blockxpert-block-list">';
        foreach ($blocks as $block) {
            $checked = in_array($block, $active_blocks) ? 'checked' : '';
            $yesno = in_array($block, $active_blocks) ? 'Yes' : 'No';
            $status = in_array($block, $active_blocks) ? 'active' : 'inactive';
            echo '<div class="blockxpert-block-card" data-block-name="' . esc_attr($block) . '" data-status="' . esc_attr($status) . '">
                <span class="blockxpert-block-label">' . esc_html(ucwords(str_replace('-', ' ', $block))) . '</span>
                <div>
                    <label class="blockxpert-toggle-switch">
                        <input type="checkbox" name="blockxpert_blocks_active[]" value="' . esc_attr($block) . '" ' . esc_attr($checked) . '>
                        <span class="blockxpert-toggle-slider"></span>
                    </label>
                    <span class="blockxpert-toggle-yesno">' . esc_html($yesno) . '</span>
                </div>
            </div>';
        }
        echo '</div>';
        submit_button();
        echo '</form>';
        echo '</div>';
        

        



        

        echo '</div>';
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_blockxpert-settings') {
            return;
        }
        
        // Enqueue admin CSS (common)
        $admin_css_path = BLOCKXPERT_PATH . 'includes/assets/css/common.css';
        $admin_css_url = BLOCKXPERT_URL . 'includes/assets/css/common.css';
        if (file_exists($admin_css_path)) {
            wp_enqueue_style(
                'blockxpert-admin',
                $admin_css_url,
                [],
                filemtime($admin_css_path)
            );
        }
        
        // Enqueue admin settings CSS
        $settings_css_path = BLOCKXPERT_PATH . 'includes/assets/css/settings.css';
        $settings_css_url = BLOCKXPERT_URL . 'includes/assets/css/settings.css';
        if (file_exists($settings_css_path)) {
            wp_enqueue_style(
                'blockxpert-admin-settings',
                $settings_css_url,
                ['blockxpert-admin'],
                filemtime($settings_css_path)
            );
        }
        
        // Enqueue admin settings JavaScript
        $settings_js_path = BLOCKXPERT_PATH . 'includes/assets/js/settings.js';
        $settings_js_url = BLOCKXPERT_URL . 'includes/assets/js/settings.js';
        if (file_exists($settings_js_path)) {
            wp_enqueue_script(
                'blockxpert-admin-settings',
                $settings_js_url,
                ['jquery'],
                filemtime($settings_js_path),
                true
            );
        }
    }

    public function sanitize_active_blocks($input) {
        if (!is_array($input)) {
            return [];
        }
        return array_filter(array_map(function ($block) {
            return preg_replace('/[^a-z0-9_-]/', '', strtolower($block));
        }, $input));
    }
} 