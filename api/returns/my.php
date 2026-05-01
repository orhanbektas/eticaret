<?php
/**
 * GET /api/returns/my-returns
 */

require_once __DIR__ . '/../config.php';

function apiReturnsMy() {
    try {
        $user = auth();
        $db = getDB();

        $stmt = $db->prepare("
            SELECT
                r.id,
                r.order_id,
                CAST(o.id AS CHAR) AS order_number,
                r.reason,
                r.description,
                r.refund_type,
                r.status,
                r.created_at
            FROM returns r
            LEFT JOIN orders o ON r.order_id = o.id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$user['id']]);
        $returns = $stmt->fetchAll();

        sendJSON(['returns' => $returns]);
    } catch (Exception $e) {
        logError('Returns my: ' . $e->getMessage());
        sendJSON(['error' => 'Iade talepleri alinamadi'], 500);
    }
}
