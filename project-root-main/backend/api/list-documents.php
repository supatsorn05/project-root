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

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
  http_response_code(403);
  echo json_encode(['status'=>'error','message'=>'Unauthorized']);
  exit;
}

$upload_dir = 'uploads/';
$files = scandir($upload_dir);
$documents = [];

foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        $documents[] = [
            'name' => $file,
            'path' => $upload_dir . $file
        ];
    }
}

echo json_encode(['status' => 'success', 'documents' => $documents]);
