<?php
/**
 * POST /api/gallery
 */

require_once __DIR__ . '/../config.php';

function apiGalleryCreate() {
    try {
        adminAuth();
        $body = getBody();

        $title = trim($body['title'] ?? '');
        $imageUrl = trim($body['image_url'] ?? '');
        $mediaType = ($body['media_type'] ?? 'image') === 'video' ? 'video' : 'image';
        $thumbnailUrl = trim($body['thumbnail_url'] ?? '');

        if ($title === '') {
            sendJSON(['error' => 'Baslik zorunludur'], 400);
        }
        if ($imageUrl === '') {
            sendJSON(['error' => 'Gorsel URL zorunludur'], 400);
        }

        $db = getDB();
        $hasMediaType = dbHasColumn($db, 'gallery', 'media_type');
        $hasThumbnail = dbHasColumn($db, 'gallery', 'thumbnail_url');

        if ($hasMediaType && $hasThumbnail) {
            $stmt = $db->prepare("INSERT INTO gallery (title, image_url, media_type, thumbnail_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $imageUrl, $mediaType, $thumbnailUrl]);
        } elseif ($hasMediaType) {
            $stmt = $db->prepare("INSERT INTO gallery (title, image_url, media_type) VALUES (?, ?, ?)");
            $stmt->execute([$title, $imageUrl, $mediaType]);
        } else {
            $stmt = $db->prepare("INSERT INTO gallery (title, image_url) VALUES (?, ?)");
            $stmt->execute([$title, $imageUrl]);
        }

        sendJSON([
            'message' => 'Galeri ogesi olusturuldu',
            'id' => (int)$db->lastInsertId(),
            'title' => $title,
            'image_url' => $imageUrl,
            'media_type' => $mediaType,
            'thumbnail_url' => $thumbnailUrl
        ], 201);
    } catch (Exception $e) {
        logError('Gallery create: ' . $e->getMessage());
        sendJSON(['error' => 'Galeri olusturulamadi'], 500);
    }
}
