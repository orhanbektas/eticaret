<?php
/**
 * PUT /api/orders/{id}/tracking - Add tracking number (Admin only)
 * Updates tracking number and sets status to 'shipped'
 */

require_once __DIR__ . '/../config.php';

function apiOrdersTracking($id) {
    try {
        // Admin authentication required
        adminAuth();

        $body = getBody();
        $db = getDB();
        $orderId = (int)$id;

        $trackingNumber = trim($body['tracking_number'] ?? '');

        if (empty($trackingNumber)) {
            sendJSON(['error' => 'Kargo takip numarasi gereklidir'], 400);
        }

        // Check if order exists
        $orderStmt = $db->prepare("SELECT id, status, tracking_number FROM orders WHERE id = ?");
        $orderStmt->execute([$orderId]);
        $order = $orderStmt->fetch();

        if (!$order) {
            sendJSON(['error' => 'Siparis bulunamadi'], 404);
        }

        $setClause = "tracking_number = ?, status = 'shipped'";
        if (dbHasColumn($db, 'orders', 'updated_at')) {
            $setClause .= ", updated_at = NOW()";
        }

        $updateStmt = $db->prepare("
            UPDATE orders
            SET $setClause
            WHERE id = ?
        ");
        $updateStmt->execute([$trackingNumber, $orderId]);

        sendJSON([
            'success' => true,
            'message' => 'Kargo takip numarasi eklendi ve siparis kargoya verildi',
            'trackingNumber' => $trackingNumber,
            'previousStatus' => $order['status'],
            'newStatus' => 'shipped'
        ]);

    } catch (Exception $e) {
        logError("Orders Tracking Error: " . $e->getMessage());
        sendJSON(['error' => 'Kargo takip numarasi eklenirken hata olustu'], 500);
    }
}
