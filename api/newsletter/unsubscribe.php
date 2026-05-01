<?php
// POST /api/newsletter/unsubscribe
function apiNewsletterUnsubscribe() {
    try {
        require_once __DIR__ . '/../config.php';

        $body = getBody();
        $email = trim($body['email'] ?? '');

        // Validate email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendJSON(['error' => 'Geçerli bir e-posta adresi girin'], 400);
        }

        $db = getDB();

        // Find and delete if exists
        $stmt = $db->prepare("SELECT id FROM newsletter_emails WHERE email = ?");
        $stmt->execute([$email]);
        $existing = $stmt->fetch();

        if ($existing) {
            $deleteStmt = $db->prepare("DELETE FROM newsletter_emails WHERE email = ?");
            $deleteStmt->execute([$email]);
        }

        // Always return success message
        sendJSON(['message' => 'Bülten aboneliğiniz iptal edildi']);

    } catch (Exception $e) {
        logError("Newsletter unsubscribe error: " . $e->getMessage());
        sendJSON(['error' => 'Abonelik iptal edilirken hata oluştu'], 500);
    }
}
