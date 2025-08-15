<?php
$password = 'admin123'; // เปลี่ยนเป็นรหัสที่อยากเข้ารหัส
$hash = password_hash($password, PASSWORD_DEFAULT);
echo $hash;
