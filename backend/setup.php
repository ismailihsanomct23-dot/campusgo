<?php
/**
 * CampusGo Database Setup Script
 * Run this once to initialize the database
 */

$config = require __DIR__ . '/config.php';

// Connect to MySQL server (without specifying database)
try {
  $dsn = sprintf(
    'mysql:host=%s;port=%d;charset=utf8mb4',
    $config['db_host'],
    (int)$config['db_port']
  );

  $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ]);

  echo "✓ Connected to MySQL\n";

  // Read and execute database schema
  $schema = file_get_contents(__DIR__ . '/database.sql');
  
  // Split by semicolon and execute each statement
  $statements = array_filter(array_map('trim', explode(';', $schema)));
  
  foreach ($statements as $statement) {
    if (!empty($statement)) {
      $pdo->exec($statement);
    }
  }

  echo "✓ Database schema created successfully\n";
  echo "✓ Routes and schedules initialized\n";
  echo "\n✅ CampusGo database setup complete!\n";
  echo "📍 API available at: http://localhost:8000\n";

} catch (PDOException $e) {
  echo "❌ Database Error: " . $e->getMessage() . "\n";
  exit(1);
}
?>
