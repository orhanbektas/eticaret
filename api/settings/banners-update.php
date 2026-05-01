<?php
/**
 * PUT /api/settings/banners/{id}
 * Update a slider banner (admin only)
 */

require_once __DIR__ . '/../config.php';

function apiBannersUpdate($id) {
    try {
        // Authenticate admin
        $user = adminAuth();

        // Get banner ID from parameter
        $id = (int)$id;

        if ($id <= 0) {
            sendJSON(['error' => 'Geçersiz ID'], 400);
        }

        $body = getBody();

        // Allowed fields
        $allowedFields = ['title', 'subtitle', 'cta', 'gradient', 'image', 'link', 'active', 'order'];

        // Filter and prepare data
        $data = [];
        foreach ($allowedFields as $field) {
            if (isset($body[$field])) {
                $value = $body[$field];

                // Convert boolean for active
                if ($field === 'active') {
                    $value = $value ? 1 : 0;
                }

                // Convert integer for order
                if ($field === 'order') {
                    $value = (int)$value;
                }

                $data[$field] = $value;
            }
        }

        if (empty($data)) {
            sendJSON(['error' => 'Güncellenecek alan yok'], 400);
        }

        $db = getDB();

        // Check if banner exists
        $stmt = $db->prepare("SELECT id FROM slider_banners WHERE id = ?");
        $stmt->execute([$id]);

        if (!$stmt->fetch()) {
            sendJSON(['error' => 'Banner bulunamadı'], 404);
        }

        // Build update query
        $setParts = [];
        foreach (array_keys($data) as $field) {
            $setParts[] = "`$field` = :$field";
        }

        $data['id'] = $id;
        $sql = "UPDATE slider_banners SET " . implode(', ', $setParts) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute($data);

        // Fetch updated banner
        $stmt = $db->prepare("SELECT * FROM slider_banners WHERE id = ?");
        $stmt->execute([$id]);
        $banner = $stmt->fetch();

        sendJSON([
            'message' => 'Banner güncellendi',
            'banner' => $banner
        ]);

    } catch (PDOException $e) {
        logError('Banners update: ' . $e->getMessage());
        sendJSON(['error' => 'Banner güncellenemedi'], 500);
    } catch (Exception $e) {
        logError('Banners update: ' . $e->getMessage());
        sendJSON(['error' => 'Banner guncellenemedi'], 500);
    }
}
