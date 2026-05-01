<?php
// POST /api/auth/login
require_once __DIR__ . '/../config.php';

function apiLogin() {
    try {
        $body = getBody();
        $email = trim($body['email'] ?? '');
        $password = $body['password'] ?? '';

        if (empty($email) || empty($password)) {
            sendJSON(['error' => 'E-posta ve sifre gerekli'], 400);
        }

        $db = getDB();
        $stmt = $db->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !verifyPassword($password, $user['password'])) {
            sendJSON(['error' => 'Gecersiz e-posta veya sifre'], 401);
        }

        $token = signToken($user);

        sendJSON([
            'token' => $token,
            'user' => [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    } catch (Exception $e) {
        logError("Login error: " . $e->getMessage());
        sendJSON(['error' => 'Giris islemi basarisiz'], 500);
    }
}
