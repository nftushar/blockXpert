<?php
use Dompdf\Dompdf;
use Dompdf\Options;

if (!function_exists('blockxpert_generate_invoice_pdf')) {
    function blockxpert_generate_invoice_pdf($order_id, $company = []) {
        if (!class_exists('Dompdf\\Dompdf')) {
            return new WP_Error('dompdf_missing', 'Dompdf library is not loaded.');
        }
        $order = wc_get_order($order_id);
        if (!$order) {
            return new WP_Error('invalid_order', 'Order not found.');
        }
        ob_start();
        $order = $order;
        $company = $company;
        include __DIR__ . '/template-basic.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->output();
    }
} 