<?php
require_once __DIR__ . '/load_env.php';
load_env(__DIR__ . '/../.env'); // อ่าน .env จากรูท

$host = getenv('DB_HOST') ?: 'srv2046.hstgr.io';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('MYSQL_DATABASE') ?: 'u383523667_aicsat';
$user = getenv('MYSQL_USER') ?: 'user';
$pass = getenv('MYSQL_PASSWORD') ?: 'password';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

$ca = getenv('DB_SSL_CA');
if ($ca && file_exists($ca)) {
  $options[PDO::MYSQL_ATTR_SSL_CA] = $ca;
  $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Throwable $e) {
  error_log("DB connect failed: " . $e->getMessage());
  http_response_code(500);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['status'=>'error','message'=>'เชื่อมต่อฐานข้อมูลล้มเหลว']);
  exit;
}
