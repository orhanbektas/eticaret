<?php
/**
 * POST /api/admin/posts
 */

require_once __DIR__ . '/../config.php';

function apiPostsCreate() {
    try {
        adminAuth();
        $db = getDB();
        $body = getBody();

        $title = trim($body['title'] ?? '');
        if (!$title) {
            sendJSON(['error' => 'Baslik gerekli'], 400);
        }

        $slug = !empty($body['slug']) ? trim($body['slug']) : makeSlug($title);
        $excerpt = trim($body['excerpt'] ?? '');
        $content = trim($body['content'] ?? '');
        $category = trim($body['category'] ?? 'Genel');
        $image = trim($body['image'] ?? '');
        $author = trim($body['author'] ?? 'Admin');
        $views = 0;

        $stmt = $db->prepare("
            INSERT INTO blog_posts (title, slug, excerpt, content, category, image, author, views, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$title, $slug, $excerpt, $content, $category, $image, $author, $views]);

        $id = $db->lastInsertId();

        sendJSON([
            'message' => 'Yazi olusturuldu',
            'id' => (int)$id
        ], 201);
    } catch (PDOException $e) {
        logError("Post create error: " . $e->getMessage());
        sendJSON(['error' => 'Yazi olusturulamadi'], 500);
    } catch (Exception $e) {
        logError("Post create error: " . $e->getMessage());
        sendJSON(['error' => 'Sunucu hatasi'], 500);
    }
}
