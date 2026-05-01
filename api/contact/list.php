<?php
// GET /api/contact (admin only)
function apiContactList() {
    try {
        require_once __DIR__ . '/../config.php';

        adminAuth();
        $db = getDB();

        $stmt = $db->query("SELECT id, name, email, subject, message, `read`, created_at
                             FROM contact_messages
                             ORDER BY created_at DESC");
        $messages = $stmt->fetchAll();

        sendJSON(['messages' => $messages]);

    } catch (Exception $e) {
        logError("Contact list error: " . $e->getMessage());
        sendJSON(['error' => 'Mesajlar yüklenirken hata oluştu'], 500);
    }
}
