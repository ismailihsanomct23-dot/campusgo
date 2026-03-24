<?php
require_once __DIR__ . '/backend/lib/Database.php';

$config = require __DIR__ . '/backend/config.php';
$db = (new Database($config))->pdo();

$new_name = 'admin';
$new_password = 'admin123';

echo "=== Updating Admin Credentials ===\n";
echo "New Name: " . $new_name . "\n";
echo "New Password: " . $new_password . "\n\n";

// Generate new password hash
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

// Update admin user
$stmt = $db->prepare('UPDATE users SET name = :name, password_hash = :hash WHERE role = "admin" OR is_admin = 1 LIMIT 1');
$updated = $stmt->execute([
    ':name' => $new_name,
    ':hash' => $new_hash
]);

if ($updated) {
    echo "✓ Admin credentials updated successfully!\n\n";
    echo "You can now login with:\n";
    echo "  Name: " . $new_name . "\n";
    echo "  Password: " . $new_password . "\n";
} else {
    echo "✗ Failed to update credentials\n";
}
?>
