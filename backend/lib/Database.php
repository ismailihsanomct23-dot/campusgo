<?php

class Database {
  private PDO $pdo;

  public function __construct(array $config) {
    $dbType = $config['db_type'] ?? 'mysql';
    
    if ($dbType === 'sqlite') {
      // SQLite connection
      $dbPath = $config['db_path'];
      // Ensure directory exists
      $dir = dirname($dbPath);
      if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
      }
      
      $this->pdo = new PDO("sqlite:$dbPath", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]);
      
      // Enable foreign keys for SQLite
      $this->pdo->exec('PRAGMA foreign_keys = ON');
    } else {
      // MySQL connection (legacy)
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
    }

    date_default_timezone_set($config['timezone'] ?? 'UTC');
  }

  public function pdo(): PDO {
    return $this->pdo;
  }
}
