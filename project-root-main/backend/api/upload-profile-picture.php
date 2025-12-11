<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../db_connect.php';

header('Content-Type: application/json; charset=utf-8');

// 1. Authenticate user
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$userId = null;

if (preg_match('/Bearer\s(.+)/', $authHeader, $matches)) {
    $token = $matches[1];
    $token_hash = hash('sha256', $token);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE session_token = :token_hash LIMIT 1");
    $stmt->execute([':token_hash' => $token_hash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $userId = $user['id'];
    } else {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid token']);
        exit;
    }
} else {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Authentication token required']);
    exit;
}

// 2. Handle File Upload
if (!isset($_FILES['profile_picture'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded.']);
    exit;
}

$file = $_FILES['profile_picture'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error during file upload.']);
    exit;
}

// 3. Validate File
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($file['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
    exit;
}

if ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'File is too large. 2MB maximum.']);
    exit;
}

// 4. Generate unique name and path
$uploadDir = __DIR__ . '/../uploads/profile_pictures/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFilename = 'user_' . $userId . '_' . time() . '.' . $extension;
$destination = $uploadDir . $newFilename;

// Web-accessible path
$webPath = '/uploads/profile_pictures/' . $newFilename;

// 5. Move file and update database
try {
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Update database
        $updateStmt = $pdo->prepare("UPDATE users SET profile_image_url = :path WHERE id = :user_id");
        $updateStmt->execute([':path' => $webPath, ':user_id' => $userId]);

        echo json_encode(['status' => 'success', 'message' => 'Profile picture updated.', 'url' => $webPath]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to save uploaded file.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log("PDOException in upload-profile-picture.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error while updating profile picture.']);
}

exit;
