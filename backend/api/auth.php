<?php
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Database.php';

$config = require __DIR__ . '/../config.php';
$db = (new Database($config))->pdo();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method !== 'POST') {
  jsonResponse(405, ['ok' => false, 'message' => 'Method not allowed']);
}

$body = getJsonBody();

if ($action === 'register') {
  requireFields($body, ['name', 'email', 'studentId', 'phone', 'role', 'dept', 'year', 'password']);

  $email = strtolower(trim($body['email']));
  $studentId = strtoupper(trim($body['studentId']));

  $check = $db->prepare('SELECT id FROM users WHERE email = :email OR student_id = :student_id LIMIT 1');
  $check->execute([':email' => $email, ':student_id' => $studentId]);
  if ($check->fetch()) {
    jsonResponse(409, ['ok' => false, 'message' => 'Email or student ID already registered']);
  }

  $stmt = $db->prepare('INSERT INTO users (name, email, student_id, phone, role, dept, year_value, password_hash, is_admin) VALUES (:name,:email,:student_id,:phone,:role,:dept,:year_value,:password_hash,:is_admin)');
  $stmt->execute([
    ':name' => trim($body['name']),
    ':email' => $email,
    ':student_id' => $studentId,
    ':phone' => trim($body['phone']),
    ':role' => $body['role'],
    ':dept' => $body['dept'],
    ':year_value' => (string)$body['year'],
    ':password_hash' => password_hash($body['password'], PASSWORD_DEFAULT),
    ':is_admin' => ($body['role'] === 'admin') ? 1 : 0,
  ]);

  jsonResponse(201, ['ok' => true, 'message' => 'Registered successfully']);
}

if ($action === 'login') {
  requireFields($body, ['idOrEmail', 'password']);

  $idOrEmail = strtolower(trim($body['idOrEmail']));
  $stmt = $db->prepare('SELECT id, name, email, student_id, phone, role, dept, year_value, is_admin, password_hash, created_at FROM users WHERE LOWER(email)=:key OR LOWER(student_id)=:key LIMIT 1');
  $stmt->execute([':key' => $idOrEmail]);
  $user = $stmt->fetch();

  if (!$user || !password_verify($body['password'], $user['password_hash'])) {
    jsonResponse(401, ['ok' => false, 'message' => 'Invalid credentials']);
  }

  unset($user['password_hash']);
  $user['studentId'] = $user['student_id'];
  $user['year'] = $user['year_value'];
  $user['isAdmin'] = (bool)$user['is_admin'];
  unset($user['student_id'], $user['year_value'], $user['is_admin']);

  jsonResponse(200, ['ok' => true, 'user' => $user]);
}

jsonResponse(400, ['ok' => false, 'message' => 'Invalid action']);
