<?php
// PUT /api/contact/{id}/read
function apiContactRead($id) {
    try {
        adminAuth();
        $db = getDB();

        // Check if message exists
        $stmt = $db->prepare("SELECT id FROM contact_messages WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            sendJSON(['error' => 'Mesaj bulunamadı'], 404);
        }

        // Update read=1
        $updateStmt = $db->prepare("UPDATE contact_messages SET `read` = 1 WHERE id = ?");
        $updateStmt->execute([$id]);

        sendJSON(['message' => 'Mesaj okundu olarak işaretlendi']);

    } catch (Exception $e) {
        logError("Contact read error: " . $e->getMessage());
        sendJSON(['error' => 'Mesaj güncellenirken hata oluştu'], 500);
    }
}
