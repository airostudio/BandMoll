<?php
// Dropship orders view
if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1>Dropship Orders</h1>

    <div class="abmd-orders-container">
        <?php if (empty($orders)): ?>
            <p>No dropship orders found.</p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>WC Order</th>
                        <th>Source Site</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Order Total</th>
                        <th>Source Cost</th>
                        <th>Profit</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo $order->id; ?></td>
                            <td>
                                <a href="<?php echo admin_url('post.php?post=' . $order->wc_order_id . '&action=edit'); ?>">
                                    #<?php echo $order->wc_order_id; ?>
                                </a>
                            </td>
                            <td><?php echo esc_html($order->source_site); ?></td>
                            <td><?php echo esc_html($order->customer_name); ?></td>
                            <td>
                                <span class="abmd-status abmd-status-<?php echo esc_attr($order->status); ?>">
                                    <?php echo esc_html(ucfirst($order->status)); ?>
                                </span>
                            </td>
                            <td>$<?php echo number_format($order->order_total, 2); ?></td>
                            <td>$<?php echo number_format($order->source_total, 2); ?></td>
                            <td class="abmd-profit">$<?php echo number_format($order->profit, 2); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($order->created_at)); ?></td>
                            <td>
                                <?php if ($order->status === 'pending'): ?>
                                    <button class="button button-small abmd-view-order"
                                            data-order-id="<?php echo $order->id; ?>"
                                            data-source-id="<?php echo $order->source_product_id; ?>">
                                        View Details
                                    </button>
                                <?php elseif ($order->status === 'failed'): ?>
                                    <button class="button button-small abmd-view-error"
                                            data-error="<?php echo esc_attr($order->error_message); ?>">
                                        View Error
                                    </button>
                                <?php else: ?>
                                    <?php if ($order->source_order_id): ?>
                                        <span class="dashicons dashicons-yes-alt"></span>
                                        <?php echo esc_html($order->source_order_id); ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="abmd-orders-summary">
                <h3>Summary</h3>
                <p>
                    <strong>Total Orders:</strong> <?php echo count($orders); ?><br>
                    <strong>Pending:</strong> <?php echo count(array_filter($orders, function($o) { return $o->status === 'pending'; })); ?><br>
                    <strong>Placed:</strong> <?php echo count(array_filter($orders, function($o) { return $o->status === 'placed'; })); ?><br>
                    <strong>Failed:</strong> <?php echo count(array_filter($orders, function($o) { return $o->status === 'failed'; })); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="abmd-order-modal" style="display: none;">
    <div class="abmd-modal-content">
        <span class="abmd-modal-close">&times;</span>
        <h2>Dropship Order Details</h2>
        <div id="abmd-order-details"></div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.abmd-view-order').on('click', function() {
        var orderId = $(this).data('order-id');
        var sourceId = $(this).data('source-id');

        // Load order details via AJAX
        alert('Order ID: ' + orderId + '\nSource Product ID: ' + sourceId + '\n\nManually place this order with the source site and mark it as complete.');
    });

    $('.abmd-view-error').on('click', function() {
        var error = $(this).data('error');
        alert('Error:\n' + error);
    });
});
</script>
