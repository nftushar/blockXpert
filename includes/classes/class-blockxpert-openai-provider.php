<?php


if (!defined('ABSPATH')) exit;

class BlockXpert_OpenAI_Provider implements AIProviderInterface {
    const API_URL = 'https://api.openai.com/v1/chat/completions';
    const DEFAULT_MODEL = 'gpt-3.5-turbo';
    
    /**
     * @var array Available models
     */
    private $available_models = [
        'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
        'gpt-4' => 'GPT-4',
        'gpt-4-turbo' => 'GPT-4 Turbo',
    ];
    
    /**
     * @var string OpenAI API key
     */
    private $api_key = '';
    
    /**
     * @var BlockXpert_Logger
     */
    private $logger;
    
    public function __construct() {
        $this->logger = ServiceContainer::get_instance()->get('logger');
        $this->load_config();
    }
    
    /**
     * Load configuration
     */
    private function load_config() {
        $this->api_key = get_option('blockxpert_openai_api_key', '');
    }
    
    /**
     * Check if provider is configured
     */
    public function is_configured() {
        return !empty($this->api_key);
    }
    
    /**
     * Generate text completion
     */
    public function generate($prompt, $options = []) {
        if (!$this->is_configured()) {
            $this->logger->warning('OpenAI provider not configured');
            return new WP_Error(
                'ai_provider_error',
                __('AI provider is not configured. Please add your API key.', 'blockxpert')
            );
        }
        
        try {
            $model = $options['model'] ?? self::DEFAULT_MODEL;
            $max_tokens = $options['max_tokens'] ?? 1024;
            $temperature = $options['temperature'] ?? 0.7;
            
            $body = [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful AI assistant.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => (int) $max_tokens,
                'temperature' => (float) $temperature,
            ];
            
            $response = $this->make_request($body);
            
            if (is_wp_error($response)) {
                return $response;
            }
            
            return [
                'success' => true,
                'content' => $response['choices'][0]['message']['content'] ?? '',
                'usage' => $response['usage'] ?? [],
                'model' => $model,
            ];
            
        } catch (Exception $e) {
            $this->logger->error('Generation error', ['exception' => $e->getMessage()]);
            return new WP_Error('generation_error', $e->getMessage());
        }
    }
    
    /**
     * Generate FAQ content
     */
    public function generate_faq($topic, $count = 5, $options = []) {
        $custom_prompt = $options['prompt'] ?? '';
        
        $prompt = $custom_prompt ?: sprintf(
            __('Generate %d frequently asked questions and answers about: %s. Format as JSON array with "question" and "answer" keys.', 'blockxpert'),
            $count,
            $topic
        );
        
        $result = $this->generate($prompt, array_merge($options, ['max_tokens' => 2048]));
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Try to parse as JSON
        $content = $result['content'];
        $questions = json_decode($content, true);
        
        if (!is_array($questions)) {
            // Fallback: parse Q/A format
            $questions = $this->parse_qa_format($content);
        }
        
        $result['questions'] = $questions;
        return $result;
    }
    
    /**
     * Parse Q/A format from text
     */
    private function parse_qa_format($content) {
        $questions = [];
        $lines = explode("\n", trim($content));
        $current_question = '';
        $current_answer = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            if (preg_match('/^Q:|^Question:/i', $line)) {
                if (!empty($current_question)) {
                    $questions[] = [
                        'question' => $current_question,
                        'answer' => $current_answer,
                    ];
                }
                $current_question = preg_replace('/^Q:|^Question:\s*/i', '', $line);
                $current_answer = '';
            } elseif (preg_match('/^A:|^Answer:/i', $line)) {
                $current_answer = preg_replace('/^A:|^Answer:\s*/i', '', $line);
            }
        }
        
        // Add last question
        if (!empty($current_question)) {
            $questions[] = [
                'question' => $current_question,
                'answer' => $current_answer,
            ];
        }
        
        return $questions;
    }
    
    /**
     * Get provider name
     */
    public function get_provider_name() {
        return 'OpenAI';
    }
    
    /**
     * Get available models
     */
    public function get_available_models() {
        return $this->available_models;
    }
    
    /**
     * Make API request
     * @param array $body Request body
     * @return array|WP_Error
     */
    private function make_request($body) {
        $response = wp_remote_post(self::API_URL, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key,
            ],
            'body' => wp_json_encode($body),
            'timeout' => 30,
        ]);
        
        if (is_wp_error($response)) {
            $this->logger->error('API request failed', [
                'error' => $response->get_error_message(),
            ]);
            return new WP_Error(
                'api_request_failed',
                __('Failed to connect to OpenAI API.', 'blockxpert')
            );
        }
        
        $status = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($status !== 200) {
            $error_message = $body['error']['message'] ?? __('Unknown API error', 'blockxpert');
            $this->logger->error('API error', [
                'status' => $status,
                'error' => $error_message,
            ]);
            return new WP_Error('api_error', $error_message);
        }
        
        return $body;
    }
    
    /**
     * Set API key
     * @param string $key
     */
    public function set_api_key($key) {
        $this->api_key = $key;
        update_option('blockxpert_openai_api_key', sanitize_text_field($key));
    }
    
    /**
     * Get API key (masked for display)
     * @return string
     */
    public function get_api_key_masked() {
        if (empty($this->api_key)) {
            return '';
        }
        $len = strlen($this->api_key);
        return str_repeat('*', max(1, $len - 8)) . substr($this->api_key, -8);
    }
}
