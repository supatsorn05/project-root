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
    $token_hash = hash('sha256', $token);

    $stmt = $pdo->prepare("SELECT id, role FROM users WHERE session_token = :token_hash LIMIT 1");
    $stmt->execute([':token_hash' => $token_hash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized or not an admin']);
        exit;
    }
} else {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Authentication token required']);
    exit;
}

$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);

$id = $input['id'] ?? '';

if (empty($id)) {
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
  exit;
}

try {
  $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
  $stmt->execute([$id]);

  echo json_encode(['status' => 'success', 'message' => 'User deleted successfully']);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
