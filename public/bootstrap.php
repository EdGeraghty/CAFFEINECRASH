<?php
// Bootstrap file
require_once __DIR__ . '/../vendor/autoload.php';

// Define project root
define('PROJECT_ROOT', realpath(__DIR__ . '/..'));

// Load environment variables
$envFile = PROJECT_ROOT . '/.env';
if (!file_exists($envFile)) {
    $envFile = PROJECT_ROOT . '/.env.example';
}

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Start session
session_save_path(PROJECT_ROOT . '/sessions');
if (!is_dir(session_save_path())) {
    mkdir(session_save_path(), 0755, true);
}
session_name($_ENV['SESSION_NAME'] ?? 'CAFFEINECRASH_SESSION');
session_start();

// Set absolute database path if not already set to an absolute path
if (empty($_ENV['DB_PATH']) || !str_starts_with($_ENV['DB_PATH'], '/')) {
    $_ENV['DB_PATH'] = PROJECT_ROOT . '/public/data/caffeinecrash.db';
}

// Helper functions
function redirect(string $path): void {
    header("Location: $path");
    exit;
}

function json_response(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function require_login(): void {
    $auth = new \App\Auth();
    if (!$auth->isLoggedIn()) {
        redirect('/login.php');
    }
}

function require_admin(): void {
    $auth = new \App\Auth();
    if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
        redirect('/dashboard.php');
    }
    if (!$auth->isTOTPVerified()) {
        redirect('/admin/totp-verify.php');
    }
}

function sanitize(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
