<?php
/**
 * CampusGo Database Reset Script
 * Clears existing database and reinitializes with new credentials
 */

$config = require __DIR__ . '/config.php';
$dbType = $config['db_type'] ?? 'sqlite';

if ($dbType === 'sqlite') {
    $dbPath = $config['db_path'];
    $initFile = __DIR__ . '/.initialized';
    
    // Remove existing database
    if (file_exists($dbPath)) {
        unlink($dbPath);
        echo "✅ Database file deleted\n";
    }
    
    // Remove initialization marker
    if (file_exists($initFile)) {
        unlink($initFile);
        echo "✅ Initialization marker removed\n";
    }
    
    // Reinitialize
    require __DIR__ . '/bootstrap.php';
    echo "✅ Database reinitialized with admin credentials\n";
    echo "\n--- New Admin Credentials ---\n";
    echo "Email: admin@college.edu\n";
    echo "Password: admin123\n";
    echo "Role: Admin\n";
} else {
    echo "❌ This script only supports SQLite databases\n";
}
?>
