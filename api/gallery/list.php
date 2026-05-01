<?php
/**
 * GET /api/gallery
 */

require_once __DIR__ . '/../config.php';

function apiGalleryList() {
    try {
        $db = getDB();
        $hasMediaType = dbHasColumn($db, 'gallery', 'media_type');
        $hasThumbnail = dbHasColumn($db, 'gallery', 'thumbnail_url');

        $stmt = $db->prepare("
            SELECT
                id,
                title,
                image_url
                " . ($hasMediaType ? ", media_type" : ", 'image' AS media_type") . "
                " . ($hasThumbnail ? ", thumbnail_url" : ", '' AS thumbnail_url") . "
            FROM gallery
            ORDER BY id DESC
        ");
        $stmt->execute();
        $items = $stmt->fetchAll();

        sendJSON($items);
    } catch (Exception $e) {
        logError('Gallery list: ' . $e->getMessage());
        sendJSON(['error' => 'Galeri alinamadi'], 500);
    }
}
