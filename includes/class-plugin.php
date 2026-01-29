<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Main BlockXpert plugin class. Initializes all features.
 */
class BlockXpert {
    /** @var BlockXpert_Blocks */
    public $blocks;
    /** @var BlockXpert_REST */
    public $rest;
    /** @var BlockXpert_Admin_Settings */
    public $admin_settings;

    public function __construct() {
        $this->blocks = new BlockXpert_Blocks();
        $this->rest = new BlockXpert_REST();
        $this->admin_settings = new BlockXpert_Admin_Settings();
    }

    /**
     * Return the list of available block slugs by scanning the plugin's blocks directory.
     * This centralizes discovery so all callers use the same source of truth.
     *
     * @return array
     */
    public static function get_all_blocks() {
        $blocks = [];
        $blocks_dir = trailingslashit( BLOCKXPERT_PATH . 'src/blocks' );

        if ( is_dir( $blocks_dir ) ) {
            foreach ( scandir( $blocks_dir ) as $item ) {
                if ( $item === '.' || $item === '..' ) continue;
                $full = $blocks_dir . $item;
                if ( is_dir( $full ) ) {
                    $blocks[] = sanitize_key( $item );
                }
            }
        }

        return array_values( array_unique( $blocks ) );
    }
} 