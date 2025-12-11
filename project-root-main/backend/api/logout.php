<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../db_connect.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'No token provided']);
    exit;
}

$authHeader = $_SERVER['HTTP_AUTHORIZATION'];
$parts = explode(' ', $authHeader);

if (count($parts) !== 2 || strtolower($parts[0]) !== 'bearer') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid token format']);
    exit;
}

$token = $parts[1];
$token_hash = hash('sha256', $token);

$stmt = $pdo->prepare("UPDATE users SET session_token = NULL WHERE session_token = :token_hash");
$stmt->execute([':token_hash' => $token_hash]);

echo json_encode(['status' => 'success', 'message' => 'Logged out']);
exit;