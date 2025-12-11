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

$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
  exit;
}

$identifier = trim((string)($input['email'] ?? $input['username'] ?? ''));
$password   = (string)($input['password'] ?? '');

if ($identifier === '' || $password === '') {
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกอีเมล/ชื่อผู้ใช้ และรหัสผ่าน']);
  exit;
}

$isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
$sql = $isEmail
  ? "SELECT id, email, username, password, full_name, role, profile_image_url FROM users WHERE email = :id LIMIT 1"
  : "SELECT id, email, username, password, full_name, role, profile_image_url FROM users WHERE username = :id LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $identifier]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password'])) {
  http_response_code(401);
  echo json_encode(['status' => 'error', 'message' => 'ข้อมูลเข้าสู่ระบบไม่ถูกต้อง']);
  exit;
}

// Generate and store token
$token = bin2hex(random_bytes(32));
error_log("LOGIN: Generated token: " . $token);
$token_hash = hash('sha256', $token);
error_log("LOGIN: Stored hash: " . $token_hash);

$updateStmt = $pdo->prepare("UPDATE users SET session_token = :token_hash WHERE id = :user_id");
$updateStmt->execute([
    ':token_hash' => $token_hash,
    ':user_id' => $user['id']
]);

error_log("LOGIN: User ID: " . $user['id'] . ", Token Hash updated in DB.");

// Return user and token
unset($user['password']); // Don't send password to client

echo json_encode(['status' => 'success', 'user' => $user, 'token' => $token]);
exit;