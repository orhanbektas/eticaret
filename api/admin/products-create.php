<?php
/**
 * POST /api/admin/products
 */

require_once __DIR__ . '/../config.php';

function apiAdminProductsCreate() {
    try {
        adminAuth();
        $db = getDB();
        $body = getBody();

        $name = trim($body['name'] ?? '');
        if (!$name) {
            sendJSON(['error' => 'Urun adi gerekli'], 400);
        }

        $slug = !empty($body['slug']) ? trim($body['slug']) : makeSlug($name);
        $shortDesc = trim($body['short_desc'] ?? '');
        $description = trim($body['description'] ?? '');
        $price = (float)($body['price'] ?? 0);
        $salePrice = isset($body['sale_price']) ? (float)$body['sale_price'] : null;
        $stock = (int)($body['stock'] ?? 0);
        $sku = trim($body['sku'] ?? '');
        $categoryId = isset($body['category_id']) ? (int)$body['category_id'] : null;
        $images = $body['images'] ?? [];
        $videoUrl = trim($body['video_url'] ?? '');
        $featured = isset($body['featured']) ? (int)$body['featured'] : 0;
        $bestSeller = isset($body['best_seller']) ? (int)$body['best_seller'] : 0;
        $newProduct = isset($body['new_product']) ? (int)$body['new_product'] : 0;
        $variants = $body['variants'] ?? null;
        $weight = isset($body['weight']) ? (float)$body['weight'] : null;
        $dimensions = $body['dimensions'] ?? null;
        $colorCode = trim($body['color_code'] ?? '');
        $material = trim($body['material'] ?? '');
        $warrantyPeriod = trim($body['warranty_period'] ?? '');

        $stmt = $db->prepare("
            INSERT INTO products (
                name, slug, short_desc, description, price, sale_price, stock, sku,
                category_id, images, video_url, featured, best_seller, new_product, active,
                variants, weight, dimensions, color_code, material, warranty_period, created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, 1,
                ?, ?, ?, ?, ?, ?, NOW()
            )
        ");
        $stmt->execute([
            $name, $slug, $shortDesc, $description, $price, $salePrice, $stock, $sku,
            $categoryId,
            is_array($images) ? json_encode($images, JSON_UNESCAPED_UNICODE) : $images,
            $videoUrl,
            $featured, $bestSeller, $newProduct,
            $variants ? (is_array($variants) ? json_encode($variants, JSON_UNESCAPED_UNICODE) : $variants) : null,
            $weight,
            $dimensions ? (is_array($dimensions) ? json_encode($dimensions, JSON_UNESCAPED_UNICODE) : $dimensions) : null,
            $colorCode, $material, $warrantyPeriod
        ]);

        $id = $db->lastInsertId();

        sendJSON([
            'message' => 'Urun olusturuldu',
            'id' => (int)$id
        ], 201);
    } catch (PDOException $e) {
        logError("Product create error: " . $e->getMessage());
        sendJSON(['error' => 'Urun olusturulamadi'], 500);
    } catch (Exception $e) {
        logError("Product create error: " . $e->getMessage());
        sendJSON(['error' => 'Sunucu hatasi'], 500);
    }
}
