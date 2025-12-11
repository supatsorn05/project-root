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

try {
  $term_id = $_GET['term_id'] ?? null;

  $sql = "SELECT p.project_id, p.project_name, p.project_name_en, p.abstract, u.full_name as advisor_name, at.academic_year, at.term_name FROM projects p JOIN advisors a ON p.advisor_id = a.advisor_id JOIN users u ON a.user_id = u.id JOIN academic_terms at ON p.term_id = at.term_id";
  $params = [];

  if ($term_id) {
    $sql .= " WHERE p.term_id = :term_id";
    $params[':term_id'] = $term_id;
  }

  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  echo json_encode(['status'=>'success','projects'=>$stmt->fetchAll()]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
