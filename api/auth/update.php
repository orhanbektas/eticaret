<?php
// PUT /api/auth/me
require_once __DIR__ . '/../config.php';

function apiUpdateMe() {
    try {
        $payload = auth();
        $body = getBody();

        $name = isset($body['name']) ? trim($body['name']) : null;
        $phone = isset($body['phone']) ? trim($body['phone']) : null;
        $address = isset($body['address']) ? trim($body['address']) : null;
        $password = $body['password'] ?? null;

        $db = getDB();
        $updates = [];
        $params = [];

        if ($name !== null) {
            if (strlen($name) < 2) {
                sendJSON(['error' => 'Isim en az 2 karakter olmali'], 400);
            }
            $updates[] = 'name = ?';
            $params[] = $name;
        }

        if ($phone !== null) {
            $updates[] = 'phone = ?';
            $params[] = $phone;
        }

        if ($address !== null) {
            $updates[] = 'address = ?';
            $params[] = $address;
        }

        if ($password !== null) {
            if (strlen($password) < 6) {
                sendJSON(['error' => 'Sifre en az 6 karakter olmali'], 400);
            }
            $updates[] = 'password = ?';
            $params[] = hashPassword($password);
        }

        if (!empty($updates)) {
            $params[] = $payload['id'];
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
        }

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
        logError("Update error: " . $e->getMessage());
        sendJSON(['error' => 'Guncelleme islemi basarisiz'], 500);
    }
}
