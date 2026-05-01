<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// JSON response
function sendJSON($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Error logging
function logError($message) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $line = "[$timestamp] $message\n";
    file_put_contents($logDir . '/error.log', $line, FILE_APPEND);
}

function envValue($key, $default = null) {
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }
    return $value;
}

function requireEnv($key) {
    $value = envValue($key);
    if ($value === null || $value === '') {
        logError("Missing required environment variable: " . $key);
        sendJSON(['error' => 'Sunucu konfigrasyonu eksik'], 500);
    }
    return $value;
}

// Database / auth configuration
define('DB_HOST', envValue('DB_HOST', 'localhost'));
define('DB_USER', requireEnv('DB_USER'));
define('DB_PASS', requireEnv('DB_PASSWORD'));
define('DB_NAME', requireEnv('DB_NAME'));
define('JWT_SECRET', requireEnv('JWT_SECRET'));

// PDO Connection
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";port=3306;dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            logError("DB Connection failed: " . $e->getMessage());
            sendJSON(['error' => 'Veritabani baglanti hatasi'], 500);
        }
    }
    return $pdo;
}

// Schema helper for backward-compatible queries on older databases
function dbHasColumn($db, $table, $column) {
    static $cache = [];
    $key = $table . '.' . $column;
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    $stmt = $db->prepare("
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
        LIMIT 1
    ");
    $stmt->execute([$table, $column]);
    $cache[$key] = (bool)$stmt->fetchColumn();
    return $cache[$key];
}

// Get request body
function getBody() {
    static $body = null;
    if ($body === null) {
        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true) ?: [];
    }
    return $body;
}

// Get query parameter
function getParam($key, $default = null) {
    return $_GET[$key] ?? $default;
}

// JWT functions
function signToken($user) {
    $payload = [
        'id' => (int)$user['id'],
        'email' => $user['email'],
        'role' => $user['role'],
        'iat' => time(),
        'exp' => time() + 7 * 24 * 60 * 60
    ];
    $header = base64url_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
    $payloadEnc = base64url_encode(json_encode($payload));
    $signature = base64url_encode(hash_hmac('sha256', "$header.$payloadEnc", JWT_SECRET, true));
    return "$header.$payloadEnc.$signature";
}

function verifyToken() {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!$header || !preg_match('/^Bearer\s+(.+)$/', $header, $matches)) {
        sendJSON(['error' => 'Token gerekli'], 401);
    }

    $token = $matches[1];
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        sendJSON(['error' => 'Gecersiz token'], 401);
    }

    $signature = base64url_encode(hash_hmac('sha256', "$parts[0].$parts[1]", JWT_SECRET, true));
    if (!hash_equals($signature, $parts[2])) {
        sendJSON(['error' => 'Gecersiz token'], 401);
    }

    $payload = json_decode(base64url_decode($parts[1]), true);
    if (!$payload || ($payload['exp'] ?? 0) < time()) {
        sendJSON(['error' => 'Token suresi dolmus'], 401);
    }

    return $payload;
}

function auth() {
    return verifyToken();
}

function adminAuth() {
    $user = verifyToken();
    if (($user['role'] ?? '') !== 'admin') {
        sendJSON(['error' => 'Admin yetkisi gerekli'], 403);
    }
    return $user;
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

// Password hashing
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Slug generator
function makeSlug($text) {
    $search = ['ÄŸ', 'Ã¼', 'ÅŸ', 'Ä±', 'Ã¶', 'Ã§', 'Ä', 'Ãœ', 'Å', 'Ä°', 'Ã–', 'Ã‡'];
    $replace = ['g', 'u', 's', 'i', 'o', 'c', 'g', 'u', 's', 'i', 'o', 'c'];
    $text = str_replace($search, $replace, $text);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}
