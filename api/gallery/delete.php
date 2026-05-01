<?php
/**
 * DELETE /api/gallery/{id}
 * Delete a gallery item (admin only)
 */

require_once __DIR__ . '/../config.php';

function apiGalleryDelete($id) {
    try {
        // Authenticate admin
        $user = adminAuth();

        // Get gallery ID from parameter
        $id = (int)$id;

        if ($id <= 0) {
            sendJSON(['error' => 'Geçersiz ID'], 400);
        }

        $db = getDB();

        // Check if gallery item exists
        $stmt = $db->prepare("SELECT id FROM gallery WHERE id = ?");
        $stmt->execute([$id]);

        if (!$stmt->fetch()) {
            sendJSON(['error' => 'Galeri öğesi bulunamadı'], 404);
        }

        // Delete gallery item
        $stmt = $db->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->execute([$id]);

        sendJSON([
            'message' => 'Galeri öğesi silindi',
            'id' => $id
        ]);

    } catch (PDOException $e) {
        logError('Gallery delete: ' . $e->getMessage());
        sendJSON(['error' => 'Galeri silinemedi'], 500);
    } catch (Exception $e) {
        logError('Gallery delete: ' . $e->getMessage());
        sendJSON(['error' => 'Galeri silinemedi'], 500);
    }
}
