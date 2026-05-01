<?php
// DELETE /api/admin/categories/{id}

function apiCategoriesDelete($id) {
    try {
        adminAuth();
        $db = getDB();

        $id = (int)$id;
        if (!$id) {
            sendJSON(['error' => 'Geçersiz kategori ID'], 400);
        }

        $stmt = $db->prepare("SELECT id FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            sendJSON(['error' => 'Kategori bulunamadı'], 404);
        }

        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);

        sendJSON(['message' => 'Kategori silindi']);
    } catch (PDOException $e) {
        logError("Category delete error: " . $e->getMessage());
        sendJSON(['error' => 'Kategori silinemedi'], 500);
    } catch (Exception $e) {
        logError("Category delete error: " . $e->getMessage());
        sendJSON(['error' => 'Sunucu hatası'], 500);
    }
}
