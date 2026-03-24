<?php
echo "\n=== CampusGo API Test ===\n\n";

require_once __DIR__ . '/backend/lib/Database.php';
$config = require __DIR__ . '/backend/config.php';

try {
    $db = (new Database($config))->pdo();
    echo "✓ Database connection successful\n";
    
    // Check if database has data
    $routeCount = $db->query('SELECT COUNT(*) AS c FROM routes')->fetch()['c'];
    $userCount = $db->query('SELECT COUNT(*) AS c FROM users')->fetch()['c'];
    
    echo "  Routes: $routeCount\n";
    echo "  Users: $userCount\n";
    
    // Check admin user
    $admin = $db->query("SELECT id, email, role, is_admin FROM users WHERE role='admin' OR is_admin=1 LIMIT 1")->fetch();
    if ($admin) {
        echo "\n✓ Admin user found:\n";
        echo "  Email: " . $admin['email'] . "\n";
        echo "  Role: " . $admin['role'] . "\n";
    } else {
        echo "\n✗ No admin user found!\n";
    }
    
    // Check routes from database
    $routes = $db->query("SELECT route_code, name FROM routes LIMIT 2")->fetchAll();
    if (count($routes) > 0) {
        echo "\n✓ Routes in database:\n";
        foreach ($routes as $route) {
            echo "  - " . $route['route_code'] . ": " . $route['name'] . "\n";
        }
    }
    
    echo "\n✓ All systems operational!\n";
    echo "\n=== Next Steps ===\n";
    echo "1. Terminal 1: php -S localhost:8000 -t backend\n";
    echo "2. Terminal 2: php -S localhost:8001\n";
    echo "3. Browser: http://localhost:8001/campusgo.html\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
