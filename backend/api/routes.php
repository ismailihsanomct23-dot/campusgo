<?php
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Database.php';

$config = require __DIR__ . '/../config.php';
$db = (new Database($config))->pdo();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
  jsonResponse(405, ['ok' => false, 'message' => 'Method not allowed']);
}

$routeStmt = $db->query("SELECT id, route_code, name, bus_no, color, base_fare, bus_capacity FROM routes WHERE status='active' ORDER BY id");
$routes = $routeStmt->fetchAll();

$stopStmt = $db->prepare('SELECT stop_name FROM route_stops WHERE route_id = :route_id ORDER BY stop_order');
$timeStmt = $db->prepare('SELECT schedule_type, time_label FROM route_times WHERE route_id = :route_id ORDER BY id');

$payload = [];
foreach ($routes as $route) {
  $stopStmt->execute([':route_id' => $route['id']]);
  $stops = array_map(fn($x) => $x['stop_name'], $stopStmt->fetchAll());

  $timeStmt->execute([':route_id' => $route['id']]);
  $times = $timeStmt->fetchAll();
  $morning = [];
  $evening = [];
  foreach ($times as $row) {
    if ($row['schedule_type'] === 'morning') $morning[] = $row['time_label'];
    if ($row['schedule_type'] === 'evening') $evening[] = $row['time_label'];
  }

  $payload[] = [
    'id' => $route['route_code'],
    'name' => $route['name'],
    'busNo' => $route['bus_no'],
    'color' => $route['color'],
    'baseFare' => (float)$route['base_fare'],
    'busCapacity' => (int)$route['bus_capacity'],
    'stops' => $stops,
    'morningTimes' => $morning,
    'eveningTimes' => $evening,
    'dbId' => (int)$route['id'],
  ];
}

jsonResponse(200, ['ok' => true, 'routes' => $payload]);
