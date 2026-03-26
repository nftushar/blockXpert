<?php
/**
 * ServiceInterface
 * Base interface for service classes
 */

if (!defined('ABSPATH')) exit;

interface ServiceInterface {
    /**
     * Initialize the service
     */
    public function init();
    
    /**
     * Shutdown/cleanup the service
     */
    public function shutdown();
}
