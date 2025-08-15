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

$username = trim($input['username'] ?? '');
$password = (string)($input['password'] ?? '');
if ($username === '' || $password === '') {
  http_response_code(400);
  echo json_encode(['status'=>'error','message'=>'กรุณากรอก Username และ Password']);
  exit;
}

$stmt = $pdo->prepare("SELECT id, username, password, full_name, role FROM users WHERE username=:uname LIMIT 1");
$stmt->execute([':uname' => $username]);
$user = $stmt->fetch();

if (!$user) {
  http_response_code(401);
  echo json_encode(['status'=>'error','message'=>'ไม่พบผู้ใช้นี้ในระบบ']);
  exit;
}

if (!password_verify($password, $user['password'])) {
  http_response_code(401);
  echo json_encode(['status'=>'error','message'=>'รหัสผ่านไม่ถูกต้อง']);
  exit;
}

$_SESSION['user'] = [
  'id'        => $user['id'],
  'username'  => $user['username'],
  'full_name' => $user['full_name'] ?: $user['username'],
  'role'      => $user['role'] ?: 'user'
];

echo json_encode(['status'=>'success','user'=>$_SESSION['user'],'message'=>'เข้าสู่ระบบสำเร็จ']);
