<?php
/**
 * PUT /api/admin/coupons/{id}
 */

require_once __DIR__ . '/../config.php';

function apiCouponsUpdate($id) {
    try {
        adminAuth();
        $db = getDB();
        $body = getBody();

        $id = (int)$id;
        if (!$id) {
            sendJSON(['error' => 'Gecersiz kupon ID'], 400);
        }

        $stmt = $db->prepare("SELECT id FROM coupons WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            sendJSON(['error' => 'Kupon bulunamadi'], 404);
        }

        $active = isset($body['active']) ? (int)$body['active'] : 1;

        $stmt = $db->prepare("UPDATE coupons SET active = ? WHERE id = ?");
        $stmt->execute([$active, $id]);

        sendJSON(['message' => 'Kupon guncellendi']);
    } catch (PDOException $e) {
        logError("Coupon update error: " . $e->getMessage());
        sendJSON(['error' => 'Kupon guncellenemedi'], 500);
    } catch (Exception $e) {
        logError("Coupon update error: " . $e->getMessage());
        sendJSON(['error' => 'Sunucu hatasi'], 500);
    }
}
