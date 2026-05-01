<?php
require_once __DIR__ . '/../config.php';

function apiProductsUpdate($id) {
    try {
        adminAuth();

        $db = getDB();
        $body = getBody();
        $productId = (int)$id;

        if ($productId <= 0) {
            sendJSON(['error' => 'Urun ID gerekli'], 400);
        }

        $checkStmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $checkStmt->execute([$productId]);
        $existingProduct = $checkStmt->fetch();

        if (!$existingProduct) {
            sendJSON(['error' => 'Urun bulunamadi'], 404);
        }

        $updateFields = [];
        $params = [];

        $allowedFields = [
            'name', 'description', 'short_desc', 'price', 'sale_price',
            'stock', 'sku', 'category_id', 'images', 'video_url',
            'featured', 'best_seller', 'new_product', 'variants',
            'weight', 'dimensions', 'color_code', 'material', 'warranty_period', 'active'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $body)) {
                $value = $body[$field];

                if (in_array($field, ['images', 'variants', 'dimensions'], true) && is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                } elseif (in_array($field, ['price', 'sale_price', 'weight'], true)) {
                    $value = ($value === null || $value === '') ? null : (float)$value;
                } elseif (in_array($field, ['stock', 'category_id', 'featured', 'best_seller', 'new_product', 'active'], true)) {
                    $value = (int)$value;
                }

                $updateFields[] = "$field = ?";
                $params[] = $value;
            }
        }

        if (isset($body['name']) && $body['name'] !== $existingProduct['name']) {
            $newSlug = makeSlug($body['name']);
            $slugCheck = $db->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
            $slugCheck->execute([$newSlug, $productId]);
            if ($slugCheck->fetch()) {
                $newSlug = $newSlug . '-' . time();
            }

            $updateFields[] = "slug = ?";
            $params[] = $newSlug;
        }

        if (empty($updateFields)) {
            sendJSON(['error' => 'Guncellenecek alan bulunamadi'], 400);
        }

        if (dbHasColumn($db, 'products', 'updated_at')) {
            $updateFields[] = "updated_at = NOW()";
        }

        $params[] = $productId;
        $sql = "UPDATE products SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $fetchStmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $fetchStmt->execute([$productId]);
        $product = $fetchStmt->fetch();

        $product['images'] = json_decode($product['images'] ?? '[]', true) ?: [];
        $product['variants'] = json_decode($product['variants'] ?? '[]', true) ?: [];

        sendJSON([
            'message' => 'Urun basariyla guncellendi',
            'product' => $product
        ]);
    } catch (Exception $e) {
        logError("Product update error: " . $e->getMessage());
        sendJSON(['error' => 'Urun guncellenirken hata olustu'], 500);
    }
}
