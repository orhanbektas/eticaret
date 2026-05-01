<?php
function apiProductsList() {
    try {
        $db = getDB();
        $category = getParam('category');
        $featured = getParam('featured');
        $search = getParam('search');
        $sort = getParam('sort', 'newest');
        $page = max(1, (int)getParam('page', 1));
        $limit = min(100, max(1, (int)getParam('limit', 12)));
        $price_min = getParam('price_min');
        $price_max = getParam('price_max');
        $rating = getParam('rating');
        $in_stock = getParam('in_stock');
        $has_sale = getParam('has_sale');
        $brand = getParam('brand');

        $conditions = ['p.active = 1'];
        $params = [];

        if ($category) { $conditions[] = "c.slug = ?"; $params[] = $category; }
        if ($featured === 'true') { $conditions[] = "p.featured = 1"; }
        if ($search) {
            $conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%$search%"; $params[] = "%$search%";
        }
        if ($price_min !== null) { $conditions[] = "COALESCE(p.sale_price, p.price) >= ?"; $params[] = (float)$price_min; }
        if ($price_max !== null) { $conditions[] = "COALESCE(p.sale_price, p.price) <= ?"; $params[] = (float)$price_max; }
        if ($rating !== null) { $conditions[] = "COALESCE(r.avg_rating, 0) >= ?"; $params[] = (float)$rating; }
        if ($in_stock === 'true') { $conditions[] = "p.stock > 0"; }
        if ($has_sale === 'true') { $conditions[] = "p.sale_price IS NOT NULL AND p.sale_price < p.price"; }
        if ($brand) { $conditions[] = "p.material LIKE ?"; $params[] = "%$brand%"; }

        $where = implode(' AND ', $conditions);

        $sortMap = [
            'price_asc' => 'ORDER BY COALESCE(p.sale_price, p.price) ASC',
            'price_desc' => 'ORDER BY COALESCE(p.sale_price, p.price) DESC',
            'newest' => 'ORDER BY p.created_at DESC',
            'name' => 'ORDER BY p.name ASC'
        ];
        $orderBy = $sortMap[$sort] ?? $sortMap['newest'];

        $baseJoin = "FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN (SELECT product_id, AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews GROUP BY product_id) r ON r.product_id = p.id";

        $countStmt = $db->prepare("SELECT COUNT(*) as total $baseJoin WHERE $where");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        $offset = ($page - 1) * $limit;
        $stmt = $db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug, COALESCE(r.avg_rating, 0) as avg_rating, COALESCE(r.review_count, 0) as review_count $baseJoin WHERE $where $orderBy LIMIT ? OFFSET ?");
        $params[] = $limit; $params[] = $offset;
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        foreach ($products as &$p) {
            $p['images'] = json_decode($p['images'] ?? '[]', true) ?: [];
            $p['category'] = $p['category_name'] ? ['name' => $p['category_name'], 'slug' => $p['category_slug']] : null;
            unset($p['category_name'], $p['category_slug']);
        }

        sendJSON(['products' => $products, 'total' => $total, 'page' => $page, 'totalPages' => (int)ceil($total / $limit)]);
    } catch (Exception $e) {
        logError("Products list: " . $e->getMessage());
        sendJSON(['error' => 'Ürünler yüklenemedi'], 500);
    }
}
