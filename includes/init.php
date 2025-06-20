<?php
if (!defined('GB_PATH')) {
    define('GB_PATH', plugin_dir_path(__FILE__));
}

if (!defined('GB_URL')) {
    define('GB_URL', plugin_dir_url(__FILE__));
}

class Gutenberg_Blocks_Init
{
    public function __construct()
    {
        add_action('init', [$this, 'register_blocks']);
        add_filter('block_categories_all', [$this, 'add_block_category']);
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('enqueue_block_editor_assets', [$this, 'gutenberg_block_assets']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Register the dynamic blocks based on active blocks option
     */
    public function register_blocks()
    {
        $active_blocks = get_option('gutenberg_blocks_active', $this->get_default_blocks());

        foreach ($active_blocks as $block) {
            $block_dir = plugin_dir_path(__DIR__) . 'blocks/' . $block;
            $block_json_path = $block_dir . '/block.json';
error_log("🔍 Checking block: $block at $block_json_path");
            if (file_exists($block_json_path)) {
                $callback_method = 'render_dynamic_block_' . str_replace('-', '_', $block);

                if (method_exists($this, $callback_method)) {
                    register_block_type($block_dir, [
                        'render_callback' => [$this, $callback_method],
                    ]);
                    error_log("✅ Registered dynamic block: $block with callback $callback_method");
                } else {
                    error_log("⚠️ Missing render callback method $callback_method for block $block");
                }
            } else {
                error_log("⚠️ Missing block.json for block: $block");
            }
        }
    }

    /**
     * Register settings for active blocks option with sanitization
     */
    public function register_settings()
    {
        register_setting('gutenberg_blocks_settings', 'gutenberg_blocks_active', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_active_blocks'],
            'default' => $this->get_default_blocks(),
        ]);
    }

    /**
     * Sanitize active blocks option input
     */
    public function sanitize_active_blocks($input)
    {
        if (!is_array($input)) {
            return $this->get_default_blocks();
        }

        return array_filter(array_map(function ($block) {
            return preg_replace('/[^a-z0-9_-]/', '', strtolower($block));
        }, $input));
    }

    /**
     * Default blocks if none set
     */
    private function get_default_blocks()
    {
        return ['block-one', 'block-two', 'block-three'];
    }

    /**
     * Render callback for block-one
     */
    public function render_dynamic_block_block_one($attributes)
    {
        ob_start();
        ?>
        <div class="dynamic-block block-one">
            <h2><?php echo esc_html($attributes['content'] ?? 'Default Title for Block One'); ?></h2>
            <p><?php echo esc_html__('This is dynamic content rendered by PHP for Block One.', 'gblocks'); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render callback for block-two
     */
    public function render_dynamic_block_block_two($attributes)
    {
        ob_start();
        ?>
        <div class="dynamic-block block-two">
            <h2><?php echo esc_html($attributes['content'] ?? 'Default Title for Block Two'); ?></h2>
            <p><?php echo esc_html__('This is dynamic content rendered by PHP for Block Two.', 'gblocks'); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render callback for block-three
     */
    public function render_dynamic_block_block_three($attributes)
    {
        ob_start();
        ?>
        <div class="dynamic-block block-three">
            <h2><?php echo esc_html($attributes['content'] ?? 'Default Title for Block Three'); ?></h2>
            <p><?php echo esc_html__('This is dynamic content rendered by PHP for Block Three.', 'gblocks'); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Add custom block category to Gutenberg editor
     */
    public function add_block_category($categories)
    {
        // Avoid duplicating category
        foreach ($categories as $category) {
            if ($category['slug'] === 'custom-blocks') {
                return $categories;
            }
        }

        return array_merge($categories, [[
            'slug'  => 'custom-blocks',
            'title' => __('Custom Blocks', 'gblocks'),
        ]]);
    }

    /**
     * Add admin menu page for block settings
     */
    public function add_admin_page()
    {
        add_menu_page(
            __('Gutenberg Blocks Settings', 'gblocks'),
            __('Gutenberg Blocks', 'gblocks'),
            'manage_options',
            'gutenberg-blocks-settings',
            [$this, 'render_admin_page'],
            'dashicons-editor-code',
            60
        );
    }

    /**
     * Enqueue block editor scripts and styles on block editor pages only
     */
    public function gutenberg_block_assets()
    {
        // Only load scripts/styles in Gutenberg editor
        if (!is_admin() || !function_exists('get_current_screen')) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen || $screen->base !== 'post') {
            return;
        }

        $script_path = plugin_dir_path(__DIR__) . 'src/index.js';
        $script_url  = plugins_url('src/index.js', __DIR__);

        if (file_exists($script_path)) {
            wp_enqueue_script(
                'gutenberg-blocks-js',
                $script_url,
                ['wp-blocks', 'wp-element', 'wp-editor', 'wp-i18n'],
                filemtime($script_path),
                true
            );
        }
    }

    /**
     * Render the admin settings page
     */
    public function render_admin_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $blocks = $this->get_block_metadata();
        $active_blocks = get_option('gutenberg_blocks_active', $this->get_default_blocks());
        ?>
        <div class="wrap gutenberg-blocks-settings">
            <h1><?php esc_html_e('Gutenberg Blocks Settings', 'gblocks'); ?></h1>

            <form method="post" action="options.php">
                <?php settings_fields('gutenberg_blocks_settings'); ?>

                <div class="blocks-sub-header">
                    <div class="tabs" role="tablist">
                        <span role="tab" aria-selected="true" class="tab is-active">All</span>
                        <span role="tab" class="tab">Active</span>
                        <span role="tab" class="tab">Inactive</span>
                    </div>

                    <div class="search-container">
                        <span class="dashicons dashicons-search search-icon"></span>
                        <input
                            id="search"
                            type="text"
                            class="search-input"
                            placeholder="<?php esc_attr_e('Search…', 'gblocks'); ?>"
                            aria-label="<?php esc_attr_e('Search Blocks', 'gblocks'); ?>"
                            value="">
                        <button type="button" id="clear-search" class="button">×</button>
                    </div>
                </div>

                <div class="block-grid">
                    <?php foreach ($blocks as $slug => $block) :
                        $is_active = in_array($slug, $active_blocks, true);
                        $status_class = $is_active ? 'block-active' : 'block-inactive';
                        ?>
                        <div class="block-card <?php echo esc_attr($status_class); ?>" data-slug="<?php echo esc_attr($slug); ?>">
                            <div class="block-toggle">
                                <label class="toggle-switch">
                                    <input
                                        type="checkbox"
                                        name="gutenberg_blocks_active[]"
                                        value="<?php echo esc_attr($slug); ?>"
                                        <?php checked($is_active); ?>>
                                    <span class="slider"></span>
                                </label>
                                <div class="block-info">
                                    <span class="dashicons dashicons-<?php echo esc_attr($block['icon']); ?>"></span>
                                    <h3><?php echo esc_html($block['title']); ?></h3>
                                </div>
                            </div>
                            <p class="description"><?php echo esc_html($block['description']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php submit_button(__('Save Settings', 'gblocks')); ?>
            </form>

            <?php
            // Debug output of saved option (remove on production)
            $saved_blocks = get_option('gutenberg_blocks_active', []);
            echo '<div class="notice notice-success"><p><strong>Saved option value:</strong></p><pre>';
            print_r($saved_blocks);
            echo '</pre></div>';
            ?>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const tabs = document.querySelectorAll('.tabs .tab');
                    const cards = document.querySelectorAll('.block-card');
                    const searchInput = document.getElementById('search');
                    const clearSearchBtn = document.getElementById('clear-search');

                    let currentTab = 'all';

                    function filterBlocks() {
                        const searchTerm = searchInput.value.toLowerCase();
                        cards.forEach(card => {
                            const isActive = card.classList.contains('block-active');
                            const isInactive = card.classList.contains('block-inactive');
                            const title = card.querySelector('h3').textContent.toLowerCase();
                            const desc = card.querySelector('p.description').textContent.toLowerCase();

                            let show = true;

                            if (currentTab === 'active' && !isActive) show = false;
                            if (currentTab === 'inactive' && !isInactive) show = false;

                            if (searchTerm && !(title.includes(searchTerm) || desc.includes(searchTerm))) {
                                show = false;
                            }

                            card.style.display = show ? 'block' : 'none';
                        });
                    }

                    tabs.forEach(tab => {
                        tab.addEventListener('click', () => {
                            tabs.forEach(t => {
                                t.classList.remove('is-active');
                                t.setAttribute('aria-selected', 'false');
                            });
                            tab.classList.add('is-active');
                            tab.setAttribute('aria-selected', 'true');

                            currentTab = tab.textContent.trim().toLowerCase();
                            filterBlocks();
                        });
                    });

                    searchInput.addEventListener('input', () => {
                        clearSearchBtn.style.display = searchInput.value.length > 0 ? 'inline' : 'none';
                        filterBlocks();
                    });

                    clearSearchBtn.addEventListener('click', () => {
                        searchInput.value = '';
                        clearSearchBtn.style.display = 'none';
                        filterBlocks();
                    });

                    clearSearchBtn.style.display = 'none';
                });
            </script>

            <style>
                .block-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                    gap: 16px;
                }

                .block-card {
                    border: 1px solid #ddd;
                    padding: 12px;
                    background: #fff;
                    border-radius: 6px;
                    transition: all 0.2s ease;
                }

                .block-card[style*="display: none"] {
                    opacity: 0;
                }

                .tabs .tab {
                    display: inline-block;
                    padding: 6px 12px;
                    margin-right: 8px;
                    cursor: pointer;
                    border-bottom: 2px solid transparent;
                }

                .tabs .tab.is-active {
                    border-bottom-color: #0073aa;
                    font-weight: bold;
                }

                .search-container {
                    margin-top: 10px;
                    position: relative;
                }

                .search-input {
                    padding-left: 24px;
                    padding-right: 24px;
                    width: 200px;
                }

                .search-icon {
                    position: absolute;
                    top: 50%;
                    left: 6px;
                    transform: translateY(-50%);
                }

                #clear-search {
                    position: absolute;
                    right: 6px;
                    top: 50%;
                    transform: translateY(-50%);
                    padding: 0 6px;
                    font-size: 14px;
                    line-height: 1;
                    cursor: pointer;
                }
            </style>
        </div>
        <?php
    }

    /**
     * Get block metadata by reading block.json from blocks directory
     */
    private function get_block_metadata()
    {
        $metadata = [];
        $blocks_dir = GB_PATH . 'blocks/';

        if (!is_dir($blocks_dir)) {
            return $metadata;
        }

        foreach (scandir($blocks_dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $block_json = $blocks_dir . $item . '/block.json';
            if (file_exists($block_json)) {
                $data = json_decode(file_get_contents($block_json), true);
                $metadata[$item] = [
                    'title'       => $data['title'] ?? ucfirst(str_replace('-', ' ', $item)),
                    'icon'        => $data['icon'] ?? 'block-default',
                    'description' => $data['description'] ?? __('Custom Gutenberg block', 'gblocks'),
                ];
            }
        }

        return $metadata;
    }

    /**
     * Enqueue admin CSS only on the plugin's settings page
     */
    public function enqueue_admin_assets($hook)
    {
        if ($hook !== 'toplevel_page_gutenberg-blocks-settings') {
            return;
        }

        $css_path = GB_PATH . 'includes/assets/css/admin.css';
        $css_url = GB_URL . 'includes/assets/css/admin.css';

        if (file_exists($css_path)) {
            wp_enqueue_style(
                'gutenberg-blocks-admin',
                $css_url,
                [],
                filemtime($css_path)
            );
        }
    }
}

new Gutenberg_Blocks_Init();
