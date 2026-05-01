<?php
/**
 * POST /api/settings/banners
 */

require_once __DIR__ . '/../config.php';

function apiBannersCreate() {
    try {
        adminAuth();
        $body = getBody();

        $title = trim($body['title'] ?? '');
        $subtitle = trim($body['subtitle'] ?? '');
        $cta = trim($body['cta'] ?? '');
        $gradient = trim($body['gradient'] ?? '');
        $image = trim($body['image'] ?? '');
        $link = trim($body['link'] ?? '');
        $active = !empty($body['active']) ? 1 : 0;
        $order = isset($body['order']) ? (int)$body['order'] : 0;

        if ($title === '') {
            sendJSON(['error' => 'Baslik zorunludur'], 400);
        }
        if ($image === '') {
            sendJSON(['error' => 'Gorsel zorunludur'], 400);
        }

        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO slider_banners (title, subtitle, cta, gradient, image, link, active, `order`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $subtitle, $cta, $gradient, $image, $link, $active, $order]);

        sendJSON([
            'message' => 'Banner olusturuldu',
            'id' => (int)$db->lastInsertId()
        ], 201);
    } catch (Exception $e) {
        logError('Banners create: ' . $e->getMessage());
        sendJSON(['error' => 'Banner olusturulamadi'], 500);
    }
}
