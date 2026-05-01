<?php
// POST /api/newsletter/subscribe
function apiNewsletterSubscribe() {
    try {
        require_once __DIR__ . '/../config.php';

        $body = getBody();
        $email = trim($body['email'] ?? '');

        // Validate email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendJSON(['error' => 'Geçerli bir e-posta adresi girin'], 400);
        }

        $db = getDB();

        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM newsletter_emails WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            sendJSON(['error' => 'Bu e-posta adresi zaten abone'], 400);
        }

        // Insert subscription
        $insertStmt = $db->prepare("INSERT INTO newsletter_emails (email, created_at) VALUES (?, NOW())");
        $insertStmt->execute([$email]);

        sendJSON([
            'message' => 'Bültene başarıyla abone oldunuz'
        ], 201);

    } catch (Exception $e) {
        logError("Newsletter subscribe error: " . $e->getMessage());
        sendJSON(['error' => 'Abone olurken hata oluştu'], 500);
    }
}
