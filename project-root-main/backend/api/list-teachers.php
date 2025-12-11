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

// Authenticate user
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

if (preg_match('/Bearer\s(\S+)/i', $auth_header, $matches)) {
    $token = $matches[1];
    $token_hash = hash('sha256', $token);

    $stmt = $pdo->prepare("SELECT role FROM users WHERE session_token = :token_hash");
    $stmt->execute(['token_hash' => $token_hash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Invalid session token.']);
        exit;
    }
} else {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Token not provided']);
    exit;
}

$sql = "SELECT id, full_name FROM users WHERE role = 'teacher' ORDER BY full_name";
$stmt = $pdo->query($sql);
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($teachers);
?>