<?php
/**
 * GET /api/settings/banners/all
 */

require_once __DIR__ . '/../config.php';

function apiBannersAdmin() {
    try {
        adminAuth();
        $db = getDB();

        $stmt = $db->prepare("
            SELECT id, title, subtitle, cta, gradient, image, link, active, `order`
            FROM slider_banners
            ORDER BY `order` ASC
        ");
        $stmt->execute();
        $banners = $stmt->fetchAll();

        sendJSON(['banners' => $banners]);
    } catch (Exception $e) {
        logError('Banners admin: ' . $e->getMessage());
        sendJSON(['error' => 'Bannerlar alinamadi'], 500);
    }
}
