<?php
/**
 * BlockXpert_Logger
 * Logging service for BlockXpert
 * Supports multiple log levels and context
 */

if (!defined('ABSPATH')) exit;

class BlockXpert_Logger implements LoggerInterface {
    const LOG_DIR = BLOCKXPERT_PATH . 'logs';
    const LOG_FILE = self::LOG_DIR . '/blockxpert.log';
    
    /**
     * Initialize logger
     */
    public function __construct() {
        $this->ensure_log_dir();
    }
    
    /**
     * Ensure log directory exists
     */
    private function ensure_log_dir() {
        if (!is_dir(self::LOG_DIR)) {
            wp_mkdir_p(self::LOG_DIR);
        }
    }
    
    /**
     * Log a message
     * @param string $level Log level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, $context = []) {
        $timestamp = current_time('mysql');
        $level = strtoupper($level);
        
        // Prepare context string
        $context_str = !empty($context) ? ' | ' . json_encode($context) : '';
        
        // Format: [TIMESTAMP] [LEVEL] Message | Context
        $log_line = "[{$timestamp}] [{$level}] {$message}{$context_str}" . PHP_EOL;
        
        // Also add to WordPress error_log if WP_DEBUG is enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log($log_line);
        }
        
        // Write to file
        if (is_writable(self::LOG_DIR)) {
            file_put_contents(
                self::LOG_FILE,
                $log_line,
                FILE_APPEND | LOCK_EX
            );
        }
        
        // Allow extensions to hook into logging
        do_action('blockxpert_log', $level, $message, $context);
    }
    
    /**
     * Log info level
     */
    public function info($message, $context = []) {
        $this->log('info', $message, $context);
    }
    
    /**
     * Log warning level
     */
    public function warning($message, $context = []) {
        $this->log('warning', $message, $context);
    }
    
    /**
     * Log error level
     */
    public function error($message, $context = []) {
        $this->log('error', $message, $context);
    }
    
    /**
     * Log debug level
     */
    public function debug($message, $context = []) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->log('debug', $message, $context);
        }
    }
    
    /**
     * Get log lines
     * @param int $limit Number of lines to retrieve
     * @param int $offset Offset
     * @return array Log lines
     */
    public static function get_logs($limit = 100, $offset = 0) {
        if (!file_exists(self::LOG_FILE)) {
            return [];
        }
        
        $lines = file(self::LOG_FILE, FILE_SKIP_EMPTY_LINES);
        if (!$lines) return [];
        
        // Reverse to get latest first
        $lines = array_reverse($lines);
        
        return array_slice($lines, $offset, $limit);
    }
    
    /**
     * Clear logs
     * @return bool
     */
    public static function clear() {
        if (file_exists(self::LOG_FILE)) {
            return unlink(self::LOG_FILE);
        }
        return true;
    }
    
    /**
     * Get log file size
     * @return int Size in bytes
     */
    public static function get_log_size() {
        if (file_exists(self::LOG_FILE)) {
            return filesize(self::LOG_FILE);
        }
        return 0;
    }
    
    /**
     * Check if logs are too large and rotate if needed
     */
    public static function rotate_if_needed($max_size = 5242880) { // 5MB default
        if (file_exists(self::LOG_FILE) && filesize(self::LOG_FILE) > $max_size) {
            $backup = self::LOG_FILE . '.' . date('Y-m-d_H-i-s');
            rename(self::LOG_FILE, $backup);
        }
    }
}
