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

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

if (preg_match('/Bearer\s(.+)/', $authHeader, $matches)) {
    $token = $matches[1];
    $token_hash = hash('sha256', $token);

    $stmt = $pdo->prepare("SELECT id, role FROM users WHERE session_token = :token_hash LIMIT 1");
    $stmt->execute([':token_hash' => $token_hash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized or not an admin']);
        exit;
    }
} else {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Authentication token required']);
    exit;
}

try {
  $sql = "SELECT u.id, u.full_name, u.username, u.role, u.email, s.student_id, s.group_name 
          FROM users u
          LEFT JOIN students s ON u.id = s.user_id";
  $params = [];

  if (isset($_GET['id'])) {
      $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
      if (!$id) {
          http_response_code(400);
          echo json_encode(['status'=>'error','message'=>'Invalid user ID']);
          exit;
      }
      $sql .= " WHERE u.id = ?";
      $params[] = $id;
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($user) {
          echo json_encode(['status'=>'success','user'=>$user]);
      } else {
          http_response_code(404);
          echo json_encode(['status'=>'error','message'=>'User not found']);
      }
  } elseif (isset($_GET['role'])) { // Existing logic for filtering by role
      $role = $_GET['role'];
      $sql .= " WHERE u.role = ?";
      $params[] = $role;
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      echo json_encode(['status'=>'success','users'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
  } else { // Fetch all users
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      echo json_encode(['status'=>'success','users'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
  }
} catch (PDOException $e) {
  http_response_code(500);
  error_log("PDOException in users.php: " . $e->getMessage());
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}