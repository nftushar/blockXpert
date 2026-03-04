<?php
if ( ! defined( 'ABSPATH' ) ) exit;


class BlockXpert_Blocks {
    private static $instance = null;
    private static $category_registered = false;

    /**
     * Static initializer - registers category immediately on class load
     */
    public static function init_category() {
        if ( ! self::$category_registered ) {
            error_log( '[BlockXpert] init_category() called' );
            error_log( '[BlockXpert] is_admin: ' . (is_admin() ? 'true' : 'false') );
            error_log( '[BlockXpert] Registering block category filters...' );
            
            // Register on both hooks for maximum compatibility
            add_filter( 'block_categories', [ __CLASS__, 'register_static_category' ], 5, 2 );
            add_filter( 'block_categories_all', [ __CLASS__, 'register_static_category' ], 5, 1 );
            
            self::$category_registered = true;
            error_log( '[BlockXpert] Block category filters registered.' );
        } else {
            error_log( '[BlockXpert] Block category already registered, skipping.' );
        }
    }

    /**
     * Static method to add block category
     */
    public static function register_static_category( $categories, $post = null ) {
        error_log( '[BlockXpert] register_static_category() called' );
        error_log( '[BlockXpert] post: ' . ($post ? 'ID=' . $post->ID : 'null') );
        error_log( '[BlockXpert] Adding blockxpert category. Current categories: ' . count( $categories ) );
        
        // Check if blockxpert category already exists
        $existing_slugs = wp_list_pluck( $categories, 'slug' );
        if ( ! in_array( 'blockxpert', $existing_slugs ) ) {
            $categories[] = ['slug'=>'blockxpert','title'=>__('BlockXpert','blockxpert')];
            error_log( '[BlockXpert] Category added. Total categories: ' . count( $categories ) );
        } else {
            error_log( '[BlockXpert] Category already exists in list.' );
        }
        
        error_log( '[BlockXpert] Final category slugs: ' . print_r( wp_list_pluck( $categories, 'slug' ), true ) );
        return $categories;
    }

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor - prevents direct instantiation
     */
    private function __construct() {
        // Ensure category is registered
        self::init_category();
        error_log( '[BlockXpert] Constructor: registering init hook' );
        
        // Register the category filter callback to init hook with high priority
        add_action( 'init', [ __CLASS__, 'ensure_category_on_init' ], 0 );
        
        // Add REST API support for categories
        add_action( 'rest_api_init', [ __CLASS__, 'register_category_rest' ] );
        
        add_action( 'init', [ $this, 'register_blocks' ], 10 );
        add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_assets' ] );
        add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_dependencies' ] );

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
    }

    /**
     * Register category via REST API
     */
    public static function register_category_rest() {
        error_log( '[BlockXpert] register_category_rest() called' );
        
        // Register the category as a filterable endpoint
        register_rest_route( 'blockxpert/v1', '/categories', array(
            'methods' => 'GET',
            'callback' => [ __CLASS__, 'get_categories_rest' ],
            'permission_callback' => '__return_true',
        ) );
        
        // Also filter the editor settings to include the category
        add_filter( 'block_editor_settings_all', [ __CLASS__, 'add_category_to_editor_settings' ], 10, 2 );
        add_filter( 'block_editor_settings', [ __CLASS__, 'add_category_to_editor_settings' ], 10, 2 );
    }

    /**
     * Add BlockXpert category to editor settings
     */
    public static function add_category_to_editor_settings( $settings, $post = null ) {
        error_log( '[BlockXpert] add_category_to_editor_settings() called' );
        
        if ( ! isset( $settings['categories'] ) ) {
            $settings['categories'] = [];
        }
        
        // Check if blockxpert category already exists
        $category_exists = false;
        foreach ( $settings['categories'] as $cat ) {
            if ( isset( $cat['slug'] ) && $cat['slug'] === 'blockxpert' ) {
                $category_exists = true;
                break;
            }
        }
        
        if ( ! $category_exists ) {
            error_log( '[BlockXpert] Adding blockxpert category to editor settings' );
            $settings['categories'][] = [
                'slug' => 'blockxpert',
                'title' => __('BlockXpert','blockxpert'),
                'icon' => null
            ];
        }
        
        error_log( '[BlockXpert] Editor categories: ' . print_r( wp_list_pluck( $settings['categories'], 'slug' ), true ) );
        return $settings;
    }

