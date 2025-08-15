<?php
// backend/db_connect.php
$host = getenv('DB_HOST') ?: 'uni_db'; // ตาม compose network name
$db   = getenv('MYSQL_DATABASE') ?: 'university_portal';
$user = getenv('MYSQL_USER') ?: 'user';
$pass = getenv('MYSQL_PASSWORD') ?: 'password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // ไม่ควรส่ง error detail กลับ production
    error_log("DB connect failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'เชื่อมต่อฐานข้อมูลล้มเหลว']);
    exit;
}
