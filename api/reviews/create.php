<?php
/**
 * POST /api/reviews/{productId}
 */

require_once __DIR__ . '/../config.php';

function apiReviewsCreate($productId) {
    try {
        $user = auth();
        $body = getBody();

        $rating = (int)($body['rating'] ?? 0);
        $comment = trim($body['comment'] ?? '');

        if ($rating < 1 || $rating > 5) {
            sendJSON(['error' => 'Puan 1-5 arasinda olmalidir'], 400);
        }

        $commentLen = strlen($comment);
        if ($commentLen < 3 || $commentLen > 500) {
            sendJSON(['error' => 'Yorum 3-500 karakter arasinda olmalidir'], 400);
        }

        $db = getDB();

        $checkStmt = $db->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
        $checkStmt->execute([$productId, $user['id']]);
        if ($checkStmt->fetch()) {
            sendJSON(['error' => 'Bu urun icin zaten yorum yaptiniz'], 400);
        }

        $userName = $user['name'] ?? 'Misafir';

        $stmt = $db->prepare("INSERT INTO reviews (product_id, user_id, user_name, rating, comment, approved, created_at)
                              VALUES (?, ?, ?, ?, ?, 1, NOW())");
        $stmt->execute([$productId, $user['id'], $userName, $rating, $comment]);

        $reviewId = (int)$db->lastInsertId();

        $reviewStmt = $db->prepare("SELECT id, product_id, user_id, user_name, rating, comment, approved, created_at
                                    FROM reviews WHERE id = ?");
        $reviewStmt->execute([$reviewId]);
        $review = $reviewStmt->fetch();

        sendJSON([
            'message' => 'Yorumunuz basariyla eklendi',
            'review' => $review
        ], 201);

    } catch (Exception $e) {
        logError("Reviews create error: " . $e->getMessage());
        sendJSON(['error' => 'Yorum eklenirken hata olustu'], 500);
    }
}
