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
    $token_hash = hash('sha256', $token);

    $stmt = $pdo->prepare("SELECT id, role FROM users WHERE session_token = :token_hash");
    $stmt->execute([':token_hash' => $token_hash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status'=>'error','message'=>'Unauthorized: Invalid token or insufficient role']);
        exit;
    }
} else {
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'Unauthorized: Token not provided']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$project_id = $_GET['project_id'] ?? null;
$doc_type_id = $_GET['doc_type_id'] ?? null;

if (!$project_id || !$doc_type_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Project ID and Document Type ID are required.']);
    exit;
}

try {
    // Get file path from database
    $stmt = $pdo->prepare("SELECT file_path FROM submitted_documents WHERE project_id = :project_id AND doc_type_id = :doc_type_id");
    $stmt->execute([':project_id' => $project_id, ':doc_type_id' => $doc_type_id]);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($document && $document['file_path']) {
        $file_to_delete = __DIR__ . '/../' . $document['file_path'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
    }

    // Delete record from database
    $stmt = $pdo->prepare("DELETE FROM submitted_documents WHERE project_id = :project_id AND doc_type_id = :doc_type_id");
    $stmt->execute([':project_id' => $project_id, ':doc_type_id' => $doc_type_id]);

    echo json_encode(['status' => 'success', 'message' => 'Document deleted successfully']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}