<?php
/**
 * ServiceContainer
 * Dependency Injection Container for BlockXpert
 * Manages service instantiation and dependency resolution
 */

if (!defined('ABSPATH')) exit;

class ServiceContainer {
    /**
     * @var array Registered services
     */
    private $services = [];
    
    /**
     * @var array Instantiated singletons
     */
    private $instances = [];
    
    /**
     * @var ServiceContainer Singleton instance
     */
    private static $instance = null;
    
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
     * Private constructor
     */
    private function __construct() {
        $this->register_defaults();
    }
    
    /**
     * Register default services
     */
    private function register_defaults() {
        // Cache service
        $this->singleton('cache', function() {
            return BlockXpert_Cache::get_instance();
        });
        
        // Logger service
        $this->singleton('logger', function() {
            return new BlockXpert_Logger();
        });
        
        // Service layer
        $this->singleton('service', function() {
            return BlockXpert_Service::get_instance();
        });
        
        // Block manager
        $this->singleton('blocks', function() {
            return BlockXpert_Blocks::get_instance();
        });
        
        // REST API
        $this->singleton('rest', function() {
            return BlockXpert_REST::get_instance();
        });
        
        // Admin settings
        $this->singleton('admin_settings', function() {
            return BlockXpert_Admin_Settings::get_instance();
        });
        
        // AI Provider (can be overridden)
        $this->singleton('ai_provider', function() {
            $provider_class = apply_filters('blockxpert_ai_provider_class', 'BlockXpert_OpenAI_Provider');
            if (class_exists($provider_class)) {
                return new $provider_class();
            }
            return new BlockXpert_OpenAI_Provider();
        });
    }
    
    /**
     * Register a service
     * @param string $name Service name
     * @param callable $definition Service definition
     */
    public function register($name, $definition) {
        $this->services[$name] = $definition;
        unset($this->instances[$name]); // Clear cached instance
    }
    
    /**
     * Register a singleton service
     * @param string $name Service name
     * @param callable|object $definition Service definition or instance
     */
    public function singleton($name, $definition) {
        if (is_callable($definition)) {
            $this->register($name, function() use ($definition) {
                if (!isset($this->instances[$name])) {
                    $this->instances[$name] = $definition($this);
                }
                return $this->instances[$name];
            });
        } else {
            // If it's already an object, store it directly
            $this->instances[$name] = $definition;
        }
    }
    
    /**
     * Get a service
     * @param string $name Service name
     * @return mixed Service instance
     * @throws Exception If service not found
     */
    public function get($name) {
        if (!isset($this->services[$name]) && !isset($this->instances[$name])) {
            throw new Exception("Service '{$name}' not found in container");
        }
        
        // Return cached instance if exists
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }
        
        // Call the service definition
        $definition = $this->services[$name];
        if (is_callable($definition)) {
            return $definition($this);
        }
        
        return $definition;
    }
    
    /**
     * Check if service exists
     * @param string $name Service name
     * @return bool
     */
    public function has($name) {
        return isset($this->services[$name]) || isset($this->instances[$name]);
    }
    
    /**
     * Remove a service
     * @param string $name Service name
     */
    public function remove($name) {
        unset($this->services[$name]);
        unset($this->instances[$name]);
    }
    
    /**
     * Get all registered services
     * @return array Service names
     */
    public function get_services() {
        return array_keys($this->services);
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialize
     */
    private function __wakeup() {}
}
