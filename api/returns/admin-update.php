<?php
/**
 * PUT /api/returns/admin/{id}
 * Update return request status (admin only)
 */

require_once __DIR__ . '/../config.php';

function apiReturnsAdminUpdate($id) {
    try {
        // Authenticate admin
        $user = adminAuth();

        // Get return ID from parameter
        $id = (int)$id;

        if ($id <= 0) {
            sendJSON(['error' => 'Geçersiz ID'], 400);
        }

        // Get status from body
        $body = getBody();
        $status = $body['status'] ?? '';

        // Validate status
        $validStatuses = ['pending', 'approved', 'rejected', 'completed'];

        if (empty($status)) {
            sendJSON(['error' => 'Durum zorunludur'], 400);
        }

        if (!in_array($status, $validStatuses)) {
            sendJSON(['error' => 'Geçersiz durum'], 400);
        }

        $db = getDB();

        // Check if return exists
        $stmt = $db->prepare("SELECT id FROM returns WHERE id = ?");
        $stmt->execute([$id]);

        if (!$stmt->fetch()) {
            sendJSON(['error' => 'İade talebi bulunamadı'], 404);
        }

        // Update status
        $stmt = $db->prepare("UPDATE returns SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        sendJSON([
            'message' => 'İade durumu güncellendi',
            'id' => $id,
            'status' => $status
        ]);

    } catch (PDOException $e) {
        logError('Returns admin update: ' . $e->getMessage());
        sendJSON(['error' => 'İade güncellenemedi'], 500);
    } catch (Exception $e) {
        logError('Returns admin update: ' . $e->getMessage());
        sendJSON(['error' => 'Iade guncellenemedi'], 500);
    }
}