    /**
     * REST endpoint to get categories
     */
    public static function get_categories_rest() {
        error_log( '[BlockXpert] get_categories_rest() called' );
        return [
            [
                'slug' => 'blockxpert',
                'title' => __('BlockXpert','blockxpert'),
            ]
        ];
    }

    /**
     * Ensure category is available on init
     */
    public static function ensure_category_on_init() {
        error_log( '[BlockXpert] ensure_category_on_init() called on init hook' );
        self::init_category();
    }

    public function register_blocks() {
        $blocks_root = trailingslashit( BLOCKXPERT_PATH . 'src/blocks' );
        error_log( '[BlockXpert] Starting block registration...' );

        foreach ( $this->get_active_blocks() as $block ) {
            $block = sanitize_key( $block );
            $dir   = $blocks_root . $block;

            if ( ! file_exists($dir.'/block.json') ) {
                error_log( "[BlockXpert] Skipping $block - block.json not found at $dir" );
                continue;
            }

            // Read block.json to check category
            $block_json_path = $dir . '/block.json';
            $block_config = json_decode( file_get_contents( $block_json_path ), true );
            $category = $block_config['category'] ?? 'unknown';
            
            error_log( "[BlockXpert] Registering block: $block, category: $category" );

            $callback = 'render_dynamic_block_'.str_replace('-','_',$block);

            if ( method_exists($this, $callback) ) {
                register_block_type($dir, ['render_callback'=>[$this,$callback]]);
            } else {
                register_block_type($dir);
            }
            
            error_log( "[BlockXpert] Block registered: $block" );
        }
        
        error_log( '[BlockXpert] Block registration complete.' );
    }

