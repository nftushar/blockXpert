<?php
/**
 * CacheInterface
 * Defines contract for caching operations
 */

if (!defined('ABSPATH')) exit;

interface CacheInterface {
    /**
     * Get cached value
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = false);
    
    /**
     * Set cached value
     * @param string $key
     * @param mixed $value
     * @param int $ttl Time to live in seconds
     * @return bool
     */
    public function set($key, $value, $ttl = 3600);
    
    /**
     * Delete cached value
     * @param string $key
     * @return bool
     */
    public function delete($key);
    
    /**
     * Check if key exists
     * @param string $key
     * @return bool
     */
    public function exists($key);
    
    /**
     * Flush all cache
     * @return bool
     */
    public function flush();
}
