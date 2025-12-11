<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

header("Content-Type: application/json; charset=UTF-8");

include_once '../db_connect.php';
include_once '../load_env.php'; // Although not directly used for DB creds, it might be for other env vars

$requestMethod = $_SERVER["REQUEST_METHOD"];

if ($requestMethod === 'GET') {
    $sql = "SELECT a.advisor_id, u.full_name, a.department FROM advisors a JOIN users u ON a.user_id = u.id ORDER BY u.full_name ASC";
    $stmt = $pdo->query($sql);
    $advisors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($advisors);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed."]);
}

// PDO connection is automatically closed when the script finishes
?>