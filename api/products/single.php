<?php
function apiProductsSingle($slug) {
    try {
        $db = getDB();
        if (!$slug) { sendJSON(['error' => 'Ürün bulunamadı'], 400); }

        $stmt = $db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug, COALESCE(r.avg_rating, 0) as avg_rating, COALESCE(r.review_count, 0) as review_count FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN (SELECT product_id, AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews GROUP BY product_id) r ON r.product_id = p.id WHERE p.slug = ? AND p.active = 1");
        $stmt->execute([$slug]);
        $product = $stmt->fetch();

        if (!$product) { sendJSON(['error' => 'Ürün bulunamadı'], 404); }

        $product['images'] = json_decode($product['images'] ?? '[]', true) ?: [];
        $product['variants'] = json_decode($product['variants'] ?? '[]', true) ?: [];
        $product['category'] = $product['category_name'] ? ['name' => $product['category_name'], 'slug' => $product['category_slug']] : null;
        unset($product['category_name'], $product['category_slug']);

        $simStmt = $db->prepare("SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.images, COALESCE(r.avg_rating, 0) as avg_rating FROM products p LEFT JOIN (SELECT product_id, AVG(rating) as avg_rating FROM reviews GROUP BY product_id) r ON r.product_id = p.id WHERE p.active = 1 AND p.id != ? AND p.category_id = ? ORDER BY RAND() LIMIT 4");
        $simStmt->execute([$product['id'], $product['category_id'] ?? 0]);
        $similar = $simStmt->fetchAll();
        foreach ($similar as &$s) { $s['images'] = json_decode($s['images'] ?? '[]', true) ?: []; }
        $product['similar_products'] = $similar;

        sendJSON($product);
    } catch (Exception $e) {
        logError("Product single: " . $e->getMessage());
        sendJSON(['error' => 'Ürün yüklenirken hata oluştu'], 500);
    }
}
