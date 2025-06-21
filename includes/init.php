<?php
if (!defined('BLOCKXPERT_PATH')) {
    define('BLOCKXPERT_PATH', plugin_dir_path(__FILE__));
}

if (!defined('BLOCKXPERT_URL')) {
    define('BLOCKXPERT_URL', plugin_dir_url(__FILE__));
}

class BlockXpert_Init
{
    public function __construct()
    {
        add_action('init', [$this, 'register_blocks']);
        add_filter('block_categories_all', [$this, 'add_block_category']);
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Register the dynamic blocks based on active blocks option
     */
    public function register_blocks()
    {
        $active_blocks = get_option('blockxpert_active', $this->get_default_blocks());

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
        register_setting('blockxpert_settings', 'blockxpert_active', [
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
        return ['block-one', 'block-two', 'block-three', 'product-slider'];
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
            <p><?php echo esc_html__('This is dynamic content rendered by PHP for Block One.', 'blockxpert'); ?></p>
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
            <p><?php echo esc_html__('This is dynamic content rendered by PHP for Block Two.', 'blockxpert'); ?></p>
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
            <p><?php echo esc_html__('This is dynamic content rendered by PHP for Block Three.', 'blockxpert'); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render callback for product-slider
     */
    public function render_dynamic_block_product_slider($attributes)
    {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            return '<div class="notice notice-error"><p>' . esc_html__('WooCommerce is required for the Product Slider block.', 'blockxpert') . '</p></div>';
        }

        $title = $attributes['title'] ?? __('Featured Products', 'blockxpert');
        $products_per_slide = $attributes['productsPerSlide'] ?? 3;
        $auto_play = $attributes['autoPlay'] ?? true;
        $show_navigation = $attributes['showNavigation'] ?? true;
        $show_pagination = $attributes['showPagination'] ?? true;
        $category = $attributes['category'] ?? '';
        $order_by = $attributes['orderBy'] ?? 'date';
        $order = $attributes['order'] ?? 'desc';

        // Build query args
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'orderby' => $order_by,
            'order' => $order,
            'meta_query' => [
                [
                    'key' => '_visibility',
                    'value' => ['catalog', 'visible'],
                    'compare' => 'IN'
                ]
            ]
        ];

        // Add category filter if specified
        if (!empty($category)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $category
                ]
            ];
        }

        $products = new WP_Query($args);
        $unique_id = 'product-slider-' . uniqid();

        ob_start();
        ?>
        <div class="woocommerce-product-slider" id="<?php echo esc_attr($unique_id); ?>">
            <?php if (!empty($title)) : ?>
                <h2 class="slider-title"><?php echo esc_html($title); ?></h2>
            <?php endif; ?>

            <?php if ($products->have_posts()) : ?>
                <div class="slider-container">
                    <div class="slider-wrapper">
                        <div class="slider-track">
                            <?php while ($products->have_posts()) : $products->the_post(); 
                                global $product;
                                if (!$product) continue;
                            ?>
                                <div class="slider-item">
                                    <div class="product-card">
                                        <div class="product-image">
                                            <a href="<?php echo esc_url(get_permalink()); ?>">
                                                <?php if (has_post_thumbnail()) : ?>
                                                    <?php the_post_thumbnail('woocommerce_thumbnail', ['class' => 'product-thumbnail']); ?>
                                                <?php else : ?>
                                                    <img src="<?php echo esc_url(wc_placeholder_img_src()); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="product-thumbnail">
                                                <?php endif; ?>
                                            </a>
                                        </div>
                                        <div class="product-info">
                                            <h3 class="product-title">
                                                <a href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a>
                                            </h3>
                                            <div class="product-price">
                                                <?php echo $product->get_price_html(); ?>
                                            </div>
                                            <?php if ($product->is_in_stock()) : ?>
                                                <div class="product-actions">
                                                    <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="button add-to-cart">
                                                        <?php echo esc_html__('Add to Cart', 'blockxpert'); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <?php if ($show_navigation && $products->post_count > $products_per_slide) : ?>
                        <button class="slider-nav slider-prev" aria-label="<?php esc_attr_e('Previous', 'blockxpert'); ?>">‹</button>
                        <button class="slider-nav slider-next" aria-label="<?php esc_attr_e('Next', 'blockxpert'); ?>">›</button>
                    <?php endif; ?>

                    <?php if ($show_pagination && $products->post_count > $products_per_slide) : ?>
                        <div class="slider-pagination"></div>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <p class="no-products"><?php esc_html_e('No products found.', 'blockxpert'); ?></p>
            <?php endif; ?>
        </div>

        <style>
            .woocommerce-product-slider {
                margin: 2rem 0;
            }
            .slider-title {
                text-align: center;
                margin-bottom: 2rem;
                font-size: 2rem;
                color: #333;
            }
            .slider-container {
                position: relative;
                max-width: 100%;
                overflow: hidden;
            }
            .slider-wrapper {
                overflow: hidden;
            }
            .slider-track {
                display: flex;
                transition: transform 0.3s ease;
            }
            .slider-item {
                flex: 0 0 calc(100% / <?php echo esc_attr($products_per_slide); ?>);
                padding: 0 10px;
                box-sizing: border-box;
            }
            .product-card {
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
                overflow: hidden;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }
            .product-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            .product-image {
                position: relative;
                overflow: hidden;
            }
            .product-thumbnail {
                width: 100%;
                height: 200px;
                object-fit: cover;
                transition: transform 0.3s ease;
            }
            .product-card:hover .product-thumbnail {
                transform: scale(1.05);
            }
            .product-info {
                padding: 1rem;
            }
            .product-title {
                margin: 0 0 0.5rem 0;
                font-size: 1rem;
                line-height: 1.3;
            }
            .product-title a {
                color: #333;
                text-decoration: none;
            }
            .product-title a:hover {
                color: #0073aa;
            }
            .product-price {
                font-weight: bold;
                color: #0073aa;
                margin-bottom: 1rem;
            }
            .product-actions {
                text-align: center;
            }
            .add-to-cart {
                background: #0073aa;
                color: #fff;
                border: none;
                padding: 0.5rem 1rem;
                border-radius: 4px;
                text-decoration: none;
                display: inline-block;
                transition: background 0.2s ease;
            }
            .add-to-cart:hover {
                background: #005a87;
                color: #fff;
            }
            .slider-nav {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                background: rgba(255,255,255,0.9);
                border: 1px solid #ddd;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                font-size: 1.2rem;
                z-index: 10;
                transition: all 0.2s ease;
            }
            .slider-nav:hover {
                background: #fff;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            .slider-prev {
                left: 10px;
            }
            .slider-next {
                right: 10px;
            }
            .slider-pagination {
                display: flex;
                justify-content: center;
                margin-top: 1rem;
                gap: 0.5rem;
            }
            .slider-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: #ddd;
                cursor: pointer;
                transition: background 0.2s ease;
            }
            .slider-dot.active {
                background: #0073aa;
            }
            .no-products {
                text-align: center;
                color: #666;
                font-style: italic;
            }
            @media (max-width: 768px) {
                .slider-item {
                    flex: 0 0 calc(100% / 2);
                }
            }
            @media (max-width: 480px) {
                .slider-item {
                    flex: 0 0 100%;
                }
            }
        </style>

