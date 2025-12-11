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

// Token-based authentication
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

if (preg_match('/Bearer\s(\S+)/i', $auth_header, $matches)) {
    $token = $matches[1];
    error_log("Received token: " . $token);
    $token_hash = hash('sha256', $token);
    error_log("Hashed token: " . $token_hash);

    $stmt = $pdo->prepare("SELECT id, role FROM users WHERE session_token = :token_hash");
    $stmt->execute([':token_hash' => $token_hash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        error_log("User found: " . print_r($user, true));
    }

    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status'=>'error','message'=>'Unauthorized: Invalid token or insufficient role']);
        exit;
    }
    $uploader_user_id = $user['id'];
} else {
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'Unauthorized: Token not provided']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$project_id = $_POST['project_id'] ?? null;
$doc_type_id = $_POST['doc_type_id'] ?? null;

if (!$project_id || !$doc_type_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Project ID and Document Type ID are required.']);
    exit;
}

$upload_dir = __DIR__ . '/../uploads/project_documents/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_file_name = uniqid('doc_') . '.' . $file_extension;
    $target_file = $upload_dir . $new_file_name;
    $relative_file_path = 'uploads/project_documents/' . $new_file_name; // Path to store in DB

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        try {
            // Check if a document already exists for this project and doc_type
            $stmt = $pdo->prepare("SELECT submission_id FROM submitted_documents WHERE project_id = :project_id AND doc_type_id = :doc_type_id");
            $stmt->execute([':project_id' => $project_id, ':doc_type_id' => $doc_type_id]);
            $existing_submission = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_submission) {
                // Update existing document
                $stmt = $pdo->prepare("UPDATE submitted_documents SET file_path = :file_path, uploaded_at = NOW(), uploader_user_id = :uploader_user_id WHERE submission_id = :submission_id");
                $stmt->execute([
                    ':file_path' => $relative_file_path,
                    ':uploader_user_id' => $uploader_user_id,
                    ':submission_id' => $existing_submission['submission_id']
                ]);
            } else {
                // Insert new document
                $stmt = $pdo->prepare("INSERT INTO submitted_documents (project_id, doc_type_id, file_path, uploaded_at, uploader_user_id) VALUES (:project_id, :doc_type_id, :file_path, NOW(), :uploader_user_id)");
                $stmt->execute([
                    ':project_id' => $project_id,
                    ':doc_type_id' => $doc_type_id,
                    ':file_path' => $relative_file_path,
                    ':uploader_user_id' => $uploader_user_id
                ]);
            }

            echo json_encode(['status' => 'success', 'message' => 'File uploaded and saved successfully', 'file_path' => $relative_file_path]);
        } catch (PDOException $e) {
            http_response_code(500);
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
