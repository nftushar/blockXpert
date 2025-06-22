<?php
// Template for PDF Invoice
$order = isset($order) ? $order : null;
$company = isset($company) ? $company : [];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice</title>
    <style><?php include __DIR__ . '/style-invoice.css'; ?></style>
</head>
<body>
    <div class="invoice-container">
        <header>
            <div class="company-logo">
                <?php if (!empty($company['logo'])): ?>
                    <img src="<?php echo $company['logo']; ?>" alt="Company Logo" height="60" />
                <?php endif; ?>
            </div>
            <div class="company-info">
                <h2><?php echo esc_html($company['name'] ?? 'Your Company'); ?></h2>
                <p><?php echo esc_html($company['address'] ?? 'Company Address'); ?></p>
                <p><?php echo esc_html($company['email'] ?? 'Email'); ?></p>
            </div>
        </header>
        <section class="invoice-details">
            <h1>Invoice</h1>
            <p><strong>Order #:</strong> <?php echo $order ? $order->get_id() : ''; ?></p>
            <p><strong>Date:</strong> <?php echo $order ? $order->get_date_created()->date('Y-m-d') : ''; ?></p>
        </section>
        <section class="billing-shipping">
            <div>
                <h3>Billing Address</h3>
                <p><?php echo $order ? $order->get_formatted_billing_address() : ''; ?></p>
            </div>
            <div>
                <h3>Shipping Address</h3>
                <p><?php echo $order ? $order->get_formatted_shipping_address() : ''; ?></p>
            </div>
        </section>
        <section class="order-items">
            <table width="100%" cellspacing="0" cellpadding="5">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($order): foreach ($order->get_items() as $item): ?>
                    <tr>
                        <td><?php echo esc_html($item->get_name()); ?></td>
                        <td><?php echo esc_html($item->get_quantity()); ?></td>
                        <td><?php echo wc_price($item->get_total() / $item->get_quantity(), ['currency' => $order->get_currency()]); ?></td>
                        <td><?php echo wc_price($item->get_total(), ['currency' => $order->get_currency()]); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </section>
        <section class="order-summary">
            <p><strong>Subtotal:</strong> <?php echo $order ? wc_price($order->get_subtotal(), ['currency' => $order->get_currency()]) : ''; ?></p>
            <p><strong>Total:</strong> <?php echo $order ? wc_price($order->get_total(), ['currency' => $order->get_currency()]) : ''; ?></p>
        </section>
        <footer>
            <p><?php echo esc_html($company['footer'] ?? 'Thank you for your business!'); ?></p>
        </footer>
    </div>
</body>
</html> 