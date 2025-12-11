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
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
  http_response_code(403);
  echo json_encode(['status'=>'error','message'=>'Unauthorized']);
  exit;
}

try {
  $stmt = $pdo->query("SELECT sd.id, sd.form_name, sd.file_path, sd.status, sd.feedback, u.full_name as student_name FROM signed_documents sd JOIN users u ON sd.user_id = u.id WHERE u.role = 'student'");
  echo json_encode(['status'=>'success','documents'=>$stmt->fetchAll()]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
