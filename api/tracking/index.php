<?php
function apiTracking() {
    try {
        $db = getDB();
        $orderNumber = getParam('order_number');
        $email = getParam('email');

        if ($orderNumber) {
            $stmt = $db->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
            $stmt->execute([$orderNumber]);
        } elseif ($email) {
            $stmt = $db->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE u.email = ? ORDER BY o.created_at DESC LIMIT 1");
            $stmt->execute([$email]);
        } else {
            sendJSON(['error' => 'Sipariş numarası veya e-posta gerekli'], 400);
        }

        $orders = $stmt->fetchAll();
        if (empty($orders)) sendJSON(['error' => 'Sipariş bulunamadı'], 404);

        $order = $orders[0];
        $itemsStmt = $db->prepare("SELECT oi.*, p.name, p.images FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $itemsStmt->execute([$order['id']]);
        $order['items'] = $itemsStmt->fetchAll();
        foreach ($order['items'] as &$item) {
            $item['images'] = json_decode($item['images'] ?? '[]', true) ?: [];
        }
        if (array_key_exists('payment_notification', $order) && $order['payment_notification']) {
            $order['payment_notification'] = json_decode($order['payment_notification'], true);
        }
        sendJSON($order);
    } catch (Exception $e) {
        logError("Tracking: " . $e->getMessage());
        sendJSON(['error' => 'Sipariş bulunamadı'], 500);
    }
}
