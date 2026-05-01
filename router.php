<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/api' || strpos($uri, '/api/') === 0) {
    require __DIR__ . '/api/index.php';
    return true;
}

return false;
