<?php
/**
 * GET /api/returns/admin/all
 */

require_once __DIR__ . '/../config.php';

function apiReturnsAdminList() {
    try {
        adminAuth();
        $db = getDB();

        $stmt = $db->prepare("
            SELECT
                r.id,
                r.order_id,
                CAST(o.id AS CHAR) AS order_number,
                r.user_id,
                r.user_name,
                r.user_email,
                r.reason,
                r.description,
                r.refund_type,
                r.status,
                r.created_at
            FROM returns r
            LEFT JOIN orders o ON r.order_id = o.id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
        $returns = $stmt->fetchAll();

        sendJSON(['returns' => $returns]);
    } catch (Exception $e) {
        logError('Returns admin list: ' . $e->getMessage());
        sendJSON(['error' => 'Iade talepleri alinamadi'], 500);
    }
}
