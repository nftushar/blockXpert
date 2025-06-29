<?php
/**
 * Handles Gutenberg block registration and render callbacks for BlockXpert.
 */
class BlockXpert_Blocks {
    public function __construct() {
        add_action('init', [$this, 'register_blocks']);
        add_filter('block_categories_all', [$this, 'add_block_category']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
    }

    public function register_blocks() {
        $active_blocks = get_option('blockxpert_active', $this->get_default_blocks());
        foreach ($active_blocks as $block) {
            $block_dir = BLOCKXPERT_PATH . 'blocks/' . $block;
            $block_json_path = $block_dir . '/block.json';
            if (file_exists($block_json_path)) {
                
                $callback_method = 'render_dynamic_block_' . str_replace('-', '_', $block);
                if (method_exists($this, $callback_method)) {
                    register_block_type($block_dir, [
                        'render_callback' => [$this, $callback_method],
                    ]);
                }
            }
        }
    }

    private function get_default_blocks() {
        return ['product-slider', 'ai-faq', 'ai-product-recommendations'];
    }

    // Render callback for product-slider
    public function render_dynamic_block_product_slider($attributes) {
        if (!class_exists('WooCommerce')) {
            return '<div class="notice notice-error"><p>' . esc_html__('WooCommerce is required for the Product Slider block.', 'blockxpert') . '</p></div>';
        }
        $title = $attributes['title'] ?? __('WooProduct Slider', 'blockxpert');
        $products_per_slide = $attributes['productsPerSlide'] ?? 3;
        $auto_play = $attributes['autoPlay'] ?? true;
        $show_navigation = $attributes['showNavigation'] ?? true;
        $show_pagination = $attributes['showPagination'] ?? true;
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'orderby' => 'date',
            'order' => 'desc',
        ];
        $products = new \WP_Query($args);
        $product_items = [];
        if ($products->have_posts()) {
            while ($products->have_posts()) {
                $products->the_post();
                global $product;
                $image = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'woocommerce_thumbnail') : wc_placeholder_img_src();
                $product_items[] = [
                    'title' => get_the_title(),
                    'price_html' => $product ? $product->get_price_html() : '',
                    'image' => $image,
                    'permalink' => get_permalink(),
                ];
            }
        }
        wp_reset_postdata();
        ob_start();
        ?>
        <div class="product-slider-editor-preview"
            data-products-per-slide="<?php echo esc_attr($products_per_slide); ?>"
            data-auto-play="<?php echo esc_attr($auto_play ? 'true' : 'false'); ?>"
            data-show-navigation="<?php echo esc_attr($show_navigation ? 'true' : 'false'); ?>"
            data-show-pagination="<?php echo esc_attr($show_pagination ? 'true' : 'false'); ?>"
        >
            <h3 class="slider-title"><?php echo esc_html($title); ?></h3>
            <div class="slider-container-preview">
                <div class="slider-wrapper-preview">
                    <div class="slider-track-preview">
                        <?php foreach ($product_items as $item): ?>
                            <div class="product-item-preview">
                                <div class="product-card-preview">
                                    <div class="product-image-preview">
                                        <a href="<?php echo esc_url($item['permalink']); ?>">
                                            <img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['title']); ?>" />
                                        </a>
                                    </div>
                                    <div class="product-info-preview">
                                        <h4 class="product-title-preview"><?php echo esc_html($item['title']); ?></h4>
                                        <p class="product-price-preview"><?php echo $item['price_html']; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if ($show_navigation): ?>
                <button class="slider-nav-preview prev" aria-label="Previous">‹</button>
                <button class="slider-nav-preview next" aria-label="Next">›</button>
                <?php endif; ?>
                <?php if ($show_pagination): ?>
                <div class="slider-pagination-preview"></div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // Render callback for ai-faq
    public function render_dynamic_block_ai_faq($attributes) {
        $title = $attributes['title'] ?? __('Frequently Asked Questions', 'blockxpert');
        $questions = $attributes['questions'] ?? [];
        
        $wrapper_attributes = get_block_wrapper_attributes([
            'data-animation-type' => $attributes['animationType'] ?? 'slide',
            'data-animation-duration' => $attributes['animationDuration'] ?? 300,
        ]);

        $title_style = '';
        if (!empty($attributes['titleColor'])) {
            $title_style .= 'color:' . esc_attr($attributes['titleColor']) . ';';
        }
        if (!empty($attributes['titleFontSize'])) {
            $title_style .= 'font-size:' . esc_attr($attributes['titleFontSize']) . ';';
        }

        $question_style = '';
        if (!empty($attributes['questionColor'])) {
            $question_style .= 'color:' . esc_attr($attributes['questionColor']) . ';';
        }
        if (!empty($attributes['questionFontSize'])) {
            $question_style .= 'font-size:' . esc_attr($attributes['questionFontSize']) . ';';
        }

        $answer_style = '';
        if (!empty($attributes['answerColor'])) {
            $answer_style .= 'color:' . esc_attr($attributes['answerColor']) . ';';
        }
        if (!empty($attributes['answerFontSize'])) {
            $answer_style .= 'font-size:' . esc_attr($attributes['answerFontSize']) . ';';
        }

        ob_start();
        ?>
        <div <?php echo $wrapper_attributes; ?>>
            <div class="ai-faq-editor">
                <h2 class="faq-title" style="<?php echo $title_style; ?>"><?php echo esc_html($title); ?></h2>
                <div class="faq-questions">
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="faq-question" data-faq-index="<?php echo esc_attr($index); ?>">
                            <div class="faq-question-content">
                                <div class="faq-question-header">
                                    <h3 class="faq-question-text" style="<?php echo esc_attr(
                                        (!empty($question['questionColor']) ? 'color:' . esc_attr($question['questionColor']) . ';' : $question_style)
                                        . (!empty($question['questionFontSize']) ? 'font-size:' . esc_attr($question['questionFontSize']) . ';' : '')
                                    ); ?>"><?php echo esc_html($question['question'] ?? __('Untitled Question', 'blockxpert')); ?></h3>
                                    <div class="faq-question-actions">
                                        <span class="faq-toggle-icon">+</span>
                                    </div>
                                </div>
                                <div class="faq-answer">
                                    <p style="<?php echo esc_attr(
                                        (!empty($question['answerColor']) ? 'color:' . esc_attr($question['answerColor']) . ';' : $answer_style)
                                        . (!empty($question['answerFontSize']) ? 'font-size:' . esc_attr($question['answerFontSize']) . ';' : '')
                                    ); ?>"><?php echo esc_html($question['answer'] ?? __('No answer provided.', 'blockxpert')); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // Render callback for ai-product-recommendations
    public function render_dynamic_block_ai_product_recommendations($attributes) {
        $title = $attributes['title'] ?? __('Ai Product Recom', 'blockxpert');
        $products = $attributes['recommendedProducts'] ?? [];
        $showPrice = $attributes['showPrice'] ?? true;
        $showRating = $attributes['showRating'] ?? true;
        $showAddToCart = $attributes['showAddToCart'] ?? true;
        $layoutStyle = $attributes['layoutStyle'] ?? 'grid';
        $theme = $attributes['theme'] ?? 'light';

        // If no recommended products, fetch recent WooCommerce products
        if (empty($products) && class_exists('WooCommerce')) {
            $args = [
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => $attributes['productsCount'] ?? 4,
                'orderby' => 'date',
                'order' => 'DESC',
            ];
            $query = new \WP_Query($args);
            $products = [];
            while ($query->have_posts()) {
                $query->the_post();
                global $product;
                if (!$product) {
                    $product = wc_get_product(get_the_ID());
                }
                $products[] = [
                    'id' => $product->get_id(),
                    'name' => $product->get_name(),
                    'images' => [['src' => $product->get_image_id() ? wp_get_attachment_url($product->get_image_id()) : wc_placeholder_img_src()]],
                    'price_html' => $product->get_price_html(),
                    'price' => $product->get_price(),
                    'average_rating' => $product->get_average_rating(),
                    'review_count' => $product->get_review_count(),
                    'stock_status' => $product->get_stock_status(),
                ];
            }
            wp_reset_postdata();
        }

        ob_start();
        ?>
        <div class="ai-product-recommendations theme-<?php echo esc_attr($theme); ?>">
            <div class="ai-product-recommendations-editor">
                <h2 class="recommendations-title"><?php echo esc_html($title); ?></h2>
                <div class="products-grid layout-<?php echo esc_attr($layoutStyle); ?>">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card<?php echo $layoutStyle === 'slider' ? ' slider-item' : ''; ?>">
                            <div class="product-image">
                                <?php if (!empty($product['images'][0]['src'])): ?>
                                    <img src="<?php echo esc_url($product['images'][0]['src']); ?>" alt="<?php echo esc_attr($product['name']); ?>" class="product-thumbnail" />
                                <?php else: ?>
                                    <div class="product-placeholder"><?php esc_html_e('No Image', 'blockxpert'); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title"><?php echo esc_html($product['name']); ?></h3>
                                <?php if ($showPrice): ?>
                                    <div class="product-price">
                                        <?php if (!empty($product['price_html'])): ?>
                                            <?php echo $product['price_html']; ?>
                                        <?php else: ?>
                                            <?php echo esc_html($product['price'] ?? __('Price not available', 'blockxpert')); ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($showRating && !empty($product['average_rating'])): ?>
                                    <div class="product-rating">
                                        <?php echo str_repeat('★', round($product['average_rating'])); ?>
                                        <span class="rating-count">(<?php echo esc_html($product['review_count'] ?? 0); ?>)</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($showAddToCart && ($product['stock_status'] ?? '') === 'instock'): ?>
                                    <button class="add-to-cart-btn"><?php esc_html_e('Add to Cart', 'blockxpert'); ?></button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // Render callback for advanced-post-block
    public function render_dynamic_block_advanced_post_block($attributes) {
        // Placeholder output for now
        return '<div class="apb-frontend-placeholder">Advanced Post Block will render here. (Layout: ' . esc_html($attributes['layout'] ?? 'grid') . ')</div>';
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

    public function enqueue_editor_assets() {
        $asset_file = include(BLOCKXPERT_PATH . 'build/index.asset.php');
        
        wp_enqueue_script('wp-api-fetch');
        wp_enqueue_script(
            'blockxpert-editor',
            BLOCKXPERT_URL . 'build/index.js',
            $asset_file['dependencies'],
            $asset_file['version'],
            true
        );
    }
} 