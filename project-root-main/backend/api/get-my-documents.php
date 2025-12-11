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

$user_id = $_SESSION['user']['id'];

try {
  $stmt = $pdo->prepare("SELECT id, form_name, file_path, status, feedback FROM signed_documents WHERE user_id = ?");
  $stmt->execute([$user_id]);
  echo json_encode(['status'=>'success','documents'=>$stmt->fetchAll()]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
