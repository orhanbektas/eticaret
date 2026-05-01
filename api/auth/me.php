<?php
// GET /api/auth/me
require_once __DIR__ . '/../config.php';

function apiMe() {
    try {
        $payload = auth();

        $db = getDB();
        $stmt = $db->prepare("SELECT id, name, email, role, phone, address FROM users WHERE id = ?");
        $stmt->execute([$payload['id']]);
        $user = $stmt->fetch();

        if (!$user) {
            sendJSON(['error' => 'Kullanici bulunamadi'], 404);
        }

        sendJSON([
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'phone' => $user['phone'],
            'address' => $user['address']
        ]);
    } catch (Exception $e) {
        logError("Me error: " . $e->getMessage());
        sendJSON(['error' => 'Kullanici bilgileri alinamadi'], 500);
    }
}
