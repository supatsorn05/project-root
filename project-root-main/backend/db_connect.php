<?php

date_default_timezone_set('Asia/Bangkok');

// In a real application, these credentials should come from a secure, non-versioned
// source like an .env file, not be hardcoded.
// For this development environment, we are using the credentials for the Docker setup.

$host = getenv('DB_HOST') ?: 'db';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('MYSQL_DATABASE') ?: 'university_portal';
$user = getenv('MYSQL_USER') ?: 'db_user';
$pass = getenv('MYSQL_PASSWORD') ?: 'password@1';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Throwable $e) {
  error_log("DB connect failed: " . $e->getMessage());
  http_response_code(500);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['status'=>'error','message'=>'ไม่สามารถเชื่อมต่อฐานข้อมูลได้']);
  exit;
}