<?php
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Database.php';

$config = require __DIR__ . '/../config.php';
$db = (new Database($config))->pdo();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  $email = strtolower(trim($_GET['email'] ?? ''));
  if ($email === '') {
    jsonResponse(422, ['ok' => false, 'message' => 'Missing query param: email']);
  }

  $stmt = $db->prepare('SELECT t.ticket_code AS id, r.route_code AS routeId, r.name AS routeName, r.bus_no AS busNo, t.from_stop AS `from`, t.to_stop AS `to`, t.travel_date AS date, t.time_slot AS time, t.schedule_type AS schedule, t.seat_no AS seat, t.fare, t.status, t.paid_via AS paidVia, t.booked_at AS bookedAt FROM tickets t INNER JOIN users u ON u.id = t.user_id INNER JOIN routes r ON r.id = t.route_id WHERE LOWER(u.email)=:email ORDER BY t.booked_at DESC');
  $stmt->execute([':email' => $email]);
  jsonResponse(200, ['ok' => true, 'tickets' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
  $body = getJsonBody();
  requireFields($body, ['email', 'routeCode', 'from', 'to', 'date', 'time', 'schedule', 'seat', 'fare', 'status']);

  $email = strtolower(trim($body['email']));

  $userStmt = $db->prepare('SELECT id, name, student_id, dept, role FROM users WHERE LOWER(email)=:email LIMIT 1');
  $userStmt->execute([':email' => $email]);
  $user = $userStmt->fetch();
  if (!$user) {
    jsonResponse(404, ['ok' => false, 'message' => 'User not found']);
  }

  $routeStmt = $db->prepare('SELECT id, name, bus_no FROM routes WHERE route_code=:code LIMIT 1');
  $routeStmt->execute([':code' => $body['routeCode']]);
  $route = $routeStmt->fetch();
  if (!$route) {
    jsonResponse(404, ['ok' => false, 'message' => 'Route not found']);
  }

  $ticketCode = 'CB' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

  $insert = $db->prepare('INSERT INTO tickets (ticket_code, user_id, route_id, from_stop, to_stop, travel_date, time_slot, schedule_type, seat_no, fare, status, paid_via) VALUES (:ticket_code,:user_id,:route_id,:from_stop,:to_stop,:travel_date,:time_slot,:schedule_type,:seat_no,:fare,:status,:paid_via)');

  try {
    $insert->execute([
      ':ticket_code' => $ticketCode,
      ':user_id' => $user['id'],
      ':route_id' => $route['id'],
      ':from_stop' => $body['from'],
      ':to_stop' => $body['to'],
      ':travel_date' => $body['date'],
      ':time_slot' => $body['time'],
      ':schedule_type' => $body['schedule'],
      ':seat_no' => $body['seat'],
      ':fare' => (float)$body['fare'],
      ':status' => $body['status'],
      ':paid_via' => $body['paidVia'] ?? null,
    ]);
  } catch (PDOException $e) {
    if ((int)$e->getCode() === 23000) {
      jsonResponse(409, ['ok' => false, 'message' => 'Seat already booked for selected trip']);
    }
    throw $e;
  }

  jsonResponse(201, [
    'ok' => true,
    'ticket' => [
      'id' => $ticketCode,
      'routeId' => $body['routeCode'],
      'routeName' => $route['name'],
      'busNo' => $route['bus_no'],
      'from' => $body['from'],
      'to' => $body['to'],
      'date' => $body['date'],
      'time' => $body['time'],
      'schedule' => $body['schedule'],
      'passenger' => $user['name'],
      'studentId' => $user['student_id'],
      'department' => $user['dept'],
      'role' => $user['role'],
      'seat' => $body['seat'],
      'fare' => (float)$body['fare'],
      'status' => $body['status'],
      'paidVia' => $body['paidVia'] ?? null,
      'bookedAt' => date('c'),
    ]
  ]);
}

if ($method === 'PATCH') {
  $body = getJsonBody();
  requireFields($body, ['ticketId', 'action']);

  if ($body['action'] === 'cancel') {
    $stmt = $db->prepare("UPDATE tickets SET status='cancelled', cancelled_at=NOW() WHERE ticket_code=:ticket_code");
    $stmt->execute([':ticket_code' => $body['ticketId']]);
    jsonResponse(200, ['ok' => true, 'message' => 'Ticket cancelled']);
  }

  if ($body['action'] === 'confirmReserved') {
    $stmt = $db->prepare("UPDATE tickets SET status='confirmed', paid_via=:paid_via WHERE ticket_code=:ticket_code AND status='reserved'");
    $stmt->execute([':paid_via' => $body['paidVia'] ?? null, ':ticket_code' => $body['ticketId']]);
    jsonResponse(200, ['ok' => true, 'message' => 'Reservation paid']);
  }

  jsonResponse(400, ['ok' => false, 'message' => 'Invalid action']);
}

jsonResponse(405, ['ok' => false, 'message' => 'Method not allowed']);
