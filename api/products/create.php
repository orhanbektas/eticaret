<?php
require_once __DIR__ . '/../config.php';

function apiProductsCreate() {
    try {
        adminAuth();

        $db = getDB();
        $body = getBody();

        $name = trim($body['name'] ?? '');
        $description = $body['description'] ?? '';
        $shortDesc = $body['short_desc'] ?? '';
        $price = $body['price'] ?? null;
        $salePrice = $body['sale_price'] ?? null;
        $stock = $body['stock'] ?? 0;
        $sku = $body['sku'] ?? null;
        $categoryId = $body['category_id'] ?? null;

        if ($name === '') {
            sendJSON(['error' => 'Urun adi gerekli'], 400);
        }
        if ($price === null || $price === '') {
            sendJSON(['error' => 'Fiyat gerekli'], 400);
        }
        if (!$categoryId) {
            sendJSON(['error' => 'Kategori gerekli'], 400);
        }

        $slug = makeSlug($name);
        $checkStmt = $db->prepare("SELECT id FROM products WHERE slug = ?");
        $checkStmt->execute([$slug]);
        if ($checkStmt->fetch()) {
            $slug = $slug . '-' . time();
        }

        $images = isset($body['images']) ? (is_array($body['images']) ? json_encode($body['images'], JSON_UNESCAPED_UNICODE) : $body['images']) : '[]';
        $videoUrl = $body['video_url'] ?? null;
        $featured = isset($body['featured']) ? (int)$body['featured'] : 0;
        $bestSeller = isset($body['best_seller']) ? (int)$body['best_seller'] : 0;
        $newProduct = isset($body['new_product']) ? (int)$body['new_product'] : 0;
        $variants = isset($body['variants']) ? (is_array($body['variants']) ? json_encode($body['variants'], JSON_UNESCAPED_UNICODE) : $body['variants']) : '[]';
        $weight = $body['weight'] ?? null;
        $dimensions = isset($body['dimensions']) ? (is_array($body['dimensions']) ? json_encode($body['dimensions'], JSON_UNESCAPED_UNICODE) : $body['dimensions']) : null;
        $colorCode = $body['color_code'] ?? null;
        $material = $body['material'] ?? null;
        $warrantyPeriod = $body['warranty_period'] ?? null;
        $active = isset($body['active']) ? (int)$body['active'] : 1;

        $sql = "INSERT INTO products (
                    name, slug, description, short_desc, price, sale_price,
                    stock, sku, category_id, images, video_url, featured,
                    best_seller, new_product, variants, weight, dimensions,
                    color_code, material, warranty_period, active, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            $name,
            $slug,
            $description,
            $shortDesc,
            (float)$price,
            ($salePrice === null || $salePrice === '') ? null : (float)$salePrice,
            (int)$stock,
            $sku,
            (int)$categoryId,
            $images,
            $videoUrl,
            $featured,
            $bestSeller,
            $newProduct,
            $variants,
            $weight,
            $dimensions,
            $colorCode,
            $material,
            $warrantyPeriod,
            $active
        ]);

        $productId = (int)$db->lastInsertId();
        $fetchStmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $fetchStmt->execute([$productId]);
        $product = $fetchStmt->fetch();

        $product['images'] = json_decode($product['images'] ?? '[]', true) ?: [];
        $product['variants'] = json_decode($product['variants'] ?? '[]', true) ?: [];

        sendJSON([
            'message' => 'Urun basariyla olusturuldu',
            'product' => $product
        ], 201);
    } catch (Exception $e) {
        logError("Product create error: " . $e->getMessage());
        sendJSON(['error' => 'Urun olusturulurken hata olustu'], 500);
    }
}
