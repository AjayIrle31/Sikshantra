<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
function is_logged_in() { return isset($_SESSION["user"]); }
function require_login() { if(!is_logged_in()){ header("Location: /fy_proj/auth/login.php"); exit; } }
function require_role($roles = []){
  if(!is_logged_in()){ header("Location: /fy_proj/auth/login.php"); exit; }
  if (!in_array($_SESSION["user"]["role"], $roles)) { http_response_code(403); echo "Forbidden"; exit; }
}
?>
