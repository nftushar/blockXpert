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
        add_action('rest_api_init', [$this, 'register_rest_routes']);
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
        return ['block-one', 'block-two', 'block-three', 'product-slider', 'ai-faq', 'ai-product-recommendations'];
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
     * Render callback for ai-faq
     */
    public function render_dynamic_block_ai_faq($attributes)
    {
        $title = $attributes['title'] ?? __('Frequently Asked Questions', 'blockxpert');
        $ai_enabled = $attributes['aiEnabled'] ?? true;
        $max_questions = $attributes['maxQuestions'] ?? 5;
        $auto_generate = $attributes['autoGenerate'] ?? true;
        $show_search = $attributes['showSearch'] ?? true;
        $accordion_style = $attributes['accordionStyle'] ?? 'expandable';
        $theme = $attributes['theme'] ?? 'light';
        $questions = $attributes['questions'] ?? [];
        $api_key = $attributes['apiKey'] ?? '';
        $model = $attributes['model'] ?? 'gpt-3.5-turbo';

        $unique_id = 'ai-faq-' . uniqid();

        ob_start();
        ?>
        <div class="ai-faq-block theme-<?php echo esc_attr($theme); ?>" id="<?php echo esc_attr($unique_id); ?>">
            <?php if (!empty($title)) : ?>
                <h2 class="faq-title"><?php echo esc_html($title); ?></h2>
            <?php endif; ?>

            <?php if ($show_search) : ?>
                <div class="faq-search">
                    <input 
                        type="text" 
                        class="faq-search-input" 
                        placeholder="<?php esc_attr_e('Search questions...', 'blockxpert'); ?>"
                        aria-label="<?php esc_attr_e('Search FAQ questions', 'blockxpert'); ?>"
                    >
                </div>
            <?php endif; ?>

            <?php if (!empty($questions)) : ?>
                <div class="faq-questions accordion-style-<?php echo esc_attr($accordion_style); ?>">
                    <?php foreach ($questions as $index => $question) : ?>
                        <div class="faq-question" data-index="<?php echo esc_attr($index); ?>">
                            <div class="faq-question-header">
                                <h3 class="faq-question-text">
                                    <?php echo esc_html($question['question'] ?? __('Untitled Question', 'blockxpert')); ?>
                                </h3>
                                <span class="faq-toggle-icon">+</span>
                            </div>
                            <div class="faq-answer">
                                <div class="faq-answer-content">
                                    <?php echo wp_kses_post(wpautop($question['answer'] ?? __('No answer provided.', 'blockxpert'))); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="faq-empty">
                    <p><?php esc_html_e('No FAQ questions available.', 'blockxpert'); ?></p>
                    <?php if ($ai_enabled && !empty($api_key)) : ?>
                        <p><?php esc_html_e('Use the block settings to generate AI-powered questions.', 'blockxpert'); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <style>
            .ai-faq-block {
                max-width: 800px;
                margin: 2rem auto;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }

            .ai-faq-block.theme-light {
                background: #fff;
                color: #333;
            }

            .ai-faq-block.theme-dark {
                background: #1a1a1a;
                color: #fff;
            }

            .ai-faq-block.theme-minimal {
                background: transparent;
                color: inherit;
            }

            .faq-title {
                text-align: center;
                margin-bottom: 2rem;
                font-size: 2rem;
                font-weight: 600;
            }

            .faq-search {
                margin-bottom: 2rem;
            }

            .faq-search-input {
                width: 100%;
                padding: 12px 16px;
                border: 2px solid #e1e5e9;
                border-radius: 8px;
                font-size: 16px;
                transition: border-color 0.3s ease;
            }

            .faq-search-input:focus {
                outline: none;
                border-color: #007cba;
            }

            .faq-questions {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .faq-question {
                border: 1px solid #e1e5e9;
                border-radius: 8px;
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .faq-question:hover {
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            .faq-question-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem 1.5rem;
                background: #f8f9fa;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }

            .faq-question-header:hover {
                background: #e9ecef;
            }

            .faq-question-text {
                margin: 0;
                font-size: 1.1rem;
                font-weight: 500;
                flex: 1;
            }

            .faq-toggle-icon {
                font-size: 1.5rem;
                font-weight: bold;
                color: #6c757d;
                transition: transform 0.3s ease;
            }

            .faq-answer {
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease;
            }

            .faq-answer-content {
                padding: 1rem 1.5rem;
                line-height: 1.6;
            }

            .faq-question.active .faq-toggle-icon {
                transform: rotate(45deg);
            }

            .faq-question.active .faq-answer {
                max-height: 500px;
            }

            .faq-empty {
                text-align: center;
                padding: 3rem 1rem;
                color: #6c757d;
            }

            /* Accordion styles */
            .accordion-style-single-open .faq-question.active {
                border-color: #007cba;
            }

            .accordion-style-always-open .faq-answer {
                max-height: none;
            }

            .accordion-style-always-open .faq-toggle-icon {
                display: none;
            }

            /* Dark theme adjustments */
            .ai-faq-block.theme-dark .faq-question {
                border-color: #404040;
            }

            .ai-faq-block.theme-dark .faq-question-header {
                background: #2d2d2d;
            }

            .ai-faq-block.theme-dark .faq-question-header:hover {
                background: #404040;
            }

            .ai-faq-block.theme-dark .faq-search-input {
                background: #2d2d2d;
                border-color: #404040;
                color: #fff;
            }
        </style>

        <script>
        (function() {
            const faqBlock = document.getElementById('<?php echo esc_js($unique_id); ?>');
            if (!faqBlock) return;

            const questions = faqBlock.querySelectorAll('.faq-question');
            const searchInput = faqBlock.querySelector('.faq-search-input');
            const accordionStyle = '<?php echo esc_js($accordion_style); ?>';

            // Handle question toggling
            questions.forEach(question => {
                const header = question.querySelector('.faq-question-header');
                header.addEventListener('click', () => {
                    if (accordionStyle === 'single-open') {
                        // Close all other questions
                        questions.forEach(q => {
                            if (q !== question) {
                                q.classList.remove('active');
                            }
                        });
                    }
                    
                    question.classList.toggle('active');
                });
            });

            // Handle search functionality
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase();
                    
                    questions.forEach(question => {
                        const questionText = question.querySelector('.faq-question-text').textContent.toLowerCase();
                        const answerText = question.querySelector('.faq-answer-content').textContent.toLowerCase();
                        
                        if (questionText.includes(searchTerm) || answerText.includes(searchTerm)) {
                            question.style.display = 'block';
                        } else {
                            question.style.display = 'none';
                        }
                    });
                });
            }

            // Auto-expand first question for single-open style
            if (accordionStyle === 'single-open' && questions.length > 0) {
                questions[0].classList.add('active');
            }
        })();
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Render callback for ai-product-recommendations
     */
    public function render_dynamic_block_ai_product_recommendations($attributes)
    {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            return '<div class="notice notice-error"><p>' . esc_html__('WooCommerce is required for the AI Product Recommendations block.', 'blockxpert') . '</p></div>';
        }

        $title = $attributes['title'] ?? __('Recommended for You', 'blockxpert');
        $ai_enabled = $attributes['aiEnabled'] ?? true;
        $recommendation_type = $attributes['recommendationType'] ?? 'related';
        $layout_style = $attributes['layoutStyle'] ?? 'grid';
        $products_count = $attributes['productsCount'] ?? 4;
        $theme = $attributes['theme'] ?? 'light';
        $show_price = $attributes['showPrice'] ?? true;
        $show_rating = $attributes['showRating'] ?? true;
        $show_add_to_cart = $attributes['showAddToCart'] ?? true;
        $in_stock_only = $attributes['inStockOnly'] ?? true;
        $exclude_current = $attributes['excludeCurrent'] ?? true;
        $price_range = $attributes['priceRange'] ?? ['min' => 0, 'max' => 1000];
        $custom_prompt = $attributes['customPrompt'] ?? '';
        $api_key = $attributes['apiKey'] ?? '';
        $model = $attributes['model'] ?? 'gpt-3.5-turbo';
        $cache_enabled = $attributes['cacheEnabled'] ?? true;
        $cache_duration = $attributes['cacheDuration'] ?? 3600;
        $current_product_id = $attributes['currentProductId'] ?? 0;
        $recommended_products = $attributes['recommendedProducts'] ?? [];

        $unique_id = 'ai-product-recommendations-' . uniqid();

        // Get current product if on a product page
        $current_product = null;
        if ($current_product_id && is_product()) {
            $current_product = wc_get_product($current_product_id);
        }

        // Get recommended products
        $products = [];
        if (!empty($recommended_products)) {
            // Use AI-generated recommendations
            foreach ($recommended_products as $product_data) {
                if (isset($product_data['id'])) {
                    $product = wc_get_product($product_data['id']);
                    if ($product) {
                        $products[] = $product;
                    }
                }
            }
        } else {
            // Fallback to WooCommerce related products
            if ($current_product) {
                $products = wc_get_related_product_ids($current_product->get_id(), $products_count);
                $products = array_map('wc_get_product', $products);
                $products = array_filter($products);
            } else {
                // Get recent products as fallback
                $args = [
                    'post_type' => 'product',
                    'post_status' => 'publish',
                    'posts_per_page' => $products_count,
                    'orderby' => 'date',
                    'order' => 'DESC',
                ];
                
                if ($in_stock_only) {
                    $args['meta_query'] = [
                        [
                            'key' => '_stock_status',
                            'value' => 'instock',
                            'compare' => '='
                        ]
                    ];
                }

                $query = new WP_Query($args);
                $products = array_map('wc_get_product', $query->posts);
            }
        }

        // Apply filters
        if ($exclude_current && $current_product) {
            $products = array_filter($products, function($product) use ($current_product) {
                return $product && $product->get_id() !== $current_product->get_id();
            });
        }

        if ($in_stock_only) {
            $products = array_filter($products, function($product) {
                return $product && $product->is_in_stock();
            });
        }

        // Limit products count
        $products = array_slice($products, 0, $products_count);

        ob_start();
        ?>
        <div class="ai-product-recommendations theme-<?php echo esc_attr($theme); ?>" id="<?php echo esc_attr($unique_id); ?>">
            <?php if (!empty($title)) : ?>
                <h2 class="recommendations-title"><?php echo esc_html($title); ?></h2>
            <?php endif; ?>

            <?php if (!empty($products)) : ?>
                <div class="recommendations-container layout-<?php echo esc_attr($layout_style); ?>">
                    <div class="products-grid layout-<?php echo esc_attr($layout_style); ?>">
                        <?php foreach ($products as $product) : ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <a href="<?php echo esc_url($product->get_permalink()); ?>">
                                        <?php if ($product->get_image_id()) : ?>
                                            <?php echo wp_kses_post($product->get_image('woocommerce_thumbnail', ['class' => 'product-thumbnail'])); ?>
                                        <?php else : ?>
                                            <img src="<?php echo esc_url(wc_placeholder_img_src()); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" class="product-thumbnail">
                                        <?php endif; ?>
                                    </a>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-title">
                                        <a href="<?php echo esc_url($product->get_permalink()); ?>">
                                            <?php echo esc_html($product->get_name()); ?>
                                        </a>
                                    </h3>
                                    <?php if ($show_price) : ?>
                                        <div class="product-price">
                                            <?php echo wp_kses_post($product->get_price_html()); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($show_rating && $product->get_average_rating()) : ?>
                                        <div class="product-rating">
                                            <?php echo wc_get_rating_html($product->get_average_rating(), $product->get_review_count()); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($show_add_to_cart && $product->is_in_stock()) : ?>
                                        <div class="product-actions">
                                            <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="button add-to-cart">
                                                <?php echo esc_html__('Add to Cart', 'blockxpert'); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else : ?>
                <div class="no-recommendations">
                    <p><?php esc_html_e('No product recommendations available.', 'blockxpert'); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <style>
            .ai-product-recommendations {
                margin: 2rem 0;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }

            .ai-product-recommendations.theme-light {
                background: #fff;
                color: #333;
            }

            .ai-product-recommendations.theme-dark {
                background: #1a1a1a;
                color: #fff;
            }

            .ai-product-recommendations.theme-minimal {
                background: transparent;
                color: inherit;
            }

            .recommendations-title {
                text-align: center;
                margin-bottom: 2rem;
                font-size: 2rem;
                font-weight: 600;
            }

            .recommendations-container {
                max-width: 1200px;
                margin: 0 auto;
            }

            .products-grid {
                display: grid;
                gap: 1.5rem;
            }

            .products-grid.layout-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }

            .products-grid.layout-list {
                grid-template-columns: 1fr;
            }

            .products-grid.layout-slider {
                display: flex;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                gap: 1rem;
                padding: 1rem 0;
            }

            .products-grid.layout-slider .product-card {
                flex: 0 0 250px;
                scroll-snap-align: start;
            }

            .product-card {
                border: 1px solid #e1e5e9;
                border-radius: 8px;
                overflow: hidden;
                transition: all 0.3s ease;
                background: #fff;
            }

            .product-card:hover {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                transform: translateY(-2px);
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
                font-size: 1.1rem;
                font-weight: 500;
            }

            .product-title a {
                color: inherit;
                text-decoration: none;
            }

            .product-title a:hover {
                color: #007cba;
            }

            .product-price {
                font-size: 1.2rem;
                font-weight: 600;
                color: #007cba;
                margin-bottom: 0.5rem;
            }

            .product-rating {
                margin-bottom: 0.5rem;
            }

            .product-actions {
                margin-top: 1rem;
            }

            .add-to-cart {
                width: 100%;
                text-align: center;
                padding: 0.75rem;
                background: #007cba;
                color: #fff;
                border: none;
                border-radius: 4px;
                text-decoration: none;
                display: inline-block;
                transition: background-color 0.3s ease;
            }

            .add-to-cart:hover {
                background: #005a87;
                color: #fff;
            }

            .no-recommendations {
                text-align: center;
                padding: 3rem 1rem;
                color: #6c757d;
            }

            /* Dark theme adjustments */
            .ai-product-recommendations.theme-dark .product-card {
                background: #2d2d2d;
                border-color: #404040;
            }

            .ai-product-recommendations.theme-dark .product-title a:hover {
                color: #4a9eff;
            }

            .ai-product-recommendations.theme-dark .product-price {
                color: #4a9eff;
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {
                .products-grid.layout-grid {
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                }

                .products-grid.layout-slider .product-card {
                    flex: 0 0 200px;
                }
            }
        </style>

        <?php if ($layout_style === 'slider') : ?>
        <script>
        (function() {
            const container = document.getElementById('<?php echo esc_js($unique_id); ?>');
            if (!container) return;

            const slider = container.querySelector('.products-grid.layout-slider');
            if (!slider) return;

            // Add navigation arrows for slider
            const prevBtn = document.createElement('button');
            prevBtn.className = 'slider-nav slider-prev';
            prevBtn.innerHTML = '‹';
            prevBtn.setAttribute('aria-label', '<?php esc_attr_e('Previous', 'blockxpert'); ?>');

            const nextBtn = document.createElement('button');
            nextBtn.className = 'slider-nav slider-next';
            nextBtn.innerHTML = '›';
            nextBtn.setAttribute('aria-label', '<?php esc_attr_e('Next', 'blockxpert'); ?>');

            container.querySelector('.recommendations-container').appendChild(prevBtn);
            container.querySelector('.recommendations-container').appendChild(nextBtn);

            let currentPosition = 0;
            const cardWidth = 250 + 16; // card width + gap
            const maxScroll = slider.scrollWidth - slider.clientWidth;

            prevBtn.addEventListener('click', () => {
                currentPosition = Math.max(0, currentPosition - cardWidth);
                slider.scrollTo({ left: currentPosition, behavior: 'smooth' });
            });

            nextBtn.addEventListener('click', () => {
                currentPosition = Math.min(maxScroll, currentPosition + cardWidth);
                slider.scrollTo({ left: currentPosition, behavior: 'smooth' });
            });

            // Update button states
            slider.addEventListener('scroll', () => {
                prevBtn.style.opacity = slider.scrollLeft > 0 ? '1' : '0.5';
                nextBtn.style.opacity = slider.scrollLeft < maxScroll ? '1' : '0.5';
            });
        })();
        </script>

        <style>
            .recommendations-container {
                position: relative;
            }

            .slider-nav {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                background: rgba(0, 0, 0, 0.7);
                color: #fff;
                border: none;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                font-size: 1.5rem;
                cursor: pointer;
                transition: all 0.3s ease;
                z-index: 10;
            }

            .slider-nav:hover {
                background: rgba(0, 0, 0, 0.9);
            }

            .slider-prev {
                left: -20px;
            }

            .slider-next {
                right: -20px;
            }

            @media (max-width: 768px) {
                .slider-nav {
                    display: none;
                }
            }
        </style>
        <?php endif; ?>
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

    /**
     * Register REST API routes
     */
    public function register_rest_routes()
    {
        register_rest_route('blockxpert/v1', '/generate-faq', [
            'methods' => 'POST',
            'callback' => [$this, 'generate_faq_questions'],
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);

        register_rest_route('blockxpert/v1', '/generate-product-recommendations', [
            'methods' => 'POST',
            'callback' => [$this, 'generate_product_recommendations'],
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);
    }

    /**
     * Generate FAQ questions using OpenAI API
     */
    public function generate_faq_questions($request)
    {
        $params = $request->get_params();
        $api_key = sanitize_text_field($params['apiKey'] ?? '');
        $model = sanitize_text_field($params['model'] ?? 'gpt-3.5-turbo');
        $max_questions = intval($params['maxQuestions'] ?? 5);
        $context = sanitize_textarea_field($params['context'] ?? '');

        if (empty($api_key)) {
            return new WP_Error('missing_api_key', __('OpenAI API key is required.', 'blockxpert'), ['status' => 400]);
        }

        // Prepare the prompt for OpenAI
        $prompt = "Generate {$max_questions} relevant FAQ questions and answers for a website. ";
        if (!empty($context)) {
            $prompt .= "Context: {$context}. ";
        }
        $prompt .= "Format the response as a JSON array with 'question' and 'answer' fields for each FAQ item. Make the questions practical and the answers helpful and informative.";

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant that generates FAQ questions and answers. Always respond with valid JSON format.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.7,
            ]),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('api_error', __('Failed to connect to OpenAI API.', 'blockxpert'), ['status' => 500]);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data) || !isset($data['choices'][0]['message']['content'])) {
            return new WP_Error('invalid_response', __('Invalid response from OpenAI API.', 'blockxpert'), ['status' => 500]);
        }

        $content = $data['choices'][0]['message']['content'];
        
        // Try to extract JSON from the response
        preg_match('/\[.*\]/s', $content, $matches);
        if (empty($matches)) {
            // If no JSON found, create a simple FAQ structure
            $questions = [];
            $lines = explode("\n", $content);
            $current_question = '';
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                if (preg_match('/^\d+\.\s*(.+)$/', $line, $matches)) {
                    if (!empty($current_question)) {
                        $questions[] = [
                            'question' => $current_question,
                            'answer' => 'Answer will be provided by the user.',
                            'id' => time() + count($questions)
                        ];
                    }
                    $current_question = $matches[1];
                }
            }
            
            if (!empty($current_question)) {
                $questions[] = [
                    'question' => $current_question,
                    'answer' => 'Answer will be provided by the user.',
                    'id' => time() + count($questions)
                ];
            }
        } else {
            $questions = json_decode($matches[0], true);
            if (!is_array($questions)) {
                $questions = [];
            }
            
            // Add IDs to questions
            foreach ($questions as &$question) {
                $question['id'] = time() + rand(1, 1000);
            }
        }

        return [
            'success' => true,
            'questions' => array_slice($questions, 0, $max_questions),
        ];
    }

    /**
     * Generate AI product recommendations using OpenAI API
     */
    public function generate_product_recommendations($request)
    {
        $params = $request->get_params();
        $api_key = sanitize_text_field($params['apiKey'] ?? '');
        $model = sanitize_text_field($params['model'] ?? 'gpt-3.5-turbo');
        $current_product = $params['currentProduct'] ?? null;
        $recommendation_type = sanitize_text_field($params['recommendationType'] ?? 'related');
        $products_count = intval($params['productsCount'] ?? 4);
        $custom_prompt = sanitize_textarea_field($params['customPrompt'] ?? '');
        $price_range = $params['priceRange'] ?? ['min' => 0, 'max' => 1000];
        $in_stock_only = boolval($params['inStockOnly'] ?? true);

        if (empty($api_key)) {
            return new WP_Error('missing_api_key', __('OpenAI API key is required.', 'blockxpert'), ['status' => 400]);
        }

        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            return new WP_Error('woocommerce_required', __('WooCommerce is required for product recommendations.', 'blockxpert'), ['status' => 400]);
        }

        // Get all available products for AI to choose from
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 100, // Get more products for AI to choose from
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        if ($in_stock_only) {
            $args['meta_query'] = [
                [
                    'key' => '_stock_status',
                    'value' => 'instock',
                    'compare' => '='
                ]
            ];
        }

        $query = new WP_Query($args);
        $available_products = [];

        foreach ($query->posts as $post) {
            $product = wc_get_product($post->ID);
            if ($product) {
                $available_products[] = [
                    'id' => $product->get_id(),
                    'name' => $product->get_name(),
                    'description' => $product->get_description(),
                    'short_description' => $product->get_short_description(),
                    'price' => $product->get_price(),
                    'categories' => wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'names']),
                    'tags' => wp_get_post_terms($product->get_id(), 'product_tag', ['fields' => 'names']),
                    'attributes' => $product->get_attributes()
                ];
            }
        }

        // Prepare the prompt for OpenAI
        $prompt = "You are an AI product recommendation system for an e-commerce store. ";
        
        if ($current_product) {
            $prompt .= "Current product: {$current_product['name']} - {$current_product['description']}. ";
        }
        
        $prompt .= "Recommendation type: {$recommendation_type}. ";
        
        if (!empty($custom_prompt)) {
            $prompt .= "Custom requirement: {$custom_prompt}. ";
        }
        
        $prompt .= "Available products: " . json_encode($available_products) . ". ";
        $prompt .= "Please recommend exactly {$products_count} product IDs from the available products that best match the criteria. ";
        $prompt .= "Return only a JSON array of product IDs, like: [123, 456, 789]";

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful AI assistant that recommends products. Always respond with valid JSON format containing only product IDs.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('api_error', __('Failed to connect to OpenAI API.', 'blockxpert'), ['status' => 500]);
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data) || !isset($data['choices'][0]['message']['content'])) {
            return new WP_Error('invalid_response', __('Invalid response from OpenAI API.', 'blockxpert'), ['status' => 500]);
        }

        $content = $data['choices'][0]['message']['content'];
        
        // Try to extract JSON from the response
        preg_match('/\[.*\]/s', $content, $matches);
        if (empty($matches)) {
            // If no JSON found, return random products as fallback
            $product_ids = array_slice(array_column($available_products, 'id'), 0, $products_count);
        } else {
            $product_ids = json_decode($matches[0], true);
            if (!is_array($product_ids)) {
                $product_ids = array_slice(array_column($available_products, 'id'), 0, $products_count);
            }
        }

        // Get the recommended products
        $recommended_products = [];
        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                $recommended_products[] = [
                    'id' => $product->get_id(),
                    'name' => $product->get_name(),
                    'price' => $product->get_price(),
                    'price_html' => $product->get_price_html(),
                    'image' => $product->get_image_id() ? wp_get_attachment_image_src($product->get_image_id(), 'woocommerce_thumbnail')[0] : '',
                    'permalink' => $product->get_permalink(),
                    'stock_status' => $product->get_stock_status(),
                    'average_rating' => $product->get_average_rating(),
                    'review_count' => $product->get_review_count()
                ];
            }
        }

        return [
            'success' => true,
            'products' => array_slice($recommended_products, 0, $products_count),
        ];
    }
}

new BlockXpert_Init();
