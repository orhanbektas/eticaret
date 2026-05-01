<?php
// POST /api/auth/register
require_once __DIR__ . '/../config.php';

function apiRegister() {
    try {
        $body = getBody();
        $name = trim($body['name'] ?? '');
        $email = trim($body['email'] ?? '');
        $password = $body['password'] ?? '';
        $phone = trim($body['phone'] ?? '');
        $address = trim($body['address'] ?? '');

        if (strlen($name) < 2) {
            sendJSON(['error' => 'Isim en az 2 karakter olmali'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendJSON(['error' => 'Gecerli bir e-posta adresi girin'], 400);
        }

        if (strlen($password) < 6) {
            sendJSON(['error' => 'Sifre en az 6 karakter olmali'], 400);
        }

        $db = getDB();

        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            sendJSON(['error' => 'Bu e-posta adresi zaten kayitli'], 400);
        }

        $hashedPassword = hashPassword($password);
        $stmt = $db->prepare("INSERT INTO users (name, email, password, phone, address, role, created_at) VALUES (?, ?, ?, ?, ?, 'user', NOW())");
        $stmt->execute([$name, $email, $hashedPassword, $phone, $address]);

        $userId = (int)$db->lastInsertId();
        $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        $token = signToken($user);

        sendJSON([
            'token' => $token,
            'user' => [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ], 201);
    } catch (Exception $e) {
        logError("Register error: " . $e->getMessage());
        sendJSON(['error' => 'Kayit islemi basarisiz'], 500);
    }
}
