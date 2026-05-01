<?php
/**
 * POST /api/upload - Image upload
 */

require_once __DIR__ . '/config.php';

function apiUpload() {
    try {
        adminAuth();

        // Allowed extensions
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = 10 * 1024 * 1024; // 10MB

        if (!isset($_FILES['image'])) {
            sendJSON(['error' => 'Dosya seçilmedi'], 400);
        }

        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            sendJSON(['error' => 'Yükleme hatası: ' . $file['error']], 400);
        }

        if ($file['size'] > $maxSize) {
            sendJSON(['error' => 'Dosya çok büyük (max 10MB)'], 400);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            sendJSON(['error' => 'İzin verilmeyen dosya türü'], 400);
        }

        // Create uploads directory
        $uploadDir = __DIR__ . '/../uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $filename = uniqid('img_') . '_' . time() . '.' . $ext;
        $filepath = $uploadDir . '/' . $filename;
        $publicPath = '/uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            sendJSON(['error' => 'Dosya kaydedilemedi'], 500);
        }

        sendJSON(['url' => $publicPath, 'filename' => $filename]);
    } catch (Exception $e) {
        logError("Upload error: " . $e->getMessage());
        sendJSON(['error' => 'Yükleme başarısız'], 500);
    }
}
