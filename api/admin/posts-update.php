<?php
/**
 * PUT /api/admin/posts/{id}
 */

require_once __DIR__ . '/../config.php';

function apiPostsUpdate($id) {
    try {
        adminAuth();
        $db = getDB();
        $body = getBody();

        $id = (int)$id;
        if (!$id) {
            sendJSON(['error' => 'Gecersiz yazi ID'], 400);
        }

        $stmt = $db->prepare("SELECT id FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch();
        if (!$post) {
            sendJSON(['error' => 'Yazi bulunamadi'], 404);
        }

        $fields = [];
        $values = [];

        $allowedFields = ['title', 'excerpt', 'content', 'category', 'image', 'author'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $body)) {
                $fields[] = "$field = ?";
                $values[] = $body[$field];
            }
        }

        if (isset($body['slug']) && $body['slug'] !== '') {
            $fields[] = "slug = ?";
            $values[] = makeSlug($body['slug']);
        } elseif (isset($body['title']) && $body['title'] !== '') {
            $fields[] = "slug = ?";
            $values[] = makeSlug($body['title']);
        }

        if (empty($fields)) {
            sendJSON(['error' => 'Guncellenecek alan bulunamadi'], 400);
        }

        $values[] = $id;
        $sql = "UPDATE blog_posts SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($values);

        sendJSON(['message' => 'Yazi guncellendi']);
    } catch (PDOException $e) {
        logError("Post update error: " . $e->getMessage());
        sendJSON(['error' => 'Yazi guncellenemedi'], 500);
    } catch (Exception $e) {
        logError("Post update error: " . $e->getMessage());
        sendJSON(['error' => 'Sunucu hatasi'], 500);
    }
}
