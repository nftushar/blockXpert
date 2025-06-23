<?php
// Template content for PDF Invoice
if (!defined('ABSPATH')) exit;

// Convert relative logo URL to absolute URL
$logo_url = !empty($company['logo']) ? (strpos($company['logo'], 'http') === 0 ? $company['logo'] : site_url($company['logo'])) : '';
?>
<div class="invoice-container">
    <header>
        <table width="100%" style="margin-bottom:0;">
            <tr>
                <td style="width:50%;vertical-align:top;">
                    <?php if (!empty($logo_url)): ?>
                        <img src="<?php echo esc_url($logo_url); ?>" alt="Company Logo" height="60" />
                    <?php endif; ?>
                </td>
                <td style="width:50%;text-align:right;vertical-align:top;">
                    <h2><?php echo esc_html($company['name'] ?? 'Your Company'); ?></h2>
                    <p><?php echo esc_html($company['address'] ?? 'Company Address'); ?></p>
                    <p><?php echo esc_html($company['email'] ?? 'Email'); ?></p>
                </td>
            </tr>
        </table>
    </header>
    <section class="invoice-details">
        <h1>Invoice</h1>
        <p><strong>Order #:</strong> <?php echo $order ? esc_html($order->get_id()) : ''; ?></p>
        <p><strong>Date:</strong> <?php echo $order ? esc_html($order->get_date_created()->date('Y-m-d')) : ''; ?></p>
    </section>
    <section class="billing-shipping">
        <div>
            <h3>Billing Address</h3>
            <p><?php echo $order ? wp_kses_post($order->get_formatted_billing_address()) : ''; ?></p>
        </div>
        <div>
            <h3>Shipping Address</h3>
            <p><?php echo $order ? wp_kses_post($order->get_formatted_shipping_address()) : ''; ?></p>
        </div>
    </section>
    <section class="order-items">
        <table>
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
        <p><strong>Total:</strong> <span><strong><?php echo $order ? wc_price($order->get_total(), ['currency' => $order->get_currency()]) : ''; ?></strong></span></p>
    </section>
    <footer>
        <p><?php echo esc_html($company['footer'] ?? 'Thank you for your business!'); ?></p>
    </footer>
</div> 