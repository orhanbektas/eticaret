<?php
// GET /api/blog
function apiBlogList() {
    try {
        require_once __DIR__ . '/../config.php';

        $db = getDB();

        $category = getParam('category');
        $search = getParam('search');
        $page = (int)getParam('page', 1);
        $limit = (int)getParam('limit', 6);

        $conditions = ['1=1'];
        $params = [];

        if ($category) {
            $conditions[] = "category = ?";
            $params[] = $category;
        }

        if ($search) {
            $conditions[] = "(title LIKE ? OR excerpt LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = implode(' AND ', $conditions);

        // Count total rows
        $countSql = "SELECT COUNT(*) as total FROM blog_posts WHERE $whereClause";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        // Pagination
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));
        $offset = ($page - 1) * $limit;
        $totalPages = (int)ceil($total / $limit);

        // Main query
        $sql = "SELECT id, title, slug, excerpt, content, category, author, author AS author_name, image, views, 1 AS status, created_at
                FROM blog_posts
                WHERE $whereClause
                ORDER BY created_at DESC
                LIMIT $limit OFFSET $offset";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll();

        sendJSON([
            'posts' => $posts,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages
        ]);

    } catch (Exception $e) {
        logError("Blog list error: " . $e->getMessage());
        sendJSON(['error' => 'Blog yazıları yüklenirken hata oluştu'], 500);
    }
}
