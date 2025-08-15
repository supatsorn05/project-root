<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
    'http://localhost:5174',
    'http://127.0.0.1:5174'
];
if (in_array($origin, $allowed, true)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=utf-8");

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

echo json_encode([
    'status'=>'success',
    'user'=>$_SESSION['user']
]);
