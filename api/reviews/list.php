<?php
// GET /api/reviews/{productId}
function apiReviewsList($productId) {
    try {
        require_once __DIR__ . '/../config.php';

        $db = getDB();

        // Get all reviews for product
        $stmt = $db->prepare("SELECT r.id, r.product_id, r.user_id, r.rating, r.comment, r.approved, r.created_at,
                                    u.name as user_name
                              FROM reviews r
                              LEFT JOIN users u ON r.user_id = u.id
                              WHERE r.product_id = ? AND r.approved = 1
                              ORDER BY r.created_at DESC");
        $stmt->execute([$productId]);
        $reviews = $stmt->fetchAll();

        // Calculate average rating
        $avgStmt = $db->prepare("SELECT AVG(rating) as avgRating, COUNT(*) as count
                                 FROM reviews
                                 WHERE product_id = ? AND approved = 1");
        $avgStmt->execute([$productId]);
        $stats = $avgStmt->fetch();

        $avgRating = $stats['avgRating'] ? round((float)$stats['avgRating'], 1) : 0;
        $count = (int)$stats['count'];

        sendJSON([
            'reviews' => $reviews,
            'avgRating' => $avgRating,
            'count' => $count
        ]);

    } catch (Exception $e) {
        logError("Reviews list error: " . $e->getMessage());
        sendJSON(['error' => 'Yorumlar yüklenirken hata oluştu'], 500);
    }
}