    /**
     * Enqueue dependencies needed for block editor
     */
    public function enqueue_block_editor_dependencies() {
        error_log( '[BlockXpert] enqueue_block_editor_dependencies() called' );
        
        // Ensure dashicons are available for block icons
        wp_enqueue_style( 'dashicons' );
        
        // Register the block category on the client-side with inline script
        wp_enqueue_script(
            'blockxpert-register-category',
            BLOCKXPERT_URL . 'includes/assets/js/register-block-category.js',
            ['wp-blocks', 'wp-i18n'],
            filemtime( BLOCKXPERT_PATH . 'includes/assets/js/register-block-category.js' ),
            false
        );
        
        // Add inline script with IIFE to ensure it runs
        wp_add_inline_script( 'blockxpert-register-category', "
        (function() {
            console.log('[BlockXpert] Inline script running, wp.blocks available:', typeof wp !== 'undefined' && typeof wp.blocks !== 'undefined');
            if (typeof wp === 'undefined' || typeof wp.blocks === 'undefined') {
                console.warn('[BlockXpert] wp.blocks not yet available, will retry');
                setTimeout(() => {
                    if (wp && wp.blocks && wp.blocks.getCategories) {
                        const cats = wp.blocks.getCategories();
                        console.log('[BlockXpert] Current categories:', cats.map(c => c.slug).join(', '));
                    }
                }, 1000);
            }
        })();
        ", 'after' );
        
        error_log( '[BlockXpert] Category registration script enqueued' );
    }

     public function enqueue_editor_assets() {
        foreach ( $this->get_active_blocks() as $block ) {
            $block_dir = $this->get_build_block_dir( $block );

            // Try to enqueue index.js first (primary editor script)
            $js_asset = $block_dir . 'index.asset.php';
            $js_file = $block_dir . 'index.js';

            if ( file_exists( $js_asset ) && file_exists( $js_file ) ) {
                $asset = include $js_asset;

                wp_enqueue_script(
                    "blockxpert-{$block}-editor",
                    BLOCKXPERT_URL . "build/{$block}/index.js",
                    $asset['dependencies'] ?? [],
                    $asset['version'] ?? false,
                    true
                );
            } else {
                // Fallback: Try editor.js if index.js doesn't exist
                $editor_js = $block_dir . 'editor.js';
                $editor_asset = $block_dir . 'editor.asset.php';
                
                if ( file_exists( $editor_asset ) && file_exists( $editor_js ) ) {
                    $asset = include $editor_asset;
                    
                    wp_enqueue_script(
                        "blockxpert-{$block}-editor",
                        BLOCKXPERT_URL . "build/{$block}/editor.js",
                        $asset['dependencies'] ?? [],
                        $asset['version'] ?? false,
                        true
                    );
                }
            }

            // Enqueue editor CSS if it exists
            $editor_css = $block_dir . 'editor.css';
            if ( file_exists( $editor_css ) ) {
                wp_enqueue_style(
                    "blockxpert-{$block}-editor-css",
                    BLOCKXPERT_URL . "build/{$block}/editor.css",
                    [],
                    filemtime( $editor_css )
                );
            }

            // Enqueue index CSS as fallback editor styles
            $index_css = $block_dir . 'index.css';
            if ( file_exists( $index_css ) ) {
                wp_enqueue_style(
                    "blockxpert-{$block}-index-css",
                    BLOCKXPERT_URL . "build/{$block}/index.css",
                    [],
                    filemtime( $index_css )
                );
            }
        }
    }


    public function enqueue_frontend_assets() {
        foreach ( $this->get_active_blocks() as $block ) {
            $block_dir = $this->get_build_block_dir( $block );

            $js_file = $block_dir.'view.js';
            if ( file_exists($js_file) ) {
                wp_enqueue_script(
                    "blockxpert-{$block}-frontend",
                    BLOCKXPERT_URL."build/{$block}/view.js",
                    ['jquery', 'gsap'],
                    filemtime($js_file),
                    true
                );
            }

            $css_file = $block_dir.'style-index.css';
            if ( file_exists($css_file) ) {
                wp_enqueue_style(
                    "blockxpert-{$block}-frontend",
                    BLOCKXPERT_URL."build/{$block}/style-index.css",
                    [],
                    filemtime($css_file)
                );
            }
        }
    }

    private function get_active_blocks() {
        return (array) get_option( 'blockxpert_blocks_active', BlockXpert::get_all_blocks() );
    }

    private function get_build_block_dir( $block ) {
        return trailingslashit( BLOCKXPERT_PATH . 'build/' . sanitize_key( $block ) );
    }

    public function render_dynamic_block_product_slider($attributes) {
        if ( ! class_exists('WooCommerce') ) return '<p>'.esc_html__('WooCommerce required','blockxpert').'</p>';

        $per_slide = absint($attributes['productsPerSlide'] ?? 3);
        $auto_play = !empty($attributes['autoPlay']);

        $query_args = ['post_type'=>'product','posts_per_page'=>12,'post_status'=>'publish','no_found_rows'=>true];

        $query = new WP_Query($query_args);
        if (!$query->have_posts()) return '<p>'.esc_html__('No products found','blockxpert').'</p>';

        ob_start(); ?>
        <div class="blockxpert-product-slider" data-per-slide="<?php echo esc_attr($per_slide); ?>" data-autoplay="<?php echo $auto_play?'true':'false'; ?>">
            <h2 class="slider-title"><?php echo esc_html($attributes['title'] ?? 'Products'); ?></h2>
            <div class="slider-track">
                <?php while ($query->have_posts()): $query->the_post(); ?>
                    <article class="product-card">
                        <?php woocommerce_template_loop_product_thumbnail(); ?>
                        <h3><?php the_title(); ?></h3>
                        <?php woocommerce_template_loop_price(); ?>
                    </article>
                <?php endwhile; ?>
            </div>
            <button class="slider-prev">‹</button>
            <button class="slider-next">›</button>
        </div>
        <?php wp_reset_postdata(); return ob_get_clean();
    }
}