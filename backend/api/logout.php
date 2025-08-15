<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$_SESSION = [];
if (ini_get('session.use_cookies')) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
echo json_encode(['status'=>'success','message'=>'Logged out']);
