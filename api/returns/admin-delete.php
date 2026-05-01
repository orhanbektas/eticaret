<?php
/**
 * DELETE /api/returns/admin/{id}
 * Delete a return request (admin only)
 */

require_once __DIR__ . '/../config.php';

function apiReturnsAdminDelete($id) {
    try {
        // Authenticate admin
        $user = adminAuth();

        // Get return ID from parameter
        $id = (int)$id;

        if ($id <= 0) {
            sendJSON(['error' => 'Geçersiz ID'], 400);
        }

        $db = getDB();

        // Check if return exists
        $stmt = $db->prepare("SELECT id FROM returns WHERE id = ?");
        $stmt->execute([$id]);

        if (!$stmt->fetch()) {
            sendJSON(['error' => 'İade talebi bulunamadı'], 404);
        }

        // Delete return
        $stmt = $db->prepare("DELETE FROM returns WHERE id = ?");
        $stmt->execute([$id]);

        sendJSON([
            'message' => 'İade talebi silindi',
            'id' => $id
        ]);

    } catch (PDOException $e) {
        logError('Returns admin delete: ' . $e->getMessage());
        sendJSON(['error' => 'İade silinemedi'], 500);
    } catch (Exception $e) {
        logError('Returns admin delete: ' . $e->getMessage());
        sendJSON(['error' => 'Iade silinemedi'], 500);
    }
}
