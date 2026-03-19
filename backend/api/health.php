<?php
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Database.php';

$config = require __DIR__ . '/../config.php';
$db = (new Database($config))->pdo();

$count = (int)$db->query('SELECT COUNT(*) AS c FROM routes')->fetch()['c'];
jsonResponse(200, ['ok' => true, 'message' => 'CampusGo PHP API is running', 'routes' => $count]);
