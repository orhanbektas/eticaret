<?php
/**
 * GET /api/settings/banners
 */

require_once __DIR__ . '/../config.php';

function apiBannersList() {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT id, title, subtitle, cta, gradient, image, link, `order`
            FROM slider_banners
            WHERE active = 1
            ORDER BY `order` ASC
        ");
        $stmt->execute();
        $banners = $stmt->fetchAll();

        sendJSON(['banners' => $banners]);
    } catch (Exception $e) {
        logError('Banners list: ' . $e->getMessage());
        sendJSON(['error' => 'Bannerlar alinamadi'], 500);
    }
}
