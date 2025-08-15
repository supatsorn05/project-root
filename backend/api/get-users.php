<?php
session_start();
require_once __DIR__ . '/../db_connect.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
  http_response_code(403);
  echo json_encode(['status'=>'error','message'=>'Unauthorized']);
  exit;
}

try {
  $stmt = $pdo->query("SELECT id, student_id, full_name, role, email FROM users");
  echo json_encode(['status'=>'success','users'=>$stmt->fetchAll()]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
