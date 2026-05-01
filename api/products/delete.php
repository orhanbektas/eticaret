<?php
require_once __DIR__ . '/../config.php';

function apiProductsDelete($id) {
    try {
        adminAuth();

        $db = getDB();
        $productId = (int)$id;

        if ($productId <= 0) {
            sendJSON(['error' => 'Urun ID gerekli'], 400);
        }

        $checkStmt = $db->prepare("SELECT id FROM products WHERE id = ?");
        $checkStmt->execute([$productId]);
        if (!$checkStmt->fetch()) {
            sendJSON(['error' => 'Urun bulunamadi'], 404);
        }

        $deleteStmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $deleteStmt->execute([$productId]);

        sendJSON(['message' => 'Urun basariyla silindi']);
    } catch (Exception $e) {
        logError("Product delete error: " . $e->getMessage());
        sendJSON(['error' => 'Urun silinirken hata olustu'], 500);
    }
}
