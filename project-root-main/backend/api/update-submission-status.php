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

// 1. Authenticate teacher user
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (preg_match('/Bearer\s(\S+)/i', $auth_header, $matches)) {
    $token = $matches[1];
    $token_hash = hash('sha256', $token);
    $stmt = $pdo->prepare("SELECT id FROM users WHERE session_token = :token_hash AND role = 'teacher'");
    $stmt->execute(['token_hash' => $token_hash]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Teacher role required.']);
        exit;
    }
} else {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Token not provided']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// 2. Get and validate input from POST and FILES
$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;
$comment = $_POST['comment'] ?? '';
$teacher_file = $_FILES['teacher_file'] ?? null;
$teacher_file_path = null;

if (!$id || !$status) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data: ID and status are required.']);
    exit;
}

// 3. Handle file upload if present
if ($status === 'approved' && isset($teacher_file) && $teacher_file['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../uploads/teacher_signed/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $file_extension = pathinfo($teacher_file['name'], PATHINFO_EXTENSION);
    $unique_filename = 'teacher_upload_' . uniqid() . '.' . $file_extension;
    $target_path = $upload_dir . $unique_filename;

    if (move_uploaded_file($teacher_file['tmp_name'], $target_path)) {
        // Store relative path for web access
        $teacher_file_path = 'uploads/teacher_signed/' . $unique_filename;
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file.']);
        exit;
    }
}

// 4. Update database
try {
    $params = [
        ':status' => $status,
        ':comment' => $comment,
        ':id' => $id
    ];

    if ($teacher_file_path) {
        $sql = "UPDATE `student_documents` SET `status` = :status, `comment` = :comment, `teacher_file_path` = :teacher_file_path WHERE `student_document_id` = :id";
        $params[':teacher_file_path'] = $teacher_file_path;
    } else {
        $sql = "UPDATE `student_documents` SET `status` = :status, `comment` = :comment WHERE `student_document_id` = :id";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Submission status updated successfully.']);
    } else {
        // If status is approved and a file was uploaded, rowCount can be > 0 even if other fields are the same.
        // If no fields changed, rowCount is 0. We can consider this a success if the file was part of the request.
        if ($teacher_file_path) {
             echo json_encode(['status' => 'success', 'message' => 'Submission status updated successfully with file.']);
        } else {
            // To avoid errors when resubmitting the same status
            echo json_encode(['status' => 'success', 'message' => 'Submission status was already up to date.']);
        }
    }

} catch (PDOException $e) {
    http_response_code(500);
    error_log($e->getMessage()); // Log error for debugging
    echo json_encode(['status' => 'error', 'message' => 'An internal database error occurred.']);
}
?>