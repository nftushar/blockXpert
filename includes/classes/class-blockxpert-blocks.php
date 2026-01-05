<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

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
        $active_blocks = get_option('blockxpert_blocks_active', $this->get_default_blocks());
        foreach ($active_blocks as $block) {
            $block_dir = BLOCKXPERT_PATH . 'src/blocks/' . $block;
            $block_json_path = $block_dir . '/block.json';
            
            if (file_exists($block_json_path)) {
                $callback_method = 'render_dynamic_block_' . str_replace('-', '_', $block);
                if (method_exists($this, $callback_method)) {
                    register_block_type($block_dir, [
                        'render_callback' => [$this, $callback_method],
                    ]);
                } else {
                    // Register block from block.json without a PHP render callback (client-side or JSON-based)
                    register_block_type($block_dir);
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
            return '<div class="notice notice-error"><p>' . esc_html__('WooCommerce is required for the Product Slider block.', 'BlockXpert') . '</p></div>';
        }
        
        $title = $attributes['title'] ?? esc_html__('Product Slider', 'BlockXpert');
        $products_per_slide = isset($attributes['productsPerSlide']) ? intval($attributes['productsPerSlide']) : 3;
        $auto_play = isset($attributes['autoPlay']) ? (bool)$attributes['autoPlay'] : true;
        $show_navigation = isset($attributes['showNavigation']) ? (bool)$attributes['showNavigation'] : true;
        $show_pagination = isset($attributes['showPagination']) ? (bool)$attributes['showPagination'] : true;
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
        ];
        
        if (!empty($category)) {
            // Allow slug or term_id
            if (is_numeric($category)) {
                $term_field = 'term_id';
                $term = intval($category);
            } else {
                $term_field = 'slug';
                $term = sanitize_text_field($category);
            }

            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field' => $term_field,
                    'terms' => $term,
                    'include_children' => false,
                    'operator' => 'IN',
                ],
            ];

            // Add query optimization hints
            $args['no_found_rows'] = true;
            $args['update_post_meta_cache'] = false;
            $args['update_post_term_cache'] = false;
        }
        
        $query = new \WP_Query($args);
        $products = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                global $product;
                if (!$product) {
                    $product = wc_get_product(get_the_ID());
                }

                $image_id = $product->get_image_id();
                $products[] = [
                    'id' => $product->get_id(),
                    'name' => $product->get_name(),
                    'price_html' => $product->get_price_html(),
                    'price' => $product->get_price(),
                    'image_id' => $image_id ?: 0,
                    'image_url' => $image_id ? wp_get_attachment_url($image_id) : wc_placeholder_img_src(),
                    'url' => get_permalink(),
                ];
            }
            wp_reset_postdata();
        }
        
        if (empty($products)) {
            return '<div class="product-slider-placeholder"><p>' . esc_html__('No products found.', 'BlockXpert') . '</p></div>';
        }
        
        // Build slider HTML
        $output = '<div class="product-slider" data-products-per-slide="' . esc_attr($products_per_slide) . '" data-auto-play="' . ($auto_play ? 'true' : 'false') . '" data-show-navigation="' . ($show_navigation ? 'true' : 'false') . '" data-show-pagination="' . ($show_pagination ? 'true' : 'false') . '">';
        $output .= '<h2 style="text-align:center;margin-bottom:20px;">' . esc_html($title) . '</h2>';
        $output .= '<div class="product-slider-container">';
        
        foreach ($products as $product) {
            $output .= '<div class="product-slide">';
            $output .= '<div class="product-image">';
            if (!empty($product['image_id'])) {
                $output .= wp_get_attachment_image($product['image_id'], 'woocommerce_thumbnail', false, ['alt' => esc_attr($product['name'])]);
            } else {
                $output .= '<img src="' . esc_url($product['image_url']) . '" alt="' . esc_attr($product['name']) . '">';
            }
            $output .= '</div>';
            $output .= '<h3 class="product-title">' . esc_html($product['name']) . '</h3>';
            $output .= '<div class="product-price">' . wp_kses_post($product['price_html']) . '</div>';
            $output .= '<a href="' . esc_url($product['url']) . '" class="product-link">' . esc_html__('View Product', 'BlockXpert') . '</a>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        if ($show_navigation) {
            $output .= '<button class="slider-nav prev">‹</button>';
            $output .= '<button class="slider-nav next">›</button>';
        }
        
        if ($show_pagination) {
            $output .= '<div class="slider-pagination"></div>';
        }
        
        $output .= '</div>';
        
        return $output;
    }

    // Render callback for product-carousel (reuses product-slider rendering)
    public function render_dynamic_block_product_carousel($attributes) {
        return $this->render_dynamic_block_product_slider($attributes);
    }

    // Render callback for ai-faq
    public function render_dynamic_block_ai_faq($attributes) {
        $title = $attributes['title'] ?? esc_html__('Frequently Asked Questions', 'BlockXpert');
        $questions = $attributes['questions'] ?? [];
        $show_search = $attributes['showSearch'] ?? true;
        $accordion_style = $attributes['accordionStyle'] ?? 'default';
        
        if (empty($questions)) {
            return '<div class="ai-faq-placeholder"><p>' . esc_html__('No FAQ questions available. Add some questions in the block settings.', 'BlockXpert') . '</p></div>';
        }
        
        $output = '<div class="ai-faq-block">';
        $output .= '<h2 class="ai-faq-title">' . esc_html($title) . '</h2>';
        
        if ($show_search) {
            $output .= '<div class="ai-faq-search"><input type="text" placeholder="' . esc_attr__('Search questions...', 'BlockXpert') . '" class="ai-faq-search-input"></div>';
        }
        
        $output .= '<div class="ai-faq-questions">';
        
        foreach ($questions as $question) {
            $output .= '<div class="ai-faq-item">';
            $output .= '<h3 class="ai-faq-question" data-accordion="' . esc_attr($accordion_style) . '">' . esc_html($question['question'] ?? esc_html__('Untitled Question', 'BlockXpert')) . '</h3>';
            $output .= '<div class="ai-faq-answer">';
            $output .= '<p>' . esc_html($question['answer'] ?? esc_html__('No answer provided.', 'BlockXpert')) . '</p>';
            $output .= '</div>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }

    // Render callback for ai-product-recommendations
    public function render_dynamic_block_ai_product_recommendations($attributes) {
        if (!class_exists('WooCommerce')) {
            return '<div class="notice notice-error"><p>' . esc_html__('WooCommerce is required for the Product Recommend AI block.', 'BlockXpert') . '</p></div>';
        }
        
        $title = $attributes['title'] ?? esc_html__('Ai Product Recom', 'BlockXpert');
        $recommended_products = $attributes['recommendedProducts'] ?? [];
        
        if (empty($recommended_products)) {
            return '<div class="ai-product-recommendations-placeholder"><p>' . esc_html__('No product recommendations available. Generate some recommendations in the block settings.', 'BlockXpert') . '</p></div>';
        }
        
        $output = '<div class="ai-product-recommendations-block">';
        $output .= '<h2 class="ai-product-recommendations-title">' . esc_html($title) . '</h2>';
        $output .= '<div class="ai-product-recommendations-grid">';
        
        foreach ($recommended_products as $product) {
            $output .= '<div class="ai-product-recommendation-item">';
            $output .= '<div class="product-image">';
            if (!empty($product['images'][0]['src'])) {
                // Try to get attachment ID from the image URL
                $attachment_id = attachment_url_to_postid($product['images'][0]['src']);
                if ($attachment_id) {
                    $output .= wp_get_attachment_image($attachment_id, 'woocommerce_thumbnail', false, ['alt' => esc_attr($product['name'])]);
                } else {
                    $output .= '<img src="' . esc_url($product['images'][0]['src']) . '" alt="' . esc_attr($product['name']) . '">';
                }
            } else {
                $output .= '<div class="product-placeholder">' . esc_html__('No Image', 'BlockXpert') . '</div>';
            }
            $output .= '</div>';
            $output .= '<h3 class="product-title">' . esc_html($product['name']) . '</h3>';
            $output .= '<div class="product-price">' . wp_kses_post($product['price_html'] ?? esc_html__('Price not available', 'BlockXpert')) . '</div>';
            
            if ($product['average_rating'] > 0) {
                $output .= '<div class="product-rating">';
                $output .= '<span class="stars">' . esc_html(str_repeat('★', round($product['average_rating']))) . '</span>';
                $output .= '<span class="rating-count">(' . esc_html($product['review_count']) . ')</span>';
                $output .= '</div>';
            }
            
            $output .= '<button class="add-to-cart-btn">' . esc_html__('Add to Cart', 'BlockXpert') . '</button>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }

    // Render callback for advanced-post-block
    public function render_dynamic_block_advanced_post_block($attributes) {
        $layout = $attributes['layout'] ?? 'grid';
        $posts_to_show = $attributes['postsToShow'] ?? 6;
        $columns = $attributes['columns'] ?? 3;
        $category = $attributes['category'] ?? '';
        $show_excerpt = $attributes['showExcerpt'] ?? true;
        $show_date = $attributes['showDate'] ?? true;
        $show_author = $attributes['showAuthor'] ?? true;
        $show_image = $attributes['showImage'] ?? true;
        
        // Query args
        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $posts_to_show,
            'ignore_sticky_posts' => true,
            'no_found_rows' => true,
        ];
        
        if (!empty($category)) {
            $args['cat'] = intval($category);
        }
        
        $query = new \WP_Query($args);
        
        if (!$query->have_posts()) {
            return '<div class="wp-block-blockxpert-advanced-post-block no-posts">' . 
                esc_html__('No posts found.', 'BlockXpert') . 
                '</div>';
        }
        
        $wrapper_class = 'wp-block-blockxpert-advanced-post-block';
        $wrapper_class .= ' layout-' . esc_attr($layout);
        if ($layout === 'grid') {
            $wrapper_class .= ' columns-' . esc_attr($columns);
        }
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr($wrapper_class); ?>">
            <div class="post-grid">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <article <?php post_class('post-item'); ?>>
                        <?php if ($show_image && has_post_thumbnail()) : ?>
                            <div class="post-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-content">
                            <h3 class="post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            
                            <?php if ($show_date || $show_author) : ?>
                                <div class="post-meta">
                                    <?php if ($show_date) : ?>
                                        <span class="post-date">
                                            <?php echo esc_html(get_the_date()); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($show_author) : ?>
                                        <span class="post-author">
                                            <?php esc_html_e('by', 'BlockXpert'); ?> 
                                            <?php the_author(); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($show_excerpt) : ?>
                                <div class="post-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        </div>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }

    public function add_block_category($categories) {
        foreach ($categories as $category) {
            if ($category['slug'] === 'blockxpert') {
                return $categories;
            }
        }
        return array_merge($categories, [[
            'slug'  => 'blockxpert',
            'title' => __('BlockXpert', 'BlockXpert'),
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