<?php
class Gutenberg_Blocks_Init
{
    public function __construct()
    {
        add_action('init', [$this, 'register_blocks']);
        add_filter('block_categories_all', [$this, 'add_block_category']);
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function register_blocks()
    {
        $active_blocks = get_option('gutenberg_blocks_active', $this->get_default_blocks());

        foreach ($this->discover_blocks() as $block_slug) {
            if (in_array($block_slug, $active_blocks)) {
                register_block_type(GB_PATH . "blocks/{$block_slug}");
            }
        }
    }

    private function discover_blocks()
    {
        $blocks = [];
        $blocks_dir = GB_PATH . 'blocks/';

        foreach (scandir($blocks_dir) as $item) {
            if ($item !== '.' && $item !== '..' && is_dir($blocks_dir . $item)) {
                $blocks[] = $item;
            }
        }

        return $blocks;
    }

    private function get_default_blocks()
    {
        return ['block-one', 'block-two', 'block-three'];
    }

    public function add_block_category($categories)
    {
        return array_merge($categories, [[
            'slug' => 'custom-blocks',
            'title' => __('Custom Blocks', 'gblocks')
        ]]);
    }

    public function add_admin_page()
    {
        add_menu_page(
            'Gutenberg Blocks Settings',
            'Gutenberg Blocks',
            'manage_options',
            'gutenberg-blocks-settings',
            [$this, 'render_admin_page'],
            'dashicons-editor-code',
            60
        );
    }

    public function render_admin_page()
    {
        if (!current_user_can('manage_options')) return;

        $blocks = $this->get_block_metadata();
        $active_blocks = get_option('gutenberg_blocks_active', $this->get_default_blocks());
?>
        <div class="wrap gutenberg-blocks-settings">
            <h1><?php esc_html_e('Gutenberg Blocks Settings', 'gblocks'); ?></h1>

            <div class="gform">
                <form method="post" action="options.php">
                    <?php
                    settings_fields('gutenberg_blocks_settings');
                    do_settings_sections('gutenberg_blocks_settings');
                    ?>

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
                        </div>
                    </div>

                    <div class="block-grid">
                        <?php foreach ($blocks as $slug => $block) : ?>
                            <div class="block-card">
                                <div class="block-toggle">
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="gutenberg_blocks_active[]" value="<?php echo esc_attr($slug); ?>"
                                            <?php checked(in_array($slug, $active_blocks)); ?>>


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
            </div>
        </div>

<?php
    }

    private function get_block_metadata()
    {
        $metadata = [];
        $blocks_dir = GB_PATH . 'blocks/';

        foreach (scandir($blocks_dir) as $item) {
            if ($item === '.' || $item === '..') continue;

            $block_json = $blocks_dir . $item . '/block.json';
            if (file_exists($block_json)) {
                $data = json_decode(file_get_contents($block_json), true);
                $metadata[$item] = [
                    'title' => $data['title'] ?? ucfirst(str_replace('-', ' ', $item)),
                    'icon' => $data['icon'] ?? 'block-default',
                    'description' => $data['description'] ?? __('Custom Gutenberg block', 'gblocks')
                ];
            }
        }

        return $metadata;
    }

    public function enqueue_admin_assets($hook)
    {
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

error_log('Enqueuing admin.css from: ' . GB_URL . 'includes/assets/css/admin.css');
new Gutenberg_Blocks_Init();
