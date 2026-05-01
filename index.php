<?php
// Root index.php - Front Controller
// Bu dosya .htaccess olmadan da calisir

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// API istekleri
if (strpos($uri, '/api/') === 0 || $uri === '/api') {
    require __DIR__ . '/api/index.php';
    exit;
}

// Frontend (SPA) icin index.html
if ($uri === '/' || !file_exists(__DIR__ . $uri)) {
    readfile(__DIR__ . '/index.html');
    exit;
}

// Statik dosyalar (assets, images vb.)
readfile(__DIR__ . $uri);
