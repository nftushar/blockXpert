<?php
/**
 * BlockManagerInterface
 * Defines contract for block registration and management
 */

if (!defined('ABSPATH')) exit;

interface BlockManagerInterface {
    /**
     * Register all available blocks
     */
    public function register_blocks();
    
    /**
     * Get all available blocks
     * @return array List of block slugs
     */
    public function get_all_blocks();
    
    /**
     * Get active blocks only
     * @return array List of active block slugs
     */
    public function get_active_blocks();
    
    /**
     * Check if a specific block is active
     * @param string $block Block slug
     * @return bool
     */
    public function is_block_active($block);
    
    /**
     * Get block configuration
     * @param string $block Block slug
     * @return array|false
     */
    public function get_block_config($block);
}