        <script>
        (function() {
            const slider = document.getElementById('<?php echo esc_js($unique_id); ?>');
            if (!slider) return;

            const track = slider.querySelector('.slider-track');
            const items = slider.querySelectorAll('.slider-item');
            const prevBtn = slider.querySelector('.slider-prev');
            const nextBtn = slider.querySelector('.slider-next');
            const pagination = slider.querySelector('.slider-pagination');

            let currentSlide = 0;
            const totalSlides = Math.ceil(items.length / <?php echo esc_js($products_per_slide); ?>);
            const itemsPerSlide = <?php echo esc_js($products_per_slide); ?>;

            function updateSlider() {
                const translateX = -(currentSlide * 100);
                track.style.transform = `translateX(${translateX}%)`;
                
                // Update pagination
                if (pagination) {
                    const dots = pagination.querySelectorAll('.slider-dot');
                    dots.forEach((dot, index) => {
                        dot.classList.toggle('active', index === currentSlide);
                    });
                }
            }

            function createPagination() {
                if (!pagination) return;
                
                for (let i = 0; i < totalSlides; i++) {
                    const dot = document.createElement('div');
                    dot.className = 'slider-dot' + (i === 0 ? ' active' : '');
                    dot.addEventListener('click', () => {
                        currentSlide = i;
                        updateSlider();
                    });
                    pagination.appendChild(dot);
                }
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    currentSlide = Math.max(0, currentSlide - 1);
                    updateSlider();
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    currentSlide = Math.min(totalSlides - 1, currentSlide + 1);
                    updateSlider();
                });
            }

            createPagination();

            <?php if ($auto_play) : ?>
            // Auto-play functionality
            let autoPlayInterval;
            
            function startAutoPlay() {
                autoPlayInterval = setInterval(() => {
                    currentSlide = (currentSlide + 1) % totalSlides;
                    updateSlider();
                }, 5000);
            }

            function stopAutoPlay() {
                if (autoPlayInterval) {
                    clearInterval(autoPlayInterval);
                }
            }

            slider.addEventListener('mouseenter', stopAutoPlay);
            slider.addEventListener('mouseleave', startAutoPlay);
            startAutoPlay();
            <?php endif; ?>
        })();
        </script>
        <?php
        wp_reset_postdata();
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
            'title' => __('Custom Blocks', 'blockxpert'),
        ]]);
    }

    /**
     * Add admin menu page for block settings
     */
    public function add_admin_page()
    {
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

    /**
     * Render the admin settings page
     */
    public function render_admin_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $blocks = $this->get_block_metadata();
        $active_blocks = get_option('blockxpert_active', $this->get_default_blocks());
        ?>
        <div class="wrap blockxpert-settings">
            <h1><?php esc_html_e('BlockXpert Settings', 'blockxpert'); ?></h1>

            <form method="post" action="options.php">
                <?php settings_fields('blockxpert_settings'); ?>

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
                            placeholder="<?php esc_attr_e('Search…', 'blockxpert'); ?>"
                            aria-label="<?php esc_attr_e('Search Blocks', 'blockxpert'); ?>"
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
                                        name="blockxpert_active[]"
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

                <?php submit_button(__('Save Settings', 'blockxpert')); ?>
            </form>

            <?php
            // Debug output of saved option (remove on production)
            $saved_blocks = get_option('blockxpert_active', []);
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
        $blocks_dir = BLOCKXPERT_PATH . 'blocks/';

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
                    'description' => $data['description'] ?? __('Custom Gutenberg block', 'blockxpert'),
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
}

new BlockXpert_Init();
