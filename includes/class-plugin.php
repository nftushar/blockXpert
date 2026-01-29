<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Main BlockXpert plugin class. Initializes all features.
 * Uses Singleton pattern to ensure single instance.
 */
class BlockXpert {
    private static $instance = null;
    
    /** @var BlockXpert_Blocks */
    public $blocks;
    
    /** @var BlockXpert_REST */
    public $rest;
    
    /** @var BlockXpert_Admin_Settings */
    public $admin_settings;
    
    /** @var BlockXpert_Service */
    public $service;
    
    /** @var BlockXpert_Cache */
    public $cache;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor - Initialize plugin components
     */
    public function __construct() {
        // Prevent multiple instantiation
        if (self::$instance !== null) {
            return;
        }
        
        // Mark instance as created
        self::$instance = $this;
        
        // Initialize service layer
        $this->service = BlockXpert_Service::get_instance();
        $this->cache = BlockXpert_Cache::get_instance();
        
        // Initialize components
        $this->blocks = BlockXpert_Blocks::get_instance();
        $this->rest = BlockXpert_REST::get_instance();
        $this->admin_settings = BlockXpert_Admin_Settings::get_instance();
        
        // Register hooks
        $this->register_hooks();
    }

    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        // Clear cache when blocks are activated/deactivated
        add_action('update_option_blockxpert_blocks_active', [$this, 'on_blocks_updated']);
        
        // Allow plugins to extend BlockXpert
        do_action('blockxpert_loaded', $this);
    }

    /**
     * Called when block settings are updated
     */
    public function on_blocks_updated() {
        $this->service->clear_cache();
        BlockXpert_Cache::flush_all();
    }

    /**
     * Return the list of available blocks
     * Uses service layer for consistency
     * @return array List of all available block slugs
     */
    public static function get_all_blocks() {
        return self::get_instance()->service->get_all_blocks();
    }
    
    /**
     * Get active blocks
     * @return array List of active block slugs
     */
    public static function get_active_blocks() {
        return self::get_instance()->service->get_active_blocks();
    }
    
    /**
     * Check if block is active
     * @param string $block Block slug
     * @return bool True if active
     */
    public static function is_block_active($block) {
        return self::get_instance()->service->is_block_active($block);
    }
} 