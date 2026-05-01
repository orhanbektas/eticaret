<?php
/**
 * GET /api/orders/{id} - Get single order details
 * Users can only view their own orders, admins can view all
 */

function apiOrdersSingle($id) {
    try {
        $user = auth();

        $db = getDB();
        $orderId = (int)$id;
        $hasBillingAddress = dbHasColumn($db, 'orders', 'billing_address');
        $hasPaymentNotification = dbHasColumn($db, 'orders', 'payment_notification');

        $orderStmt = $db->prepare("
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
            WHERE o.id = ?
        ");
        $orderStmt->execute([$orderId]);
        $order = $orderStmt->fetch();

        if (!$order) {
            sendJSON(['error' => 'Siparis bulunamadi'], 404);
        }

        if ($user['role'] !== 'admin' && $order['user_id'] !== $user['id']) {
            sendJSON(['error' => 'Bu siparisi goruntuleme yetkiniz yok'], 403);
        }

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
        $itemsStmt->execute([$orderId]);
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

        sendJSON($order);

    } catch (Exception $e) {
        logError("Orders Single Error: " . $e->getMessage());
        sendJSON(['error' => 'Siparis detaylari yuklenirken hata olustu'], 500);
    }
}
