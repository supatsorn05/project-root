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
$teacher_user_id = null;

if (preg_match('/Bearer\s(\S+)/i', $auth_header, $matches)) {
    $token = $matches[1];
    $token_hash = hash('sha256', $token);

    $stmt = $pdo->prepare("SELECT id, role FROM users WHERE session_token = :token_hash AND role = 'teacher'");
    $stmt->execute(['token_hash' => $token_hash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $teacher_user_id = $user['id'];
    } else {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Teacher role required.']);
        exit;
    }
} else {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Token not provided']);
    exit;
}

// 2. Fetch documents
try {
    $provisional_sql = "SELECT 
                            sd.student_document_id AS id,
                            sdt.doc_code,
                            sdt.doc_name,
                            u.full_name AS student_name,
                            s.group_name,
                            sd.file_path,
                            sd.uploaded_at,
                            sd.status,
                            sd.comment,
                            sd.teacher_file_path
                        FROM 
                            student_documents sd
                        JOIN 
                            submission_document_types sdt ON sd.doc_type_id = sdt.doc_type_id
                        JOIN 
                            students s ON sd.student_id = s.student_id
                        JOIN
                            users u ON s.user_id = u.id
                        WHERE 
                            sd.teacher_user_id = :teacher_user_id
                        ORDER BY 
                            sd.uploaded_at DESC";
    $stmt_provisional = $pdo->prepare($provisional_sql);
    $stmt_provisional->execute(['teacher_user_id' => $teacher_user_id]);
    $provisional_docs = $stmt_provisional->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $provisional_docs]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>