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

// 1. Authenticate user and get student_id
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$student_id = null;

if (preg_match('/Bearer\s(\S+)/i', $auth_header, $matches)) {
    $token = $matches[1];
    $token_hash = hash('sha256', $token);

    $stmt = $pdo->prepare("SELECT u.id, u.role, s.student_id FROM users u LEFT JOIN students s ON u.id = s.user_id WHERE u.session_token = :token_hash");
    $stmt->execute(['token_hash' => $token_hash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Invalid session token.']);
        exit;
    }

    if ($user['role'] !== 'student') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Only users with the student role can upload documents.']);
        exit;
    }

    if (!$user['student_id']) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Your student account is not linked to a Student ID. Please contact an administrator.']);
        exit;
    }
    $student_id = $user['student_id'];
} else {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Token not provided']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

// 2. Get parameters from POST request
$teacher_user_id = $_POST['teacher_user_id'] ?? null;
$doc_type_id = $_POST['doc_type_id'] ?? null;

if (!$teacher_user_id || !$doc_type_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Teacher ID and Document Type ID are required.']);
    exit;
}

// 3. Handle file upload
$upload_dir = __DIR__ . '/../uploads/documents/'; // A more general folder
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file_extension !== 'pdf') {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Only PDF files are allowed.']);
        exit;
    }

    $new_file_name = 'student_' . $student_id . '_' . uniqid() . '.pdf';
    $target_file = $upload_dir . $new_file_name;
    $relative_file_path = 'uploads/documents/' . $new_file_name; // Path to store in DB

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        try {
            // 4. Insert into the new student_documents table
            $sql = "INSERT INTO student_documents (student_id, teacher_user_id, doc_type_id, file_path, status) VALUES (:student_id, :teacher_user_id, :doc_type_id, :file_path, 'submitted')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':student_id' => $student_id,
                ':teacher_user_id' => $teacher_user_id,
                ':doc_type_id' => $doc_type_id,
                ':file_path' => $relative_file_path
            ]);

            echo json_encode(['status' => 'success', 'message' => 'File uploaded successfully', 'file_path' => $relative_file_path]);
        } catch (PDOException $e) {
            http_response_code(500);
            // In case of error, delete the uploaded file
            if (file_exists($target_file)) {
                unlink($target_file);
            }
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file']);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No file was uploaded']);
}
?>