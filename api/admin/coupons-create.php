<?php
/**
 * POST /api/admin/coupons
 */

require_once __DIR__ . '/../config.php';

function apiCouponsCreate() {
    try {
        adminAuth();
        $db = getDB();
        $body = getBody();

        $code = strtoupper(trim($body['code'] ?? ''));
        if (!$code) {
            sendJSON(['error' => 'Kupon kodu gerekli'], 400);
        }

        $discountType = trim($body['discount_type'] ?? 'percent');
        $discountValue = (float)($body['discount_value'] ?? 0);
        if ($discountValue <= 0) {
            sendJSON(['error' => 'Indirim degeri gecerli degil'], 400);
        }

        $minOrderAmount = (float)($body['min_order_amount'] ?? 0);
        $maxUses = isset($body['max_uses']) ? (int)$body['max_uses'] : null;
        $validUntil = trim($body['valid_until'] ?? '');

        $stmt = $db->prepare("
            INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, max_uses, valid_until, active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
        ");
        $stmt->execute([$code, $discountType, $discountValue, $minOrderAmount, $maxUses, $validUntil ?: null]);

        $id = $db->lastInsertId();

        sendJSON([
            'message' => 'Kupon olusturuldu',
            'id' => (int)$id
        ], 201);
    } catch (PDOException $e) {
        logError("Coupon create error: " . $e->getMessage());
        sendJSON(['error' => 'Kupon olusturulamadi'], 500);
    } catch (Exception $e) {
        logError("Coupon create error: " . $e->getMessage());
        sendJSON(['error' => 'Sunucu hatasi'], 500);
    }
}
