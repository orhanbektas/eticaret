<?php
// POST /api/contact
function apiContactCreate() {
    try {
        require_once __DIR__ . '/../config.php';

        $body = getBody();
        $name = trim($body['name'] ?? '');
        $email = trim($body['email'] ?? '');
        $subject = trim($body['subject'] ?? '');
        $message = trim($body['message'] ?? '');

        // Validate required fields
        if (empty($name)) {
            sendJSON(['error' => 'İsim alanı zorunludur'], 400);
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendJSON(['error' => 'Geçerli bir e-posta adresi girin'], 400);
        }

        if (empty($message)) {
            sendJSON(['error' => 'Mesaj alanı zorunludur'], 400);
        }

        $db = getDB();

        // Insert message (read=false)
        $stmt = $db->prepare("INSERT INTO contact_messages (name, email, subject, message, `read`, created_at)
                               VALUES (?, ?, ?, ?, 0, NOW())");
        $stmt->execute([$name, $email, $subject, $message]);

        $messageId = (int)$db->lastInsertId();

        sendJSON([
            'message' => 'Mesajınız başarıyla gönderildi',
            'id' => $messageId
        ], 201);

    } catch (Exception $e) {
        logError("Contact create error: " . $e->getMessage());
        sendJSON(['error' => 'Mesaj gönderilirken hata oluştu'], 500);
    }
}
