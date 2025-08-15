<?php
session_start();
require_once __DIR__ . '/../db_connect.php';
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user'])) {
  http_response_code(401);
  echo json_encode(['status'=>'error','message'=>'Not authenticated']);
  exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  try {
    $sql = "SELECT p.id, p.title, p.year, p.semester, u.full_name AS advisor_name
            FROM projects p JOIN users u ON p.advisor_id = u.id
            ORDER BY p.year DESC, p.semester DESC, p.title";
    $stmt = $pdo->query($sql);
    echo json_encode(['status'=>'success','data'=>$stmt->fetchAll()]);
  } catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
  }
  exit;
}

if ($method === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true);
  if (empty($input['title']) || empty($input['year']) || empty($input['semester']) || empty($input['advisor_id'])) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Missing required fields: title, year, semester, advisor_id']);
    exit;
  }
  try {
    $stmt = $pdo->prepare("INSERT INTO projects (title, year, semester, advisor_id)
                           VALUES (:title,:year,:semester,:advisor_id)");
    $stmt->execute([
      ':title'=>$input['title'],
      ':year'=>$input['year'],
      ':semester'=>$input['semester'],
      ':advisor_id'=>$input['advisor_id'],
    ]);
    echo json_encode(['status'=>'success','id'=>$pdo->lastInsertId()]);
  } catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
  }
  exit;
}

http_response_code(405);
echo json_encode(['status'=>'error','message'=>'Method Not Allowed']);
