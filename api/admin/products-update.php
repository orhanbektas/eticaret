<?php
/**
 * PUT /api/admin/products/{id}
 */

require_once __DIR__ . '/../config.php';

function apiAdminProductsUpdate($id) {
    try {
        adminAuth();
        $db = getDB();
        $body = getBody();

        $id = (int)$id;
        if (!$id) {
            sendJSON(['error' => 'Gecersiz urun ID'], 400);
        }

        $stmt = $db->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if (!$product) {
            sendJSON(['error' => 'Urun bulunamadi'], 404);
        }

        $fields = [];
        $values = [];

        $allowedFields = [
            'name', 'slug', 'short_desc', 'description', 'price', 'sale_price',
            'stock', 'sku', 'category_id', 'video_url',
            'featured', 'best_seller', 'new_product', 'active',
            'weight', 'color_code', 'material', 'warranty_period'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $body)) {
                $fields[] = "$field = ?";
                $values[] = $body[$field];
            }
        }

        if (isset($body['name']) && !empty($body['name'])) {
            $slug = !empty($body['slug']) ? trim($body['slug']) : makeSlug($body['name']);
            $fields[] = "slug = ?";
            $values[] = $slug;
        }

        if (array_key_exists('images', $body)) {
            $images = $body['images'];
            $fields[] = "images = ?";
            $values[] = is_array($images) ? json_encode($images, JSON_UNESCAPED_UNICODE) : $images;
        }

        if (array_key_exists('variants', $body)) {
            $variants = $body['variants'];
            $fields[] = "variants = ?";
            $values[] = is_array($variants) ? json_encode($variants, JSON_UNESCAPED_UNICODE) : $variants;
        }

        if (array_key_exists('dimensions', $body)) {
            $dimensions = $body['dimensions'];
            $fields[] = "dimensions = ?";
            $values[] = is_array($dimensions) ? json_encode($dimensions, JSON_UNESCAPED_UNICODE) : $dimensions;
        }

        if (empty($fields)) {
            sendJSON(['error' => 'Guncellenecek alan bulunamadi'], 400);
        }

        $values[] = $id;
        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($values);

        sendJSON(['message' => 'Urun guncellendi']);
    } catch (PDOException $e) {
        logError("Product update error: " . $e->getMessage());
        sendJSON(['error' => 'Urun guncellenemedi'], 500);
    } catch (Exception $e) {
        logError("Product update error: " . $e->getMessage());
        sendJSON(['error' => 'Sunucu hatasi'], 500);
    }
}
