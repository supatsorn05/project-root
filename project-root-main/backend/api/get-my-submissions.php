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

    $stmt = $pdo->prepare("SELECT s.student_id FROM users u JOIN students s ON u.id = s.user_id WHERE u.session_token = :token_hash");
    $stmt->execute(['token_hash' => $token_hash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['student_id']) {
        $student_id = $user['student_id'];
    } else {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Student context not found.']);
        exit;
    }
} else {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Token not provided']);
    exit;
}

// 2. Fetch submissions for the student
try {
    $sql = "SELECT 
                sd.student_document_id,
                sdt.doc_code,
                sdt.doc_name,
                t.full_name AS teacher_name,
                sd.file_path,
                sd.uploaded_at,
                sd.status,
                sd.comment
            FROM 
                student_documents sd
            JOIN 
                submission_document_types sdt ON sd.doc_type_id = sdt.doc_type_id
            JOIN 
                users t ON sd.teacher_user_id = t.id
            WHERE 
                sd.student_id = :student_id
            ORDER BY 
                sd.uploaded_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['student_id' => $student_id]);
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $submissions]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>