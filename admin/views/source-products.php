<?php
// Source products view
if (!defined('ABSPATH')) exit;
?>

<div class="wrap">
    <h1>Source Products</h1>

    <div class="abmd-source-products-container">
        <?php if (empty($products)): ?>
            <p>No source products found. <a href="<?php echo admin_url('admin.php?page=aussie-band-merch-dropship-import'); ?>">Import some products</a>.</p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Artist</th>
                        <th>Source Site</th>
                        <th>Source Price</th>
                        <th>Shipping</th>
                        <th>WC Product</th>
                        <th>Last Synced</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product->id; ?></td>
                            <td><?php echo esc_html($product->title); ?></td>
                            <td><?php echo esc_html($product->artist ?? 'N/A'); ?></td>
                            <td><?php echo esc_html($product->source_site); ?></td>
                            <td>$<?php echo number_format($product->price, 2); ?></td>
                            <td>
                                <?php if ($product->shipping_cost > 0): ?>
                                    $<?php echo number_format($product->shipping_cost, 2); ?>
                                <?php else: ?>
                                    Free
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($product->product_id): ?>
                                    <a href="<?php echo admin_url('post.php?post=' . $product->product_id . '&action=edit'); ?>">
                                        #<?php echo $product->product_id; ?>
                                    </a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($product->last_synced)); ?></td>
                            <td>
                                <a href="<?php echo esc_url($product->source_url); ?>" target="_blank" class="button button-small">
                                    View Source
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="abmd-products-summary">
                <h3>Summary</h3>
                <p>
                    <strong>Total Products:</strong> <?php echo count($products); ?><br>
                    <strong>Linked to WC:</strong> <?php echo count(array_filter($products, function($p) { return $p->product_id; })); ?><br>
                    <strong>Unlinked:</strong> <?php echo count(array_filter($products, function($p) { return !$p->product_id; })); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>
