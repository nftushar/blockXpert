<?php
use Dompdf\Dompdf;
use Dompdf\Options;

// Ensure this file is being included by a parent file
if (!defined('ABSPATH')) {
    die('Direct script access denied.');
}

if (!function_exists('blockxpert_generate_invoice_pdf')) {
    /**
     * Generate a PDF invoice for a WooCommerce order
     * 
     * @param int $order_id The WooCommerce order ID
     * @param array $company Company information for the invoice
     * @return string|WP_Error PDF content string or WP_Error on failure
     */
    function blockxpert_generate_invoice_pdf($order_id, $company = []) {
        error_log('BlockXpert PDF: Starting generation for order ' . $order_id);

        // Check for required dependencies
        if (!class_exists('WooCommerce')) {
            error_log('BlockXpert PDF: WooCommerce not active');
            return new WP_Error('wc_missing', 'WooCommerce is required.');
        }

        if (!class_exists('Dompdf\\Dompdf')) {
            error_log('BlockXpert PDF: Dompdf class not found');
            return new WP_Error('dompdf_missing', 'Dompdf library is not loaded.');
        }

        // Get the order
        $order = wc_get_order($order_id);
        if (!$order) {
            error_log('BlockXpert PDF: Order not found - ' . $order_id);
            return new WP_Error('invalid_order', 'Order not found.');
        }

        // Get the CSS content
        $css_file = __DIR__ . '/style-invoice.css';
        if (!file_exists($css_file)) {
            error_log('BlockXpert PDF: CSS file not found - ' . $css_file);
            return new WP_Error('css_missing', 'Invoice styles not found.');
        }
        $css_content = file_get_contents($css_file);
        
        error_log('BlockXpert PDF: Starting HTML generation');
        ob_start();
        try {
            // Include template but with inline styles
            echo '<!DOCTYPE html>';
            echo '<html>';
            echo '<head>';
            echo '<meta charset="utf-8">';
            echo '<title>Invoice</title>';
            echo '<style>';
            echo ':root {';
            echo '--invoice-font-size: ' . esc_attr($company['font_size'] ?? '16px') . ';';
            echo '--invoice-primary-color: ' . esc_attr($company['primary_color'] ?? '#007cba') . ';';
            echo '}';
            echo $css_content;
            echo '</style>';
            echo '</head>';
            echo '<body>';
            
            $template_file = __DIR__ . '/template-basic-content.php';
            if (!file_exists($template_file)) {
                throw new Exception('Template file not found: ' . $template_file);
            }
            
            error_log('BlockXpert PDF: Including template file');
            include $template_file;
            
            echo '</body>';
            echo '</html>';
        } catch (Exception $e) {
            error_log('BlockXpert PDF: Template error - ' . $e->getMessage());
            return new WP_Error('template_error', 'Failed to generate invoice template: ' . $e->getMessage());
        }
        
        $html = ob_get_clean();
        if (empty($html)) {
            error_log('BlockXpert PDF: Empty HTML generated');
            return new WP_Error('empty_html', 'Failed to generate invoice HTML.');
        }
        error_log('BlockXpert PDF: HTML generation complete');

        try {
            error_log('BlockXpert PDF: Configuring DomPDF');
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('enable_css_float', true);
            $options->set('enable_html5_parser', true);
            $options->set('debugKeepTemp', true);
            $options->set('debugPng', true);
            $options->set('debugCss', true);
            $options->set('chroot', __DIR__);
            
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            
            error_log('BlockXpert PDF: Starting PDF render');
            $dompdf->render();
            error_log('BlockXpert PDF: PDF render complete');
            
            $output = $dompdf->output();
            if (empty($output)) {
                error_log('BlockXpert PDF: Empty PDF output');
                return new WP_Error('empty_pdf', 'Failed to generate PDF output.');
            }
            
            error_log('BlockXpert PDF: Generation complete, output size: ' . strlen($output));
            return $output;
        } catch (Exception $e) {
            error_log('BlockXpert PDF: PDF generation error - ' . $e->getMessage());
            return new WP_Error('pdf_error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }
}

// Attach PDF invoice to WooCommerce emails
add_filter('woocommerce_email_attachments', function($attachments, $email_id, $order, $email = null) {
    $attach_to = [
        'customer_completed_order',
        'customer_processing_order',
    ];
    if (in_array($email_id, $attach_to) && $order instanceof WC_Order) {
        $pdf_path = blockxpert_generate_pdf_invoice($order->get_id());
        if ($pdf_path && file_exists($pdf_path)) {
            $attachments[] = $pdf_path;
        }
    }
    return $attachments;
}, 10, 4);

// Generate PDF invoice and return file path
function blockxpert_generate_pdf_invoice($order_id) {
    $upload_dir = wp_upload_dir();
    $pdf_dir = $upload_dir['basedir'] . '/blockxpert-invoices/';
    if (!file_exists($pdf_dir)) {
        wp_mkdir_p($pdf_dir);
    }
    $pdf_path = $pdf_dir . 'invoice-' . $order_id . '.pdf';

    if (!file_exists($pdf_path)) {
        $order = wc_get_order($order_id);
        $html = '<h1>Invoice for Order #' . $order_id . '</h1>';
        // ...build your invoice HTML here...

        // Use Dompdf to generate PDF
        require_once __DIR__ . '/../../vendor/autoload.php';
        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        file_put_contents($pdf_path, $dompdf->output());
    }
    return $pdf_path;
}

// 2. Add a download invoice button to the order admin actions
add_action('woocommerce_admin_order_actions_end', function($order) {
    $url = wp_nonce_url(
        admin_url('admin-ajax.php?action=blockxpert_download_invoice&order_id=' . $order->get_id()),
        'blockxpert_download_invoice_' . $order->get_id()
    );
    echo '<a href="' . esc_url($url) . '" class="button tips" target="_blank" alt="Download Invoice" style="margin-left:5px;">PDF Invoice</a>';
});

// Handle the AJAX download request
add_action('wp_ajax_blockxpert_download_invoice', function() {
    if (empty($_GET['order_id']) || !current_user_can('manage_woocommerce')) {
        wp_die('Unauthorized', 'Error', ['response' => 403]);
    }
    $order_id = intval($_GET['order_id']);
    if (!wp_verify_nonce($_GET['_wpnonce'], 'blockxpert_download_invoice_' . $order_id)) {
        wp_die('Invalid nonce', 'Error', ['response' => 403]);
    }
    $pdf_path = blockxpert_generate_pdf_invoice($order_id);
    if ($pdf_path && file_exists($pdf_path)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="invoice-' . $order_id . '.pdf"');
        readfile($pdf_path);
        exit;
    }
    wp_die('Invoice not found', 'Error', ['response' => 404]);
});

// 3. Add bulk action to generate invoices
add_filter('bulk_actions-edit-shop_order', function($actions) {
    $actions['blockxpert_bulk_invoice'] = __('Generate PDF Invoices', 'blockxpert');
    return $actions;
});

add_filter('handle_bulk_actions-edit-shop_order', function($redirect, $action, $post_ids) {
    if ($action === 'blockxpert_bulk_invoice') {
        foreach ($post_ids as $order_id) {
            blockxpert_generate_pdf_invoice($order_id);
        }
        $redirect = add_query_arg('blockxpert_bulk_invoice', count($post_ids), $redirect);
    }
    return $redirect;
}, 10, 3);

add_action('admin_notices', function() {
    if (!empty($_REQUEST['blockxpert_bulk_invoice'])) {
        $count = intval($_REQUEST['blockxpert_bulk_invoice']);
        echo '<div class="notice notice-success is-dismissible"><p>' .
            sprintf(_n('%d invoice generated.', '%d invoices generated.', $count, 'blockxpert'), $count) .
            '</p></div>';
    }
}); 