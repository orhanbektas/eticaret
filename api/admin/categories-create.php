<?php
// POST /api/admin/categories

function apiCategoriesCreate() {
    try {
        adminAuth();
        $db = getDB();
        $body = getBody();

        $name = trim($body['name'] ?? '');
        if (!$name) {
            sendJSON(['error' => 'Kategori adı gerekli'], 400);
        }

        $slug = !empty($body['slug']) ? trim($body['slug']) : makeSlug($name);
        $description = trim($body['description'] ?? '');
        $image = trim($body['image'] ?? '');

        if (dbHasColumn($db, 'categories', 'description')) {
            $stmt = $db->prepare("INSERT INTO categories (name, slug, description, image) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $description, $image]);
        } else {
            $stmt = $db->prepare("INSERT INTO categories (name, slug, image) VALUES (?, ?, ?)");
            $stmt->execute([$name, $slug, $image]);
        }

        $id = $db->lastInsertId();

        sendJSON([
            'message' => 'Kategori oluşturuldu',
            'id' => (int)$id
        ], 201);
    } catch (PDOException $e) {
        logError("Category create error: " . $e->getMessage());
        sendJSON(['error' => 'Kategori oluşturulamadı'], 500);
    } catch (Exception $e) {
        logError("Category create error: " . $e->getMessage());
        sendJSON(['error' => 'Sunucu hatası'], 500);
    }
}
