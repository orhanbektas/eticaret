<?php
// GET /api/admin/coupons

function apiCouponsList() {
    try {
        adminAuth();
        $db = getDB();

        $stmt = $db->query("SELECT * FROM coupons ORDER BY created_at DESC");
        $coupons = $stmt->fetchAll();

        sendJSON($coupons);
    } catch (PDOException $e) {
        logError("Coupons list error: " . $e->getMessage());
        sendJSON(['error' => 'Kuponlar alınamadı'], 500);
    } catch (Exception $e) {
        logError("Coupons list error: " . $e->getMessage());
        sendJSON(['error' => 'Sunucu hatası'], 500);
    }
}
