<?php
// GET /api/blog/categories
function apiBlogCategories() {
    try {
        require_once __DIR__ . '/../config.php';

        $db = getDB();

        $stmt = $db->query("SELECT category as name, COUNT(*) as count
                            FROM blog_posts
                            GROUP BY category
                            ORDER BY count DESC");
        $categories = $stmt->fetchAll();

        sendJSON(['categories' => $categories]);

    } catch (Exception $e) {
        logError("Blog categories error: " . $e->getMessage());
        sendJSON(['error' => 'Kategoriler yüklenirken hata oluştu'], 500);
    }
}
