<?php
/**
 * DELETE /api/settings/banners/{id}
 * Delete a slider banner (admin only)
 */

require_once __DIR__ . '/../config.php';

function apiBannersDelete($id) {
    try {
        // Authenticate admin
        $user = adminAuth();

        // Get banner ID from parameter
        $id = (int)$id;

        if ($id <= 0) {
            sendJSON(['error' => 'Geçersiz ID'], 400);
        }

        $db = getDB();

        // Check if banner exists
        $stmt = $db->prepare("SELECT id FROM slider_banners WHERE id = ?");
        $stmt->execute([$id]);

        if (!$stmt->fetch()) {
            sendJSON(['error' => 'Banner bulunamadı'], 404);
        }

        // Delete banner
        $stmt = $db->prepare("DELETE FROM slider_banners WHERE id = ?");
        $stmt->execute([$id]);

        sendJSON([
            'message' => 'Banner silindi',
            'id' => $id
        ]);

    } catch (PDOException $e) {
        logError('Banners delete: ' . $e->getMessage());
        sendJSON(['error' => 'Banner silinemedi'], 500);
    } catch (Exception $e) {
        logError('Banners delete: ' . $e->getMessage());
        sendJSON(['error' => 'Banner silinemedi'], 500);
    }
}
