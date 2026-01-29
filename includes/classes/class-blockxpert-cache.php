<?php
/**
 * BlockXpert Cache Layer
 * Handles transients and caching for performance
 */

if (!defined('ABSPATH')) exit;

class BlockXpert_Cache {
    const CACHE_PREFIX = 'blockxpert_';
    const DEFAULT_TTL = 3600; // 1 hour
    
    /**
     * Get cached value
     * @param string $key Cache key
     * @param mixed $default Default value if not found
     * @param string $group Optional cache group
     * @return mixed Cached value or default
     */
    public static function get($key, $default = false, $group = '') {
        $key = self::sanitize_key($key, $group);
        $value = get_transient($key);
        
        return $value !== false ? $value : $default;
    }
    
    /**
     * Set cached value
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds
     * @param string $group Optional cache group
     * @return bool Success status
     */
    public static function set($key, $value, $ttl = self::DEFAULT_TTL, $group = '') {
        $key = self::sanitize_key($key, $group);
        
        // Ensure TTL is reasonable
        $ttl = max(1, min((int) $ttl, 7 * DAY_IN_SECONDS));
        
        return set_transient($key, $value, $ttl);
    }
    
    /**
     * Delete cached value
     * @param string $key Cache key
     * @param string $group Optional cache group
     * @return bool Success status
     */
    public static function delete($key, $group = '') {
        $key = self::sanitize_key($key, $group);
        return delete_transient($key);
    }
    
    /**
     * Check if key exists in cache
     * @param string $key Cache key
     * @param string $group Optional cache group
     * @return bool True if exists
     */
    public static function exists($key, $group = '') {
        $key = self::sanitize_key($key, $group);
        return get_transient($key) !== false;
    }
    
    /**
     * Flush all BlockXpert cache
     */
    public static function flush_all() {
        global $wpdb;
        
        // Delete transients
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} 
                WHERE option_name LIKE %s OR option_name LIKE %s",
                '_transient_' . self::CACHE_PREFIX . '%',
                '_transient_timeout_' . self::CACHE_PREFIX . '%'
            )
        );
    }
    
    /**
     * Sanitize cache key
     * @param string $key Cache key
     * @param string $group Optional group prefix
     * @return string Sanitized key
     */
    private static function sanitize_key($key, $group = '') {
        $prefix = self::CACHE_PREFIX;
        
        if (!empty($group)) {
            $prefix .= sanitize_key($group) . '_';
        }
        
        return $prefix . sanitize_key($key);
    }
    
    /**
     * Remember pattern: get or set
     * @param string $key Cache key
     * @param callable $callback Function to call if not cached
     * @param int $ttl Time to live
     * @param string $group Optional group
     * @return mixed Cached or fresh value
     */
    public static function remember($key, $callback, $ttl = self::DEFAULT_TTL, $group = '') {
        $cached = self::get($key, null, $group);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $value = call_user_func($callback);
        self::set($key, $value, $ttl, $group);
        
        return $value;
    }
}
