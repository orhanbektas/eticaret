<?php
// GET /api/admin/categories

function apiCategoriesList() {
    try {
        adminAuth();
        $db = getDB();

        $stmt = $db->query("
            SELECT c.*, COUNT(p.id) as product_count
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id AND p.active = 1
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        $categories = $stmt->fetchAll();

        sendJSON($categories);
    } catch (PDOException $e) {
        logError("Categories list error: " . $e->getMessage());
        sendJSON(['error' => 'Kategoriler alınamadı'], 500);
    } catch (Exception $e) {
        logError("Categories list error: " . $e->getMessage());
        sendJSON(['error' => 'Sunucu hatası'], 500);
    }
}
