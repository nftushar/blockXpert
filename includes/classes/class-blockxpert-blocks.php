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
            <h2><?php echo esc_html($attributes['title'] ?? 'Block One'); ?></h2>
            <p><?php echo esc_html__('This is Block One. Replace this with your custom content.', 'blockxpert'); ?></p>
            <pre style="background:#f8f8f8;padding:10px;border-radius:4px;">Attributes: <?php echo esc_html(json_encode($attributes)); ?></pre>
        </div>
        <?php
        return ob_get_clean();
    }

    // Render callback for block-two
    public function render_dynamic_block_block_two($attributes) {
        ob_start();
        ?>
        <div class="dynamic-block block-two">
            <h2><?php echo esc_html($attributes['title'] ?? 'Block Two'); ?></h2>
            <p><?php echo esc_html__('This is Block Two. Replace this with your custom content.', 'blockxpert'); ?></p>
            <pre style="background:#f8f8f8;padding:10px;border-radius:4px;">Attributes: <?php echo esc_html(json_encode($attributes)); ?></pre>
        </div>
        <?php
        return ob_get_clean();
    }

    // Render callback for block-three
    public function render_dynamic_block_block_three($attributes) {
        ob_start();
        ?>
        <div class="dynamic-block block-three">
            <h2><?php echo esc_html($attributes['title'] ?? 'Block Three'); ?></h2>
            <p><?php echo esc_html__('This is Block Three. Replace this with your custom content.', 'blockxpert'); ?></p>
            <pre style="background:#f8f8f8;padding:10px;border-radius:4px;">Attributes: <?php echo esc_html(json_encode($attributes)); ?></pre>
        </div>
        <?php
        return ob_get_clean();
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
        ob_start();
        ?>
        <div class="ai-faq-block theme-<?php echo esc_attr($attributes['theme'] ?? 'light'); ?>">
            <div class="ai-faq-editor">
                <h2 class="faq-title"><?php echo esc_html($title); ?></h2>
                <div class="faq-questions">
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="faq-question" data-faq-index="<?php echo esc_attr($index); ?>">
                            <div class="faq-question-content">
                                <div class="faq-question-header">
                                    <h3 class="faq-question-text"><?php echo esc_html($question['question'] ?? __('Untitled Question', 'blockxpert')); ?></h3>
                                    <div class="faq-question-actions">
                                        <span class="faq-toggle-icon">+</span>
                                    </div>
                                </div>
                                <div class="faq-answer" style="display:none;">
                                    <p><?php echo esc_html($question['answer'] ?? __('No answer provided.', 'blockxpert')); ?></p>
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
        $title = $attributes['title'] ?? __('AI Product Recommendations', 'blockxpert');
        $products = $attributes['recommendedProducts'] ?? [];
        $showPrice = $attributes['showPrice'] ?? true;
        $showRating = $attributes['showRating'] ?? true;
        $showAddToCart = $attributes['showAddToCart'] ?? true;
        $layoutStyle = $attributes['layoutStyle'] ?? 'grid';
        $theme = $attributes['theme'] ?? 'light';
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

    // Render callback for pdf-invoice
    public function render_dynamic_block_pdf_invoice($attributes) {
        $button_text = $attributes['buttonText'] ?? __('Download Invoice (PDF)', 'blockxpert');
        $show_order_id_field = $attributes['showOrderIdField'] ?? false;
        $output = '<div class="blockxpert-pdf-invoice-block">';
        $output .= '<h2>' . esc_html($attributes['title'] ?? 'PDF Invoice') . '</h2>';
        if ($show_order_id_field) {
            $output .= '<form class="blockxpert-invoice-form" method="get" action="" onsubmit="event.preventDefault();var oid=this.order_id.value;if(oid){window.location=\'' . esc_url(rest_url('blockxpert/v1/pdf-invoice')) . '?order_id=\'+encodeURIComponent(oid);}">';
            $output .= '<input type="text" name="order_id" placeholder="Enter Order ID" required style="margin-right:10px;" />';
            $output .= '<button type="submit">' . esc_html($button_text) . '</button>';
            $output .= '</form>';
        } else {
            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
                $orders = wc_get_orders([
                    'customer_id' => $user_id,
                    'limit' => 1,
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'status' => ['wc-completed', 'wc-processing']
                ]);
                if (!empty($orders)) {
                    $order_id = $orders[0]->get_id();
                    $download_url = esc_url(rest_url('blockxpert/v1/pdf-invoice') . '?order_id=' . $order_id);
                    $output .= '<a href="' . $download_url . '" class="button blockxpert-download-invoice">' . esc_html($button_text) . '</a>';
                } else {
                    $output .= '<p>' . esc_html__('No recent orders found for your account.', 'blockxpert') . '</p>';
                }
            } else {
                $output .= '<p>' . esc_html__('Please log in to download your invoice.', 'blockxpert') . '</p>';
            }
        }
        $output .= '</div>';
        return $output;
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