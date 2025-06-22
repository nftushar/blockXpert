<?php
/**
 * Handles the admin settings page and options for BlockXpert.
 */
class BlockXpert_Admin_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_admin_page() {
        add_menu_page(
            __('BlockXpert Settings', 'blockxpert'),
            __('BlockXpert', 'blockxpert'),
            'manage_options',
            'blockxpert-settings',
            [$this, 'render_admin_page'],
            'dashicons-editor-code',
            60
        );
    }

    public function register_settings() {
        register_setting('blockxpert_settings', 'blockxpert_active', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_active_blocks'],
            'default' => ['block-one', 'block-two', 'block-three', 'product-slider', 'ai-faq', 'ai-product-recommendations', 'pdf-invoice'],
        ]);
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $blocks = ['block-one', 'block-two', 'block-three', 'product-slider', 'ai-faq', 'ai-product-recommendations', 'pdf-invoice'];
        $active_blocks = get_option('blockxpert_active', $blocks);
        echo '<div class="wrap"><h1>BlockXpert Settings</h1>';
        echo '<style>
        .blockxpert-tabs { display: flex; gap: 16px; margin-bottom: 16px; }
        .blockxpert-tab { padding: 8px 20px; border-radius: 20px; background: #f1f1f1; cursor: pointer; font-weight: 500; color: #2271b1; border: none; outline: none; transition: background 0.2s; }
        .blockxpert-tab.active, .blockxpert-tab:focus { background: #2271b1; color: #fff; }
        .blockxpert-search-bar { margin-bottom: 20px; }
        .blockxpert-search-input { width: 300px; padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 16px; }
        .blockxpert-block-list { display: flex; flex-wrap: wrap; gap: 24px; }
        .blockxpert-block-card { background: #fff; border: 1px solid #e1e5e9; border-radius: 8px; padding: 18px 24px; min-width: 260px; max-width: 320px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
        .blockxpert-block-label { font-size: 1.1em; font-weight: 500; }
        .blockxpert-toggle-switch { position: relative; display: inline-block; width: 48px; height: 24px; margin-right: 8px; vertical-align: middle; }
        .blockxpert-toggle-switch input { opacity: 0; width: 0; height: 0; }
        .blockxpert-toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px; }
        .blockxpert-toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        .blockxpert-toggle-switch input:checked + .blockxpert-toggle-slider { background-color: #2271b1; }
        .blockxpert-toggle-switch input:checked + .blockxpert-toggle-slider:before { transform: translateX(24px); }
        .blockxpert-toggle-yesno { font-weight: 500; color: #2271b1; }
        @media (max-width: 700px) { .blockxpert-block-list { flex-direction: column; gap: 12px; } .blockxpert-block-card { min-width: 0; width: 100%; } }
        </style>';
        echo '<form method="post" action="options.php">';
        settings_fields('blockxpert_settings');
        echo '<div class="blockxpert-tabs" id="blockxpert-tabs">
            <button type="button" class="blockxpert-tab active" data-tab="all">All</button>
            <button type="button" class="blockxpert-tab" data-tab="active">Active</button>
            <button type="button" class="blockxpert-tab" data-tab="inactive">Inactive</button>
        </div>';
        echo '<div class="blockxpert-search-bar"><input type="text" class="blockxpert-search-input" id="blockxpert-search" placeholder="Search blocks..."></div>';
        echo '<div class="blockxpert-block-list" id="blockxpert-block-list">';
        foreach ($blocks as $block) {
            $checked = in_array($block, $active_blocks) ? 'checked' : '';
            $yesno = in_array($block, $active_blocks) ? 'Yes' : 'No';
            $status = in_array($block, $active_blocks) ? 'active' : 'inactive';
            echo '<div class="blockxpert-block-card" data-block-name="' . esc_attr($block) . '" data-status="' . $status . '">
                <span class="blockxpert-block-label">' . esc_html(ucwords(str_replace('-', ' ', $block))) . '</span>
                <div>
                    <label class="blockxpert-toggle-switch">
                        <input type="checkbox" name="blockxpert_active[]" value="' . esc_attr($block) . '" ' . $checked . '>
                        <span class="blockxpert-toggle-slider"></span>
                    </label>
                    <span class="blockxpert-toggle-yesno">' . $yesno . '</span>
                </div>
            </div>';
        }
        echo '</div>';
        submit_button();
        echo '</form>';
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            // Tabs
            const tabs = document.querySelectorAll(".blockxpert-tab");
            const blockCards = document.querySelectorAll(".blockxpert-block-card");
            tabs.forEach(tab => {
                tab.addEventListener("click", function() {
                    tabs.forEach(t => t.classList.remove("active"));
                    this.classList.add("active");
                    const tabType = this.getAttribute("data-tab");
                    blockCards.forEach(card => {
                        if (tabType === "all") {
                            card.style.display = "flex";
                        } else if (tabType === "active") {
                            card.style.display = card.getAttribute("data-status") === "active" ? "flex" : "none";
                        } else if (tabType === "inactive") {
                            card.style.display = card.getAttribute("data-status") === "inactive" ? "flex" : "none";
                        }
                    });
                });
            });
            // Search
            const searchInput = document.getElementById("blockxpert-search");
            searchInput.addEventListener("input", function() {
                const val = this.value.toLowerCase();
                blockCards.forEach(card => {
                    const name = card.getAttribute("data-block-name").replace(/-/g, " ").toLowerCase();
                    card.style.display = name.includes(val) ? "flex" : "none";
                });
            });
            // Toggle Yes/No
            document.querySelectorAll(".blockxpert-toggle-switch input[type=checkbox]").forEach(function(input) {
                input.addEventListener("change", function() {
                    var span = this.closest(".blockxpert-block-card").querySelector(".blockxpert-toggle-yesno");
                    span.textContent = this.checked ? "Yes" : "No";
                    this.closest(".blockxpert-block-card").setAttribute("data-status", this.checked ? "active" : "inactive");
                });
            });
        });
        </script>';
        echo '</div>';
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_blockxpert-settings') {
            return;
        }
        $css_path = BLOCKXPERT_PATH . 'includes/assets/css/admin.css';
        $css_url = BLOCKXPERT_URL . 'includes/assets/css/admin.css';
        if (file_exists($css_path)) {
            wp_enqueue_style(
                'blockxpert-admin',
                $css_url,
                [],
                filemtime($css_path)
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