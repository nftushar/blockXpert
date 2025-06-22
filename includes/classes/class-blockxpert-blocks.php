<?php
/**
 * Handles Gutenberg block registration and render callbacks for BlockXpert.
 */
class BlockXpert_Blocks {
    public function __construct() {
        add_action('init', [$this, 'register_blocks']);
        add_filter('block_categories_all', [$this, 'add_block_category']);
    }

    public function register_blocks() {
        $active_blocks = get_option('blockxpert_active', $this->get_default_blocks());
        error_log('BlockXpert: Active blocks: ' . print_r($active_blocks, true));
        foreach ($active_blocks as $block) {
            $block_dir = BLOCKXPERT_PATH . 'blocks/' . $block;
            $block_json_path = $block_dir . '/block.json';
            if (file_exists($block_json_path)) {
                error_log("BlockXpert: Found block.json for $block at $block_json_path");
                $callback_method = 'render_dynamic_block_' . str_replace('-', '_', $block);
                if (method_exists($this, $callback_method)) {
                    error_log("BlockXpert: Registering $block with callback $callback_method");
                    register_block_type($block_dir, [
                        'render_callback' => [$this, $callback_method],
                    ]);
                } else {
                    error_log("BlockXpert: Skipping $block - missing callback $callback_method");
                }
            } else {
                error_log("BlockXpert: Skipping $block - missing block.json at $block_json_path");
            }
        }
    }

    private function get_default_blocks() {
        return ['block-one', 'block-two', 'block-three', 'product-slider', 'ai-faq', 'ai-product-recommendations', 'pdf-invoice'];
    }

    // Render callback for block-one
    public function render_dynamic_block_block_one($attributes) {
        ob_start();
        ?>
        <div class="dynamic-block block-one">
            <h2><?php echo esc_html($attributes['content'] ?? 'Default Title for Block One'); ?></h2>
            <p><?php echo esc_html__('This is dynamic content rendered by PHP for Block One.', 'blockxpert'); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }

    // Render callback for block-two
    public function render_dynamic_block_block_two($attributes) {
        ob_start();
        ?>
        <div class="dynamic-block block-two">
            <h2><?php echo esc_html($attributes['content'] ?? 'Default Title for Block Two'); ?></h2>
            <p><?php echo esc_html__('This is dynamic content rendered by PHP for Block Two.', 'blockxpert'); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }

    // Render callback for block-three
    public function render_dynamic_block_block_three($attributes) {
        ob_start();
        ?>
        <div class="dynamic-block block-three">
            <h2><?php echo esc_html($attributes['content'] ?? 'Default Title for Block Three'); ?></h2>
            <p><?php echo esc_html__('This is dynamic content rendered by PHP for Block Three.', 'blockxpert'); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }

    // Render callback for product-slider
    public function render_dynamic_block_product_slider($attributes) {
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
        if (!empty($category)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $category
                ]
            ];
        }
        $products = new \WP_Query($args);
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

    // Render callback for ai-faq
    public function render_dynamic_block_ai_faq($attributes) {
        // (Paste the full method from init.php here)
    }

    // Render callback for ai-product-recommendations
    public function render_dynamic_block_ai_product_recommendations($attributes) {
        // (Paste the full method from init.php here)
    }

    // Render callback for pdf-invoice
    public function render_dynamic_block_pdf_invoice($attributes) {
        // (Paste the full method from init.php here)
    }

    public function add_block_category($categories) {
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
} 