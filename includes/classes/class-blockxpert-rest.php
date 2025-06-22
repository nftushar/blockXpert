<?php
/**
 * Handles REST API endpoints for BlockXpert.
 */
class BlockXpert_REST {
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    public function register_rest_routes() {
        error_log('BlockXpert: register_rest_routes called');
        register_rest_route('blockxpert/v1', '/generate-faq', [
            'methods' => 'POST',
            'callback' => [$this, 'generate_faq_questions'],
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);
        register_rest_route('blockxpert/v1', '/generate-product-recommendations', [
            'methods' => 'POST',
            'callback' => [$this, 'generate_product_recommendations'],
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ]);
        register_rest_route('blockxpert/v1', '/pdf-invoice', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_pdf_invoice_download'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function generate_faq_questions($request) {
        // (Paste the full method from init.php here)
    }

    public function generate_product_recommendations($request) {
        // (Paste the full method from init.php here)
    }

    public function rest_pdf_invoice_download($request) {
        // (Paste the full method from init.php here)
    }

    // Add other REST endpoint methods as needed
} 