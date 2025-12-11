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

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

if (preg_match('/Bearer\s(.+)/', $authHeader, $matches)) {
    $token = $matches[1];
    error_log("ME: Received token: " . $token);
    $token_hash = hash('sha256', $token);
    error_log("ME: Hashed token: " . $token_hash);

    $stmt = $pdo->prepare("SELECT u.id, u.email, u.username, u.full_name, u.role, u.profile_image_url, s.student_id FROM users u LEFT JOIN students s ON u.id = s.user_id WHERE u.session_token = :token_hash LIMIT 1");
    $stmt->execute([':token_hash' => $token_hash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        error_log("ME: User found: " . print_r($user, true));
    }

    if ($user) {
        echo json_encode(['status' => 'success', 'user' => $user]);
    } else {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid token']);
    }
} else {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Authentication token required']);
}
exit;
