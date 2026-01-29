<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * BlockXpert_Blocks
 * Handles block registration and asset enqueueing
 * Uses Singleton pattern for single instance
 */
class BlockXpert_Blocks {
    private static $instance = null;

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
        add_action( 'init', [ $this, 'register_blocks' ] );
        add_filter( 'block_categories_all', [ $this, 'add_block_category' ], 10, 1 );
        add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_assets' ] );
        add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_dependencies' ] );

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
    }

    public function register_blocks() {
        $blocks_root = trailingslashit( BLOCKXPERT_PATH . 'src/blocks' );
        $active_blocks = get_option( 'blockxpert_blocks_active', BlockXpert::get_all_blocks() );

        foreach ( (array) $active_blocks as $block ) {
            $block = sanitize_key( $block );
            $dir   = $blocks_root . $block;

            if ( ! file_exists($dir.'/block.json') ) continue;

            $callback = 'render_dynamic_block_'.str_replace('-','_',$block);

            if ( method_exists($this, $callback) ) {
                register_block_type($dir, ['render_callback'=>[$this,$callback]]);
            } else {
                register_block_type($dir);
            }
        }
    }

    public function add_block_category( $categories ) {
        $categories[] = ['slug'=>'blockxpert','title'=>__('BlockXpert','blockxpert')];
        return $categories;
    }

    /**
     * Enqueue dependencies needed for block editor
     */
    public function enqueue_block_editor_dependencies() {
        // Ensure dashicons are available for block icons
        wp_enqueue_style( 'dashicons' );
    }

     public function enqueue_editor_assets() {
        $blocks_root = BLOCKXPERT_PATH . 'build/';
        $blocks = get_option( 'blockxpert_blocks_active', BlockXpert::get_all_blocks() );

        foreach ( (array) $blocks as $block ) {
            $block_dir = $blocks_root . $block . '/';

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
        $blocks_root = BLOCKXPERT_PATH . 'build/';
        $blocks = get_option( 'blockxpert_blocks_active', BlockXpert::get_all_blocks() );

        foreach ( (array) $blocks as $block ) {
            $block_dir = $blocks_root.$block.'/';

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
