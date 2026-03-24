<?php
/**
 * CampusGo Auto-Initialization Bootstrap
 * Automatically creates database and tables on first run
 * Also seeds test data if database is empty
 */

$config = require __DIR__ . '/config.php';

// Store initialization state in a file
$initFile = __DIR__ . '/.initialized';

if (!file_exists($initFile)) {
  try {
    $dbType = $config['db_type'] ?? 'mysql';
    
    if ($dbType === 'sqlite') {
      // SQLite setup
      $dbPath = $config['db_path'];
      $dir = dirname($dbPath);
      if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
      }
      
      $pdo = new PDO("sqlite:$dbPath", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      ]);
      
      // Enable foreign keys
      $pdo->exec('PRAGMA foreign_keys = ON');
      
      // Read and execute SQLite schema
      $schema = file_get_contents(__DIR__ . '/database.sqlite.sql');
      
      // Split by semicolon and execute each statement
      $statements = array_filter(array_map('trim', explode(';', $schema)));
      foreach ($statements as $statement) {
        if (!empty($statement)) {
          $pdo->exec($statement);
        }
      }
      
    } else {
      // MySQL setup (legacy)
      $dsn = sprintf(
        'mysql:host=%s;port=%d;charset=utf8mb4',
        $config['db_host'],
        (int)$config['db_port']
      );

      $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      ]);

      $schema = file_get_contents(__DIR__ . '/database.sql');
      $statements = array_filter(array_map('trim', explode(';', $schema)));
      
      foreach ($statements as $statement) {
        if (!empty($statement)) {
          $pdo->exec($statement);
        }
      }
    }

    // Mark as initialized
    file_put_contents($initFile, time());
    
  } catch (PDOException $e) {
    // Log error but don't crash
    error_log('CampusGo init: ' . $e->getMessage());
  }
}

// Always ensure test user exists (call this on every request to seed if needed)
try {
  $config = require __DIR__ . '/config.php';
  $dbType = $config['db_type'] ?? 'mysql';
  
  if ($dbType === 'sqlite') {
    $pdo = new PDO("sqlite:" . $config['db_path'], null, null, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
  } else {
    $dsn = sprintf(
      'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
      $config['db_host'],
      (int)$config['db_port'],
      $config['db_name']
    );
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
  }
  
  seedTestUser($pdo);
} catch (Exception $e) {
  // Silently ignore seeding errors
}

/**
 * Seed test user if database is empty
 */
function seedTestUser($pdo) {
  try {
    $admin = $pdo->query("SELECT id, name, password_hash FROM users WHERE role = 'admin' OR is_admin = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
      $needsNameUpdate = (($admin['name'] ?? '') !== 'admin');
      $needsPasswordUpdate = !password_verify('admin123', (string)($admin['password_hash'] ?? ''));

      if ($needsNameUpdate || $needsPasswordUpdate) {
        $sync = $pdo->prepare("UPDATE users SET name = :name, password_hash = :password_hash, role = :role, is_admin = :is_admin WHERE id = :id");
        $sync->execute([
          ':name' => 'admin',
          ':password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
          ':role' => 'admin',
          ':is_admin' => 1,
          ':id' => (int)$admin['id'],
        ]);
        error_log('CampusGo: Admin credentials synchronized');
      }
      return;
    }

    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);

    $existingDefault = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(:email) OR UPPER(student_id) = UPPER(:student_id) LIMIT 1");
    $existingDefault->execute([
      ':email' => 'admin@college.edu',
      ':student_id' => 'ADM001',
    ]);
    $existingUser = $existingDefault->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
      $promote = $pdo->prepare("UPDATE users SET name = :name, role = :role, dept = :dept, year_value = :year_value, password_hash = :password_hash, is_admin = :is_admin WHERE id = :id");
      $promote->execute([
        ':name' => 'admin',
        ':role' => 'admin',
        ':dept' => 'Admin',
        ':year_value' => 'na',
        ':password_hash' => $hashedPassword,
        ':is_admin' => 1,
        ':id' => (int)$existingUser['id'],
      ]);
      error_log('CampusGo: Existing default user promoted to admin');
      return;
    }

    $insert = $pdo->prepare("INSERT INTO users (name, email, student_id, phone, role, dept, year_value, password_hash, is_admin) VALUES (:name, :email, :student_id, :phone, :role, :dept, :year_value, :password_hash, :is_admin)");
    $insert->execute([
      ':name' => 'admin',
      ':email' => 'admin@college.edu',
      ':student_id' => 'ADM001',
      ':phone' => '9999999999',
      ':role' => 'admin',
      ':dept' => 'Admin',
      ':year_value' => 'na',
      ':password_hash' => $hashedPassword,
      ':is_admin' => 1,
    ]);

    error_log('CampusGo: Admin user created');
  } catch (Exception $e) {
    // Ignore seed errors
  }
}
?>
