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

$full_name = trim($input['full_name'] ?? '');
$email = trim($input['email'] ?? '');
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';
$role = $input['role'] ?? '';
$student_id = trim($input['student_id'] ?? '');
$group_name_raw = $input['group_name'] ?? '';
$group_name = trim((string)$group_name_raw);


// --- Validation ---
if (empty($full_name) || empty($email) || empty($username) || empty($password) || empty($role)) {
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
  exit;
}

if (!preg_match('/^[\p{L}\p{M}\s\'\-,.]+$/u', $full_name)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ชื่อ-นามสกุลไม่สามารถมีอักขระพิเศษที่ไม่ได้รับอนุญาตได้']);
    exit;
}

if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ชื่อผู้ใช้งานต้องเป็นภาษาอังกฤษและตัวเลขเท่านั้น']);
    exit;
}

if ($role === 'student') {
    if (empty($student_id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Student ID is required for student role']);
        exit;
    }
    if (!preg_match('/^[0-9]{10}$/', $student_id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Student ID must be 10 digits.']);
        exit;
    }
    if (!empty($group_name) && !preg_match('/^[\p{Thai}a-zA-Z0-9\s\-\_\.\(\)&]+$/u', $group_name)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ชื่อกลุ่มสามารถใช้ภาษาไทย, อังกฤษ, ตัวเลข และอักขระพิเศษบางตัว (- _ . ( ) &) ได้เท่านั้น']);
    exit;
}

}

if (strlen($password) < 6 || strlen($password) > 10) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Password must be 6-10 characters long.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'รูปแบบอีเมลไม่ถูกต้อง']);
  exit;
}

try {
    $pdo->beginTransaction();

    // Check for duplicate email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('อีเมลนี้มีผู้ใช้งานแล้ว', 409);
    }

    // Check for duplicate username
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        throw new Exception('ชื่อผู้ใช้งานนี้มีผู้ใช้งานแล้ว', 409);
    }

    // If student, check for duplicate student_id in students table
    if ($role === 'student') {
        $stmt = $pdo->prepare("SELECT user_id FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        if ($stmt->fetch()) {
            throw new Exception('รหัสนิสิตนี้มีผู้ใช้งานแล้ว', 409);
        }
    }

    // Insert into users table
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, username, password, role, profile_image_url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$full_name, $email, $username, $hashed_password, $role, '']);
    $user_id = $pdo->lastInsertId();

    // If student, insert into students table
    if ($role === 'student') {
        $stmt = $pdo->prepare("INSERT INTO students (student_id, user_id, group_name) VALUES (?, ?, ?)");
        $stmt->execute([$student_id, $user_id, $group_name]);
    }

    $pdo->commit();

    ob_clean();
    echo json_encode(['status' => 'success', 'message' => 'User added successfully']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $code = $e->getCode() >= 400 ? $e->getCode() : 500;
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
