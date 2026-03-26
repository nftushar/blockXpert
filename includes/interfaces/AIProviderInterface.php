<?php
/**
 * AIProviderInterface
 * Defines contract for AI generation services
 * Allows pluggable AI providers (OpenAI, Claude, etc.)
 */

if (!defined('ABSPATH')) exit;

interface AIProviderInterface {
    /**
     * Check if provider is configured
     * @return bool
     */
    public function is_configured();
    
    /**
     * Generate text completion
     * @param string $prompt
     * @param array $options Additional options (model, max_tokens, etc.)
     * @return array|WP_Error Response data or error
     */
    public function generate($prompt, $options = []);
    
    /**
     * Generate FAQ content
     * @param string $topic
     * @param int $count Number of Q&A pairs
     * @param array $options Additional options
     * @return array|WP_Error FAQ data or error
     */
    public function generate_faq($topic, $count = 5, $options = []);
    
    /**
     * Get provider name
     * @return string
     */
    public function get_provider_name();
    
    /**
     * Get available models
     * @return array List of available models
     */
    public function get_available_models();
}
