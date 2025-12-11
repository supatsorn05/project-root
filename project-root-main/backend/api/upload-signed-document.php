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


if (!isset($_SESSION['user'])) {
  http_response_code(401);
  echo json_encode(['status'=>'error','message'=>'Unauthorized']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$upload_dir = 'uploads/signed_documents/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (isset($_FILES['document'])) {
    $file = $_FILES['document'];
    $form_name = $_POST['form_name'] ?? '';
    $user_id = $_SESSION['user']['id'];

    if (empty($form_name)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Form name is required']);
        exit;
    }

    $file_name = uniqid() . '-' . basename($file['name']);
    $target_file = $upload_dir . $file_name;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO signed_documents (form_name, file_path, user_id) VALUES (?, ?, ?)");
            $stmt->execute([$form_name, $target_file, $user_id]);

            echo json_encode(['status' => 'success', 'message' => 'File uploaded successfully']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload file']);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No file was uploaded']);
}
