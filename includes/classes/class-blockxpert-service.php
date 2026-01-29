<?php
/**
 * BlockXpert Service Layer
 * Handles business logic and data operations
 */

if (!defined('ABSPATH')) exit;

class BlockXpert_Service {
    private static $instance = null;
    private $blocks_cache = [];
    private $active_blocks_cache = [];
    
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
     * Get all available blocks with caching
     * @return array List of all block slugs
     */
    public function get_all_blocks() {
        // Return cached if available
        if (!empty($this->blocks_cache)) {
            return $this->blocks_cache;
        }
        
        $blocks = [];
        $blocks_dir = BLOCKXPERT_PATH . 'src/blocks';
        
        if (is_dir($blocks_dir)) {
            $items = scandir($blocks_dir);
            if ($items === false) {
                return [];
            }
            
            foreach ($items as $item) {
                if ($item !== '.' && $item !== '..' && is_dir($blocks_dir . '/' . $item)) {
                    $blocks[] = sanitize_key($item);
                }
            }
        }
        
        $this->blocks_cache = array_unique($blocks);
        return $this->blocks_cache;
    }
    
    /**
     * Get active blocks only
     * @return array List of active block slugs
     */
    public function get_active_blocks() {
        // Return cached if available
        if (!empty($this->active_blocks_cache)) {
            return $this->active_blocks_cache;
        }
        
        $default = ['product-slider'];
        $active = (array) get_option('blockxpert_blocks_active', $default);
        
        // Validate against available blocks
        $all_blocks = $this->get_all_blocks();
        $this->active_blocks_cache = array_intersect($active, $all_blocks);
        
        return $this->active_blocks_cache;
    }
    
    /**
     * Check if a specific block is active
     * @param string $block Block slug
     * @return bool True if block is active
     */
    public function is_block_active($block) {
        $block = sanitize_key($block);
        return in_array($block, $this->get_active_blocks(), true);
    }
    
    /**
     * Get block configuration
     * @param string $block Block slug
     * @return array|false Block config or false if not found
     */
    public function get_block_config($block) {
        $block = sanitize_key($block);
        $config_path = BLOCKXPERT_PATH . "src/blocks/{$block}/block.json";
        
        if (!file_exists($config_path)) {
            return false;
        }
        
        $config = json_decode(file_get_contents($config_path), true);
        return is_array($config) ? $config : false;
    }
    
    /**
     * Get block manifest/asset file
     * @param string $block Block slug
     * @param string $type Type of asset (editor, view, index)
     * @return array|false Asset manifest or false if not found
     */
    public function get_block_asset($block, $type = 'index') {
        $block = sanitize_key($block);
        $asset_path = BLOCKXPERT_PATH . "build/{$block}/{$type}.asset.php";
        
        if (!file_exists($asset_path)) {
            return false;
        }
        
        return include $asset_path;
    }
    
    /**
     * Clear cache
     */
    public function clear_cache() {
        $this->blocks_cache = [];
        $this->active_blocks_cache = [];
    }
}
