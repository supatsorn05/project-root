<?php
require_once __DIR__ . '/../db_connect.php';
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['status'=>'ok','db'=>'connected']);
