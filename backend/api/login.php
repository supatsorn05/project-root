<?php
ob_start();

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
    'http://localhost:5174',
    'http://127.0.0.1:5174'
];
if (in_array($origin, $allowed, true)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    ob_end_flush();
    exit;
}

session_start();
require __DIR__ . '/../../db.php';

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'JSON ไม่ถูกต้อง']);
    ob_end_flush();
    exit;
}

$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'กรุณากรอก Username และ Password']);
    ob_end_flush();
    exit;
}

try {
    if (!isset($pdo)) {
        throw new Exception("No DB connection");
    }

    $stmt = $pdo->prepare("SELECT id, username, password, fullname, role FROM users WHERE username = :uname LIMIT 1");
    $stmt->execute([':uname' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401);
        echo json_encode(['status'=>'error','message'=>'ไม่พบผู้ใช้นี้ในระบบ']);
        ob_end_flush();
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['status'=>'error','message'=>'รหัสผ่านไม่ถูกต้อง']);
        ob_end_flush();
        exit;
    }

    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'full_name' => $user['fullname'] ?: $user['username'],
        'role' => $user['role'] ?: 'user'
    ];

    echo json_encode([
        'status'=>'success',
        'user'=>$_SESSION['user'],
        'message'=>'เข้าสู่ระบบสำเร็จ'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'เกิดข้อผิดพลาดของระบบ']);
}

ob_end_flush();
