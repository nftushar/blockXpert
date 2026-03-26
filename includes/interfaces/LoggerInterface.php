<?php
/**
 * LoggerInterface
 * Defines contract for logging operations
 */

if (!defined('ABSPATH')) exit;

interface LoggerInterface {
    /**
     * Log a message
     * @param string $level Log level (info, warning, error, debug)
     * @param string $message
     * @param array $context Additional context data
     */
    public function log($level, $message, $context = []);
    
    /**
     * Log info
     * @param string $message
     * @param array $context
     */
    public function info($message, $context = []);
    
    /**
     * Log warning
     * @param string $message
     * @param array $context
     */
    public function warning($message, $context = []);
    
    /**
     * Log error
     * @param string $message
     * @param array $context
     */
    public function error($message, $context = []);
    
    /**
     * Log debug
     * @param string $message
     * @param array $context
     */
    public function debug($message, $context = []);
}
