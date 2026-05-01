<?php
/**
 * PUT /api/gallery/{id}
 * Update a gallery item (admin only)
 */

require_once __DIR__ . '/../config.php';

function apiGalleryUpdate($id) {
    try {
        adminAuth();

        $id = (int)$id;
        if ($id <= 0) {
            sendJSON(['error' => 'Gecersiz ID'], 400);
        }

        $body = getBody();
        $title = trim($body['title'] ?? '');
        $image_url = trim($body['image_url'] ?? '');
        $media_type = ($body['media_type'] ?? 'image') === 'video' ? 'video' : 'image';
        $thumbnail_url = trim($body['thumbnail_url'] ?? '');

        if (!$title) {
            sendJSON(['error' => 'Baslik zorunludur'], 400);
        }

        if (!$image_url) {
            sendJSON(['error' => 'Medya URL zorunludur'], 400);
        }

        $db = getDB();
        $hasMediaType = dbHasColumn($db, 'gallery', 'media_type');
        $hasThumbnail = dbHasColumn($db, 'gallery', 'thumbnail_url');

        $check = $db->prepare("SELECT id FROM gallery WHERE id = ?");
        $check->execute([$id]);
        if (!$check->fetch()) {
            sendJSON(['error' => 'Galeri ogesi bulunamadi'], 404);
        }

        if ($hasMediaType && $hasThumbnail) {
            $stmt = $db->prepare("UPDATE gallery SET title = ?, image_url = ?, media_type = ?, thumbnail_url = ? WHERE id = ?");
            $stmt->execute([$title, $image_url, $media_type, $thumbnail_url, $id]);
        } elseif ($hasMediaType) {
            $stmt = $db->prepare("UPDATE gallery SET title = ?, image_url = ?, media_type = ? WHERE id = ?");
            $stmt->execute([$title, $image_url, $media_type, $id]);
        } else {
            $stmt = $db->prepare("UPDATE gallery SET title = ?, image_url = ? WHERE id = ?");
            $stmt->execute([$title, $image_url, $id]);
        }

        sendJSON([
            'message' => 'Galeri ogesi guncellendi',
            'id' => $id
        ]);
    } catch (PDOException $e) {
        logError('Gallery update: ' . $e->getMessage());
        sendJSON(['error' => 'Galeri guncellenemedi'], 500);
    } catch (Exception $e) {
        logError('Gallery update: ' . $e->getMessage());
        sendJSON(['error' => 'Galeri guncellenemedi'], 500);
    }
}
