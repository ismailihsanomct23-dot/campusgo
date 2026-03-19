<?php

function jsonResponse(int $status, array $payload): void {
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Headers: Content-Type');
  header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
  echo json_encode($payload);
  exit;
}

function getJsonBody(): array {
  $raw = file_get_contents('php://input') ?: '';
  if ($raw === '') {
    return [];
  }

  $decoded = json_decode($raw, true);
  if (!is_array($decoded)) {
    jsonResponse(400, ['ok' => false, 'message' => 'Invalid JSON body']);
  }

  return $decoded;
}

function requireFields(array $data, array $fields): void {
  foreach ($fields as $field) {
    if (!array_key_exists($field, $data) || $data[$field] === '' || $data[$field] === null) {
      jsonResponse(422, ['ok' => false, 'message' => "Missing field: {$field}"]);
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  jsonResponse(200, ['ok' => true]);
}
