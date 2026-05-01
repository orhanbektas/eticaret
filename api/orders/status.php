<?php
/**
 * PUT /api/orders/{id}/status - Update order status (Admin only)
 */

require_once __DIR__ . '/../config.php';

function apiOrdersStatus($id) {
    try {
        // Admin authentication required
        adminAuth();

        $body = getBody();
        $db = getDB();
        $orderId = (int)$id;

        // Valid statuses
        $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];

        $newStatus = trim($body['status'] ?? '');

        if (empty($newStatus)) {
            sendJSON(['error' => 'Durum bilgisi gereklidir'], 400);
        }

        if (!in_array($newStatus, $validStatuses)) {
            sendJSON(['error' => 'Gecersiz siparis durumu'], 400);
        }

        // Check if order exists
        $orderStmt = $db->prepare("SELECT id, status FROM orders WHERE id = ?");
        $orderStmt->execute([$orderId]);
        $order = $orderStmt->fetch();

        if (!$order) {
            sendJSON(['error' => 'Siparis bulunamadi'], 404);
        }

        $oldStatus = $order['status'];

        $setClause = "status = ?";
        if (dbHasColumn($db, 'orders', 'updated_at')) {
            $setClause .= ", updated_at = NOW()";
        }

        $updateStmt = $db->prepare("
            UPDATE orders
            SET $setClause
            WHERE id = ?
        ");
        $updateStmt->execute([$newStatus, $orderId]);

        sendJSON([
            'success' => true,
            'message' => 'Siparis durumu guncellendi',
            'oldStatus' => $oldStatus,
            'newStatus' => $newStatus
        ]);

    } catch (Exception $e) {
        logError("Orders Status Error: " . $e->getMessage());
        sendJSON(['error' => 'Siparis durumu guncellenirken hata olustu'], 500);
    }
}
