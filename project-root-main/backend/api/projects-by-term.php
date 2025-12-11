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

$term_id = filter_input(INPUT_GET, 'term_id', FILTER_VALIDATE_INT);

if (!$term_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'term_id parameter is required.']);
    exit;
}

try {
    // Get term info
    $termStmt = $pdo->prepare("SELECT academic_year, term_name FROM academic_terms WHERE term_id = :term_id");
    $termStmt->execute([':term_id' => $term_id]);
    $termInfo = $termStmt->fetch(PDO::FETCH_ASSOC);

    if (!$termInfo) {
        $termInfo = ['academic_year' => 'N/A', 'term_name' => 'N/A'];
    }

    // Get projects for the term
    $sql = "SELECT 
                p.project_id, 
                p.project_name, 
                p.project_name_en, 
                u1.full_name as main_advisor_name, 
                u2.full_name as secondary_advisor_name,
                sd.file_path as document_path 
            FROM projects p 
            LEFT JOIN advisors a1 ON p.main_advisor_id = a1.advisor_id
            LEFT JOIN users u1 ON a1.user_id = u1.id
            LEFT JOIN advisors a2 ON p.secondary_advisor_id = a2.advisor_id
            LEFT JOIN users u2 ON a2.user_id = u2.id
            LEFT JOIN submitted_documents sd ON p.project_id = sd.project_id AND sd.doc_type_id = 1 
            WHERE p.term_id = :term_id 
            ORDER BY p.project_name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':term_id' => $term_id]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepend base URL to document_path
    foreach ($projects as &$project) {
        if ($project['document_path']) {
            $project['document_path'] = rtrim('http://localhost:8000/', '/') . '/' . ltrim($project['document_path'], '/');
        }
    }

    echo json_encode([
        'status' => 'success',
        'term_info' => $termInfo,
        'data' => $projects
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
