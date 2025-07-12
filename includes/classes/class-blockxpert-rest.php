<?php

/**
 * Handles REST API endpoints for BlockXpert.
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class BlockXpert_REST
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    public function register_rest_routes()
    {
        register_rest_route('blockxpert/v1', '/generate-faq', [
            'methods' => 'POST',
            'callback' => [$this, 'generate_faq_questions'],
            'permission_callback' => function () {
                return current_user_can('edit_posts') && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'] ?? '')), 'wp_rest');
            },
        ]);
        register_rest_route('blockxpert/v1', '/generate-product-recommendations', [
            'methods' => 'POST',
            'callback' => [$this, 'generate_product_recommendations'],
            'permission_callback' => function () {
                return current_user_can('edit_posts') && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'] ?? '')), 'wp_rest');
            },
        ]);
    }

    public function generate_faq_questions($request)
    {
        // Verify nonce for additional security
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'] ?? '')), 'wp_rest')) {
            return new WP_Error('invalid_nonce', esc_html__('Security check failed.', 'BlockXpert'), ['status' => 403]);
        }
        
        // Verify user permissions
        if (!current_user_can('edit_posts')) {
            return new WP_Error('insufficient_permissions', esc_html__('You do not have permission to perform this action.', 'BlockXpert'), ['status' => 403]);
        }
        
        $params = $request->get_json_params();
        $apiKey = $params['apiKey'] ?? '';
        $model = $params['model'] ?? 'gpt-3.5-turbo';
        $customPrompt = $params['customPrompt'] ?? '';
        $questionsCount = $params['questionsCount'] ?? 5;
        if (empty($apiKey)) {
            return new WP_Error('missing_api_key', esc_html__('OpenAI API key is required.', 'BlockXpert'), ['status' => 400]);
        }
        $prompt = $customPrompt ?: esc_html__('Generate relevant FAQ questions and answers for a website', 'BlockXpert');
        $openai_url = 'https://api.openai.com/v1/chat/completions';
        $body = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => esc_html__('You are a helpful AI assistant that generates FAQ questions and answers.', 'BlockXpert')],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 1024,
            'n' => 1,
        ];
        $response = wp_remote_post($openai_url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ],
            'body' => wp_json_encode($body),
            'timeout' => 30,
        ]);
        if (is_wp_error($response)) {
            return new WP_Error('openai_error', esc_html__('Failed to connect to OpenAI API.', 'BlockXpert'), ['status' => 500]);
        }
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($data['choices'][0]['message']['content'])) {
            return new WP_Error('openai_invalid', esc_html__('Invalid response from OpenAI API.', 'BlockXpert'), ['status' => 500]);
        }
        $content = $data['choices'][0]['message']['content'];
        // Try to parse as JSON, fallback to text
        $questions = json_decode($content, true);
        if (!is_array($questions)) {
            // Fallback: try to parse Q/A pairs from text
            $lines = explode("\n", $content);
            $questions = [];
            foreach ($lines as $line) {
                if (preg_match('/^Q: (.+)/', $line, $qmatch)) {
                    $question = $qmatch[1];
                    $answer = '';
                    if (preg_match('/^A: (.+)/', next($lines), $amatch)) {
                        $answer = $amatch[1];
                    }
                    $questions[] = ['question' => $question, 'answer' => $answer];
                }
            }
        }
        return [
            'success' => true,
            'questions' => $questions,
        ];
    }

    public function generate_product_recommendations($request)
    {
        // Verify nonce for additional security
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'] ?? '')), 'wp_rest')) {
            return new WP_Error('invalid_nonce', esc_html__('Security check failed.', 'BlockXpert'), ['status' => 403]);
        }
        
        // Verify user permissions
        if (!current_user_can('edit_posts')) {
            return new WP_Error('insufficient_permissions', esc_html__('You do not have permission to perform this action.', 'BlockXpert'), ['status' => 403]);
        }
        
        $params = $request->get_json_params();
        $apiKey = $params['apiKey'] ?? '';
        $model = $params['model'] ?? 'gpt-3.5-turbo';
        $currentProduct = $params['currentProduct'] ?? null;
        $recommendationType = $params['recommendationType'] ?? 'related';
        $productsCount = $params['productsCount'] ?? 4;
        $customPrompt = $params['customPrompt'] ?? '';
        $priceRange = $params['priceRange'] ?? ['min' => 0, 'max' => 1000];
        $inStockOnly = $params['inStockOnly'] ?? true;
        if (empty($apiKey)) {
            return new WP_Error('missing_api_key', esc_html__('OpenAI API key is required.', 'BlockXpert'), ['status' => 400]);
        }
        if (!class_exists('WooCommerce')) {
            return new WP_Error('missing_woocommerce', esc_html__('WooCommerce is required for product recommendations.', 'BlockXpert'), ['status' => 400]);
        }
        // Fetch all products for context
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
            'fields' => 'ids',
        ];
        $query = new \WP_Query($args);
        $all_products = $query->posts;
        wp_reset_postdata();
        // Prepare OpenAI prompt
        $prompt = $customPrompt ?: esc_html__('You are a helpful AI assistant that recommends products. Always respond with valid JSON format containing only product IDs.', 'BlockXpert');
        $prompt .= "\nAvailable product IDs: [" . implode(", ", $all_products) . "]";
        if ($currentProduct && isset($currentProduct['id'])) {
            $prompt .= "\nCurrent product ID: " . $currentProduct['id'];
        }
        $prompt .= "\nPlease recommend exactly $productsCount product IDs from the available products that best match the criteria. Return only a JSON array of product IDs, like: [123, 456, 789]";
        $openai_url = 'https://api.openai.com/v1/chat/completions';
        $body = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $prompt],
            ],
            'max_tokens' => 256,
            'n' => 1,
        ];
        $response = wp_remote_post($openai_url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ],
            'body' => wp_json_encode($body),
            'timeout' => 30,
        ]);
        if (is_wp_error($response)) {
            return new WP_Error('openai_error', esc_html__('Failed to connect to OpenAI API.', 'BlockXpert'), ['status' => 500]);
        }
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($data['choices'][0]['message']['content'])) {
            return new WP_Error('openai_invalid', esc_html__('Invalid response from OpenAI API.', 'BlockXpert'), ['status' => 500]);
        }
        $content = $data['choices'][0]['message']['content'];
        $ids = json_decode($content, true);
        if (!is_array($ids)) {
            preg_match_all('/\d+/', $content, $matches);
            $ids = $matches[0] ?? [];
        }
        // Fetch product data
        $recommended = [];
        foreach ($ids as $id) {
            $product = wc_get_product($id);
            if ($product) {
                $recommended[] = [
                    'id' => $product->get_id(),
                    'name' => $product->get_name(),
                    'images' => [['src' => $product->get_image_id() ? wp_get_attachment_url($product->get_image_id()) : wc_placeholder_img_src()]],
                    'price_html' => $product->get_price_html(),
                    'price' => $product->get_price(),
                    'average_rating' => $product->get_average_rating(),
                    'review_count' => $product->get_review_count(),
                    'stock_status' => $product->get_stock_status(),
                ];
            }
        }
        return [
            'success' => true,
            'products' => $recommended,
        ];
    }

    // Add other REST endpoint methods as needed
}
