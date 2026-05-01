<?php
/**
 * PUT /api/settings
 * Update site settings (admin only)
 */

require_once __DIR__ . '/../config.php';

function apiSettingsUpdate() {
    try {
        $user = adminAuth();
        $body = getBody();

        $allowedFields = [
            'site_name', 'logo_text', 'logo_url', 'favicon', 'phone', 'email',
            'address', 'whatsapp', 'social', 'meta_title', 'meta_description',
            'currency', 'free_shipping_limit', 'shipping_cost', 'bank_accounts',
            'menu_items', 'footer_text', 'copyright_text', 'meta_keywords',
            'paytr_merchant_id', 'paytr_merchant_key', 'paytr_merchant_salt',
            'paytr_test_mode', 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass',
            'smtp_from_name', 'smtp_from_email', 'smtp_secure'
        ];

        $data = [];
        foreach ($allowedFields as $field) {
            if (isset($body[$field])) {
                $value = $body[$field];

                if (in_array($field, ['social', 'bank_accounts', 'menu_items']) && (is_array($value) || is_object($value))) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }

                if (in_array($field, ['free_shipping_limit', 'shipping_cost', 'paytr_test_mode', 'smtp_port'])) {
                    $value = is_numeric($value) ? $value + 0 : $value;
                }

                $data[$field] = $value;
            }
        }

        if (empty($data)) {
            sendJSON(['error' => 'Guncellenecek alan yok'], 400);
        }

        $db = getDB();

        $stmt = $db->prepare("SELECT id FROM site_settings WHERE id = 1");
        $stmt->execute();
        $exists = $stmt->fetch();

        if (!$exists) {
            $data['id'] = 1;
            $fields = array_keys($data);
            $placeholders = array_map(function($f) { return ':' . $f; }, $fields);

            $sql = "INSERT INTO site_settings (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $db->prepare($sql);
            $stmt->execute($data);
        } else {
            $setParts = [];
            foreach (array_keys($data) as $field) {
                $setParts[] = "$field = :$field";
            }

            $sql = "UPDATE site_settings SET " . implode(', ', $setParts) . " WHERE id = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute($data);
        }

        $stmt = $db->prepare("SELECT * FROM site_settings WHERE id = 1");
        $stmt->execute();
        $settings = $stmt->fetch();

        $settings['social'] = json_decode($settings['social'] ?? '{}', true) ?: [];
        $settings['bank_accounts'] = json_decode($settings['bank_accounts'] ?? '[]', true) ?: [];
        $settings['menu_items'] = json_decode($settings['menu_items'] ?? '[]', true) ?: [];

        sendJSON([
            'message' => 'Ayarlar guncellendi',
            'settings' => $settings
        ]);

    } catch (Exception $e) {
        logError('Settings update: ' . $e->getMessage());
        sendJSON(['error' => 'Ayarlar guncellenemedi'], 500);
    }
}
