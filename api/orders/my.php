<?php
/**
 * GET /api/orders/my - Get current user's orders
 */

require_once __DIR__ . '/../config.php';

function apiOrdersMy() {
    try {
        $user = auth();

        $db = getDB();
        $userId = $user['id'];
        $hasBillingAddress = dbHasColumn($db, 'orders', 'billing_address');
        $hasPaymentNotification = dbHasColumn($db, 'orders', 'payment_notification');

        $ordersStmt = $db->prepare("
            SELECT
                o.id,
                o.status,
                o.payment_status,
                o.payment_method,
                o.total,
                o.tracking_number,
                o.shipping_address,
                " . ($hasBillingAddress ? "o.billing_address" : "'' AS billing_address") . ",
                o.notes,
                " . ($hasPaymentNotification ? "o.payment_notification" : "NULL AS payment_notification") . ",
                o.created_at
            FROM orders o
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
        ");
        $ordersStmt->execute([$userId]);
        $orders = $ordersStmt->fetchAll();

        foreach ($orders as &$order) {
            $order['shipping_address'] = json_decode($order['shipping_address'], true) ?: [];
            $order['billing_address'] = json_decode($order['billing_address'], true) ?: [];
            $order['payment_notification'] = json_decode($order['payment_notification'], true);

            $itemsStmt = $db->prepare("
                SELECT
                    oi.id,
                    oi.product_id,
                    oi.quantity,
                    oi.price,
                    oi.variants,
                    p.name as product_name,
                    p.images
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            $itemsStmt->execute([$order['id']]);
            $items = $itemsStmt->fetchAll();

            foreach ($items as &$item) {
                $item['variants'] = json_decode($item['variants'], true);
                $item['images'] = json_decode($item['images'] ?? '[]', true) ?: [];
                if (!empty($item['images']) && is_array($item['images'])) {
                    $item['image'] = $item['images'][0] ?? null;
                } else {
                    $item['image'] = null;
                }
                unset($item['images']);
            }

            $order['items'] = $items;
        }

        sendJSON($orders);

    } catch (Exception $e) {
        logError("Orders My Error: " . $e->getMessage());
        sendJSON(['error' => 'Siparisleriniz yuklenirken hata olustu'], 500);
    }
}
