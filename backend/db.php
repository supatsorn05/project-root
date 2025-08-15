<?php
// backend/db.php
// ตั้งค่าการเชื่อมต่อกับ MySQL
$host = '127.0.0.1';   // หรือ 'localhost'
$user = 'your_db_user';
$pass = 'your_db_pass';
$dbname = 'your_db_name';

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // ให้โยน exception เมื่อมี error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // เขียนใน log เพื่อ debug แล้วส่งข้อความทั่วไปกลับไป
    error_log("DB connection failed: " . $e->getMessage());
    // ถ้าไฟล์นี้ถูก include ก่อนที่ response header/json จะถูกส่ง, อย่า echo ตรงนี้ ยกให้ caller จัดการ
    // แต่ถ้าจะ debug รุนแรงให้ uncomment:
    // echo json_encode(['status'=>'error','message'=>'Database connection failed: '.$e->getMessage()]);
    // exit;
}
