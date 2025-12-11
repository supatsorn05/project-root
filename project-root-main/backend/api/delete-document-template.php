<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../load_env.php';
require_once __DIR__ . '/../db_connect.php';

header('Content-Type: application/json; charset=utf-8');

// Authenticate user and check role
try {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/Bearer\s(\S+)/i', $auth_header, $matches)) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Token not provided']);
        exit;
    }

    $token = $matches[1];
    $token_hash = hash('sha256', $token);

    $stmt = $pdo->prepare("SELECT role FROM users WHERE session_token = :token_hash");
    $stmt->execute([':token_hash' => $token_hash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Invalid token or insufficient privileges']);
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database authentication error: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$template_id = $_GET['template_id'] ?? null;
$doc_type = $_GET['doc_type'] ?? null;

if (!$template_id || !$doc_type) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Template ID and document type are required.']);
    exit;
}

if (!in_array($doc_type, ['pdf', 'docx'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid document type specified.']);
    exit;
}

$url_column = $doc_type . '_url';

try {
    // 1. Fetch the file path from the database
    $stmt = $pdo->prepare("SELECT $url_column FROM document_templates WHERE template_id = :template_id");
    $stmt->execute([':template_id' => $template_id]);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($document && !empty($document[$url_column])) {
        $filename = $document[$url_column];
        // The script runs in /api, so we need to go up one level to the backend root.
        // The files are stored in the /uploads/documents/ directory.
        $file_to_delete = __DIR__ . '/../uploads/documents/' . $filename;

        // 2. Delete the physical file if it exists
        if (file_exists($file_to_delete)) {
            if (!unlink($file_to_delete)) {
                 // If unlink fails, we might not want to proceed with DB update.
                 http_response_code(500);
                 echo json_encode(['status' => 'error', 'message' => 'Failed to delete the file from the server.']);
                 exit;
            }
        }
    }

    // 3. Update the database to set the URL to NULL
    $stmt = $pdo->prepare("UPDATE document_templates SET $url_column = NULL WHERE template_id = :template_id");
    $stmt->execute([':template_id' => $template_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Document deleted and record updated successfully.']);
    } else {
        // This could happen if the template_id doesn't exist, or if the value was already NULL.
        // We can consider this a success if the file doesn't exist and the DB state is what we want.
        echo json_encode(['status' => 'success', 'message' => 'Document record updated (or was already null).']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}