<?php
// GET /api/blog/{slug}
function apiBlogSingle($slug) {
    try {
        require_once __DIR__ . '/../config.php';

        $db = getDB();

        // Find post by slug
        $stmt = $db->prepare("SELECT id, title, slug, excerpt, content, category, author, author AS author_name, image, views, 1 AS status, created_at
                              FROM blog_posts
                              WHERE slug = ?");
        $stmt->execute([$slug]);
        $post = $stmt->fetch();

        if (!$post) {
            sendJSON(['error' => 'Blog yazısı bulunamadı'], 404);
        }

        // Increment views
        $updateStmt = $db->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?");
        $updateStmt->execute([$post['id']]);
        $post['views'] = $post['views'] + 1;

        // Get related posts (same category, different id)
        $relatedStmt = $db->prepare("SELECT id, title, slug, excerpt, category, author AS author_name, image, created_at
                                    FROM blog_posts
                                    WHERE category = ? AND id != ?
                                    ORDER BY created_at DESC
                                    LIMIT 3");
        $relatedStmt->execute([$post['category'], $post['id']]);
        $related = $relatedStmt->fetchAll();

        sendJSON([
            'post' => $post,
            'related' => $related
        ]);

    } catch (Exception $e) {
        logError("Blog single error: " . $e->getMessage());
        sendJSON(['error' => 'Blog yazısı yüklenirken hata oluştu'], 500);
    }
}
