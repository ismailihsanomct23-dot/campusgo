<?php
require_once __DIR__ . '/backend/lib/Database.php';
require_once __DIR__ . '/backend/lib/Response.php';

$config = require __DIR__ . '/backend/config.php';
$db = (new Database($config))->pdo();

// Check if admin exists
echo "=== Checking Admin User ===\n";
$stmt = $db->prepare('SELECT id, name, email, student_id, role, is_admin, password_hash FROM users WHERE role = "admin" OR is_admin = 1 LIMIT 1');
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "Admin found:\n";
    echo "  ID: " . $admin['id'] . "\n";
    echo "  Name: " . $admin['name'] . "\n";
    echo "  Email: " . $admin['email'] . "\n";
    echo "  Role: " . $admin['role'] . "\n";
    echo "  Is Admin: " . $admin['is_admin'] . "\n";
    echo "  Password Hash: " . substr($admin['password_hash'], 0, 20) . "...\n";
    
    // Test password verification
    echo "\n=== Testing Password Verification ===\n";
    $test_password = 'admin123';
    $verified = password_verify($test_password, $admin['password_hash']);
    echo "Password 'admin123' matches: " . ($verified ? "YES" : "NO") . "\n";
    
    if (!$verified) {
        echo "\nTrying to update with a new hash...\n";
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        echo "New hash: " . $new_hash . "\n";
        
        $update = $db->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $update->execute([':hash' => $new_hash, ':id' => $admin['id']]);
        echo "Updated! You can now try logging in.\n";
    }
} else {
    echo "No admin user found! Creating one...\n";
    $name = 'Admin';
    $email = 'admin123@gmail.com';
    $student_id = 'ADMIN001';
    $phone = '9000000000';
    $dept = 'IT';
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    
    $insert = $db->prepare('INSERT INTO users (name, email, student_id, phone, role, dept, year_value, password_hash, is_admin) VALUES (:name, :email, :student_id, :phone, :role, :dept, :year_value, :password_hash, :is_admin)');
    $insert->execute([
        ':name' => $name,
        ':email' => $email,
        ':student_id' => $student_id,
        ':phone' => $phone,
        ':role' => 'admin',
        ':dept' => $dept,
        ':year_value' => '4',
        ':password_hash' => $password_hash,
        ':is_admin' => 1
    ]);
    
    echo "Admin user created successfully!\n";
    echo "Email: " . $email . "\n";
    echo "Password: admin123\n";
}
?>
