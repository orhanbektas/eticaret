<?php
/**
 * GET /api/settings/public
 * Get public site settings
 */

require_once __DIR__ . '/../config.php';

function apiSettingsPublic() {
    try {
        $db = getDB();

        $stmt = $db->prepare("SELECT * FROM site_settings WHERE id = 1");
        $stmt->execute();
        $settings = $stmt->fetch();

        if (!$settings) {
            $settings = [
                'site_name' => 'E-Magaza',
                'logo_text' => 'E',
                'logo_url' => null,
                'phone' => null,
                'email' => null,
                'address' => null,
                'whatsapp' => null,
                'currency' => 'TL',
                'free_shipping_limit' => 500,
                'shipping_cost' => 49.90,
                'bank_accounts' => [],
                'social' => [],
                'meta_title' => 'E-Magaza - Online Alisveris',
                'meta_description' => 'Turkiye\'nin en iyi online alisveris sitesi'
            ];
        } else {
            $settings['social'] = json_decode($settings['social'] ?? '{}', true) ?: [];
            $settings['bank_accounts'] = json_decode($settings['bank_accounts'] ?? '[]', true) ?: [];
        }

        $settings['free_shipping_limit'] = (float)($settings['free_shipping_limit'] ?? 500);
        $settings['shipping_cost'] = (float)($settings['shipping_cost'] ?? 49.90);

        $response = [
            'site_name' => $settings['site_name'] ?? 'E-Magaza',
            'logo_text' => $settings['logo_text'] ?? 'E',
            'logo_url' => $settings['logo_url'] ?? null,
            'phone' => $settings['phone'] ?? null,
            'email' => $settings['email'] ?? null,
            'address' => $settings['address'] ?? null,
            'whatsapp' => $settings['whatsapp'] ?? null,
            'currency' => $settings['currency'] ?? 'TL',
            'free_shipping_limit' => $settings['free_shipping_limit'],
            'shipping_cost' => $settings['shipping_cost'],
            'bank_accounts' => is_array($settings['bank_accounts'] ?? null) ? $settings['bank_accounts'] : [],
            'social' => is_array($settings['social'] ?? null) ? $settings['social'] : [],
            'meta_title' => $settings['meta_title'] ?? '',
            'meta_description' => $settings['meta_description'] ?? ''
        ];

        sendJSON($response);

    } catch (Exception $e) {
        logError('Settings public: ' . $e->getMessage());
        sendJSON(['error' => 'Ayarlar alinamadi'], 500);
    }
}
