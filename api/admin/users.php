<?php
// GET /api/admin/users

function apiAdminUsers() {
    try {
        adminAuth();
        $db = getDB();

        $stmt = $db->query("SELECT id, name, email, role, phone, address, created_at FROM users ORDER BY created_at DESC");
        $users = $stmt->fetchAll();

        sendJSON($users);
    } catch (PDOException $e) {
        logError("Admin users error: " . $e->getMessage());
        sendJSON(['error' => 'Kullanıcılar alınamadı'], 500);
    } catch (Exception $e) {
        logError("Admin users error: " . $e->getMessage());
        sendJSON(['error' => 'Sunucu hatası'], 500);
    }
}
