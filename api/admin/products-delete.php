<?php
// DELETE /api/admin/products/{id}

function apiAdminProductsDelete($id) {
    try {
        adminAuth();
        $db = getDB();

        $id = (int)$id;
        if (!$id) {
            sendJSON(['error' => 'Geçersiz ürün ID'], 400);
        }

        $stmt = $db->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            sendJSON(['error' => 'Ürün bulunamadı'], 404);
        }

        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        sendJSON(['message' => 'Ürün silindi']);
    } catch (PDOException $e) {
        logError("Product delete error: " . $e->getMessage());
        sendJSON(['error' => 'Ürün silinemedi'], 500);
    } catch (Exception $e) {
        logError("Product delete error: " . $e->getMessage());
        sendJSON(['error' => 'Sunucu hatası'], 500);
    }
}
