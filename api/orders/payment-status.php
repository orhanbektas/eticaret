<?php
/**
 * PUT /api/orders/{id}/payment-status - Update payment status (Admin only)
 * Updates payment_status and adjusts order status accordingly
 */

require_once __DIR__ . '/../config.php';

function apiPaymentStatus($id) {
    try {
        // Admin authentication required
        adminAuth();

        $body = getBody();
        $db = getDB();
        $orderId = (int)$id;

        // Valid payment statuses
        $validStatuses = ['pending', 'paid', 'notified', 'failed', 'refunded'];

        $paymentStatus = trim($body['payment_status'] ?? '');

        if (empty($paymentStatus)) {
            sendJSON(['error' => 'Odeme durumu bilgisi gereklidir'], 400);
        }

        if (!in_array($paymentStatus, $validStatuses)) {
            sendJSON(['error' => 'Gecersiz odeme durumu'], 400);
        }

        // Check if order exists
        $orderStmt = $db->prepare("SELECT id, status, payment_status FROM orders WHERE id = ?");
        $orderStmt->execute([$orderId]);
        $order = $orderStmt->fetch();

        if (!$order) {
            sendJSON(['error' => 'Siparis bulunamadi'], 404);
        }

        $oldPaymentStatus = $order['payment_status'];

        // Determine order status based on payment status
        $newOrderStatus = $order['status'];

        // Update order status based on payment status change
        switch ($paymentStatus) {
            case 'paid':
                // If payment is confirmed and order is pending/confirmed, set to processing
                if (in_array($order['status'], ['pending', 'confirmed'])) {
                    $newOrderStatus = 'processing';
                }
                break;

            case 'failed':
                // If payment fails, cancel the order
                $newOrderStatus = 'cancelled';
                break;

            case 'refunded':
                // If refunded, mark as returned
                $newOrderStatus = 'returned';
                break;
        }

        $setClause = "payment_status = ?, status = ?";
        if (dbHasColumn($db, 'orders', 'updated_at')) {
            $setClause .= ", updated_at = NOW()";
        }

        $updateStmt = $db->prepare("
            UPDATE orders
            SET $setClause
            WHERE id = ?
        ");
        $updateStmt->execute([$paymentStatus, $newOrderStatus, $orderId]);

        sendJSON([
            'success' => true,
            'message' => 'Odeme durumu guncellendi',
            'previousPaymentStatus' => $oldPaymentStatus,
            'newPaymentStatus' => $paymentStatus,
            'previousOrderStatus' => $order['status'],
            'newOrderStatus' => $newOrderStatus
        ]);

    } catch (Exception $e) {
        logError("Payment Status Error: " . $e->getMessage());
        sendJSON(['error' => 'Odeme durumu guncellenirken hata olustu'], 500);
    }
}
