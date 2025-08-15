<?php
session_start();
require_once __DIR__ . '/../db_connect.php';
header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
  http_response_code(400);
  echo json_encode(['status'=>'error','message'=>'Invalid JSON']);
  exit;
}

$identifier = trim($input['email'] ?? $input['username'] ?? '');
$password   = (string)($input['password'] ?? '');

if ($identifier === '' || $password === '') {
  http_response_code(400);
  echo json_encode(['status'=>'error','message'=>'กรุณากรอกอีเมล/ชื่อผู้ใช้ และรหัสผ่าน']);
  exit;
}

$isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
$sql = $isEmail
  ? "SELECT id, email, username, password, full_name, role FROM users WHERE email = :id LIMIT 1"
  : "SELECT id, email, username, password, full_name, role FROM users WHERE username = :id LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $identifier]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
  http_response_code(401);
  echo json_encode(['status'=>'error','message'=>'ข้อมูลเข้าสู่ระบบไม่ถูกต้อง']);
  exit;
}

$_SESSION['user'] = [
  'id'        => $user['id'],
  'email'     => $user['email'],
  'username'  => $user['username'],
  'full_name' => $user['full_name'],
  'role'      => $user['role']
];

echo json_encode(['status'=>'success','user'=>$_SESSION['user']]);
