<?php

class Database {
  private PDO $pdo;

  public function __construct(array $config) {
    $dsn = sprintf(
      'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
      $config['db_host'],
      (int)$config['db_port'],
      $config['db_name']
    );

    $this->pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    date_default_timezone_set($config['timezone'] ?? 'UTC');
  }

  public function pdo(): PDO {
    return $this->pdo;
  }
}
