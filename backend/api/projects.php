<?php
// CORS
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = ['http://localhost:5173', 'http://127.0.0.1:5173', 'http://localhost:5174', 'http://localhost:3000'];
if (in_array($origin, $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: *");
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();
if (empty($_SESSION['user'])) {
  http_response_code(401);
  echo json_encode(['status'=>'error','message'=>'Not authenticated']);
  exit;
}

require __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->query("
          SELECT 
            p.id, p.title, p.year, p.semester,
            u.full_name AS advisor_name
          FROM projects p
          JOIN users u ON p.advisor_id = u.id
          ORDER BY p.year DESC, p.semester DESC, p.title
        ");
        echo json_encode([
          'status' => 'success',
          'data'   => $stmt->fetchAll()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([ 'status'=>'error','message'=>$e->getMessage() ]);
    }
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (empty($input['title']) || empty($input['year']) || empty($input['semester']) || empty($input['advisor_id'])) {
        http_response_code(400);
        echo json_encode([
          'status' => 'error',
          'message' => 'Missing required fields: title, year, semester, advisor_id'
        ]);
        exit;
    }

    try {
        $sql = "INSERT INTO projects (title, year, semester, advisor_id)
                VALUES (:title, :year, :semester, :advisor_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
          ':title'      => $input['title'],
          ':year'       => $input['year'],
          ':semester'   => $input['semester'],
          ':advisor_id' => $input['advisor_id'],
        ]);

        echo json_encode([
          'status' => 'success',
          'id'     => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([ 'status'=>'error','message'=>$e->getMessage() ]);
    }
    exit;
}

http_response_code(405);
echo json_encode([
  'status'  => 'error',
  'message' => 'Method Not Allowed'
]);
