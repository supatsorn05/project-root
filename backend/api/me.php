<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (empty($_SESSION['user'])) {
  http_response_code(401);
  echo json_encode(['status'=>'error','message'=>'Not authenticated']);
  exit;
}
echo json_encode(['status'=>'success','user'=>$_SESSION['user']]);
