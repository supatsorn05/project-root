<?php
$servername = "db";
$username = "user";
$password = "password";
$database = "test_db";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("เชื่อมต่อไม่สำเร็จ: " . $conn->connect_error);
}
echo "เชื่อมต่อฐานข้อมูลสำเร็จ!";
?>
