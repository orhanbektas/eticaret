<?php
/**
 * GET /api/settings/menu - Get menu items
 * PUT /api/settings/menu - Update menu items (admin only)
 */

require_once __DIR__ . '/../config.php';

function apiMenuGet() {
    try {
        $db = getDB();

        $stmt = $db->prepare("SELECT menu_items FROM site_settings WHERE id = 1");
        $stmt->execute();
        $settings = $stmt->fetch();

        $menuItems = [];

        if ($settings && !empty($settings['menu_items'])) {
            $decoded = json_decode($settings['menu_items'], true);
            if (is_array($decoded)) {
                $menuItems = $decoded;
            }
        }

        sendJSON([
            'menu_items' => $menuItems
        ]);
    } catch (Exception $e) {
        logError('Menu get: ' . $e->getMessage());
        sendJSON(['error' => 'Menu alinamadi'], 500);
    }
}

function apiMenuUpdate() {
    try {
        $user = adminAuth();
        $body = getBody();
        $menuItems = $body['menu_items'] ?? [];

        if (!is_array($menuItems)) {
            sendJSON(['error' => 'Menu ogeleri dizi olmalidir'], 400);
        }

        $db = getDB();
        $menuJson = json_encode($menuItems, JSON_UNESCAPED_UNICODE);

        $stmt = $db->prepare("SELECT id FROM site_settings WHERE id = 1");
        $stmt->execute();
        $exists = $stmt->fetch();

        if (!$exists) {
            $stmt = $db->prepare("INSERT INTO site_settings (id, menu_items) VALUES (1, ?)");
            $stmt->execute([$menuJson]);
        } else {
            $stmt = $db->prepare("UPDATE site_settings SET menu_items = ? WHERE id = 1");
            $stmt->execute([$menuJson]);
        }

        sendJSON([
            'message' => 'Menu guncellendi',
            'menu_items' => $menuItems
        ]);
    } catch (Exception $e) {
        logError('Menu update: ' . $e->getMessage());
        sendJSON(['error' => 'Menu guncellenemedi'], 500);
    }
}
