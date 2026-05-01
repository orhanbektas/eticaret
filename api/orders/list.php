<?php
/**
 * GET /api/orders - List all orders (Admin only)
 * Returns all orders with customer info and order items
 */

require_once __DIR__ . '/../config.php';

function apiOrdersList() {
    try {
        adminAuth();

        $db = getDB();

        $orderColumnsStmt = $db->query("SHOW COLUMNS FROM orders");
        $orderColumns = array_map(static fn($row) => $row['Field'], $orderColumnsStmt->fetchAll());
        $hasBillingAddress = in_array('billing_address', $orderColumns, true);
        $hasPaymentNotification = in_array('payment_notification', $orderColumns, true);

        $ordersStmt = $db->prepare("
            SELECT
                o.id,
                o.user_id,
                o.status,
                o.payment_status,
                o.payment_method,
                o.total,
                o.tracking_number,
                o.shipping_address,
                " . ($hasBillingAddress ? "o.billing_address" : "'' AS billing_address") . ",
                o.notes,
                " . ($hasPaymentNotification ? "o.payment_notification" : "NULL AS payment_notification") . ",
                o.created_at,
                u.name as customer_name,
                u.email as customer_email
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
        ");
        $ordersStmt->execute();
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
        logError("Orders List Error: " . $e->getMessage());
        sendJSON(['error' => 'Siparisler yuklenirken hata olustu'], 500);
    }
}
