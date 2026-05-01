<?php
// GET /api/blog/featured
function apiBlogFeatured() {
    try {
        require_once __DIR__ . '/../config.php';

        $db = getDB();

        $stmt = $db->query("SELECT id, title, slug, excerpt, content, category, author, author AS author_name, image, views, 1 AS status, created_at
                            FROM blog_posts
                            ORDER BY views DESC
                            LIMIT 3");
        $posts = $stmt->fetchAll();

        sendJSON(['posts' => $posts]);

    } catch (Exception $e) {
        logError("Blog featured error: " . $e->getMessage());
        sendJSON(['error' => 'Öne çıkan yazılar yüklenirken hata oluştu'], 500);
    }
}
