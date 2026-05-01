<?php
// DELETE /api/admin/posts/{id}

function apiPostsDelete($id) {
    try {
        adminAuth();
        $db = getDB();

        $id = (int)$id;
        if (!$id) {
            sendJSON(['error' => 'Geçersiz yazı ID'], 400);
        }

        $stmt = $db->prepare("SELECT id FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            sendJSON(['error' => 'Yazı bulunamadı'], 404);
        }

        $stmt = $db->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);

        sendJSON(['message' => 'Yazı silindi']);
    } catch (PDOException $e) {
        logError("Post delete error: " . $e->getMessage());
        sendJSON(['error' => 'Yazı silinemedi'], 500);
    } catch (Exception $e) {
        logError("Post delete error: " . $e->getMessage());
        sendJSON(['error' => 'Sunucu hatası'], 500);
    }
}
