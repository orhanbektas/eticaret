<?php
// DELETE /api/reviews/{reviewId}
function apiReviewsDelete($reviewId) {
    try {
        $user = auth();
        $db = getDB();

        // Get review
        $stmt = $db->prepare("SELECT user_id FROM reviews WHERE id = ?");
        $stmt->execute([$reviewId]);
        $review = $stmt->fetch();

        if (!$review) {
            sendJSON(['error' => 'Yorum bulunamadı'], 404);
        }

        // Check ownership or admin
        if ($review['user_id'] != $user['id'] && $user['role'] !== 'admin') {
            sendJSON(['error' => 'Bu yorumu silme yetkiniz yok'], 403);
        }

        // Delete review
        $deleteStmt = $db->prepare("DELETE FROM reviews WHERE id = ?");
        $deleteStmt->execute([$reviewId]);

        sendJSON(['message' => 'Yorum başarıyla silindi']);

    } catch (Exception $e) {
        logError("Reviews delete error: " . $e->getMessage());
        sendJSON(['error' => 'Yorum silinirken hata oluştu'], 500);
    }
}
