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

$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);

$id = $input['id'] ?? '';
$status = $input['status'] ?? '';
$feedback = $input['feedback'] ?? '';

if (empty($id) || empty($status)) {
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'Document ID and status are required']);
  exit;
}

if (!in_array($status, ['approved', 'rejected'])) {
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
  exit;
}

try {
  $stmt = $pdo->prepare("UPDATE signed_documents SET status = ?, feedback = ? WHERE id = ?");
  $stmt->execute([$status, $feedback, $id]);

  echo json_encode(['status' => 'success', 'message' => 'Document status updated successfully']);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
