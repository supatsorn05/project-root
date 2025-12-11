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

// This should be protected for admins

$template_id = isset($_POST['template_id']) ? $_POST['template_id'] : null;
$doc_type = isset($_POST['doc_type']) ? $_POST['doc_type'] : null; // 'pdf' or 'docx'

if (!$template_id || !$doc_type || !isset($_FILES['document'])) {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'Missing parameters.'));
    exit;
}

$column_to_update = ($doc_type === 'pdf') ? 'pdf_url' : 'docx_url';

// Get the existing filename from DB
$stmt = $pdo->prepare("SELECT pdf_url, docx_url, template_name FROM document_templates WHERE template_id = :id");
$stmt->execute(array('id' => $template_id));
$doc = $stmt->fetch();

if (!$doc) {
    http_response_code(404);
    echo json_encode(array('status' => 'error', 'message' => 'Template not found.'));
    exit;
}

$filename_from_db = $doc[$column_to_update];

// Determine the filename to store in DB and the full server path
$upload_dir_base = __DIR__ . '/../uploads/documents'; // Base directory for actual files

$final_filename = $filename_from_db; // Filename to store in DB

// If filename is not defined in DB, create a new one based on name and type
if (!$final_filename) {
    $ext = ($doc_type === 'pdf') ? '.pdf' : '.docx';
    // Sanitize name for filename (allow Thai characters)
    $name_with_underscores = str_replace(' ', '_', $doc['template_name']);
    $safe_filename = preg_replace('/[^\p{L}\p{N}_.-]+/u', '', $name_with_underscores);
    $final_filename = $safe_filename . '_' . $doc_type . $ext; // Filename to store in DB
}

$destination = $upload_dir_base . '/' . $final_filename; // Full server path

// Ensure directory exists
$dir = dirname($destination);
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

if (move_uploaded_file($_FILES['document']['tmp_name'], $destination)) {
    // Update the database with the filename
    $update_stmt = $pdo->prepare("UPDATE document_templates SET $column_to_update = :filename WHERE template_id = :id");
    $update_stmt->execute(array(':filename' => $final_filename, ':id' => $template_id));

    echo json_encode(array('status' => 'success', 'message' => 'File uploaded successfully.'));
} else {
    http_response_code(500);
    echo json_encode(array('status' => 'error', 'message' => 'Failed to move uploaded file.'));
}