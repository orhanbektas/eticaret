<?php
/**
 * POST /api/returns
 * Create a new return request
 */

require_once __DIR__ . '/../config.php';

function apiReturnsCreate() {
    try {
        $user = auth();
        $body = getBody();

        $orderId = $body['order_id'] ?? null;
        $reason = $body['reason'] ?? null;
        $description = $body['description'] ?? '';
        $refundType = $body['refund_type'] ?? 'bank';

        if (empty($orderId)) {
            sendJSON(['error' => 'Siparis ID zorunludur'], 400);
        }

        if (empty($reason)) {
            sendJSON(['error' => 'Iade sebebi zorunludur'], 400);
        }

        $validRefundTypes = ['bank', 'credit'];
        if (!in_array($refundType, $validRefundTypes)) {
            sendJSON(['error' => 'Gecersiz iade turu'], 400);
        }

        $db = getDB();

        $stmt = $db->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $user['id']]);

        if (!$stmt->fetch()) {
            sendJSON(['error' => 'Siparis bulunamadi'], 404);
        }

        $userName = $user['name'] ?? 'Misafir';
        $userEmail = $user['email'] ?? '';

        $stmt = $db->prepare("
            INSERT INTO returns (user_id, order_id, user_name, user_email, reason, description, refund_type, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([
            $user['id'],
            $orderId,
            $userName,
            $userEmail,
            $reason,
            $description,
            $refundType
        ]);

        $insertId = $db->lastInsertId();

        sendJSON([
            'message' => 'Iade talebi olusturuldu',
            'id' => (int)$insertId
        ], 201);

    } catch (PDOException $e) {
        logError('Returns create: ' . $e->getMessage());
        sendJSON(['error' => 'Iade talebi olusturulamadi'], 500);
    } catch (Exception $e) {
        logError('Returns create: ' . $e->getMessage());
        sendJSON(['error' => 'Sunucu hatasi'], 500);
    }
}
