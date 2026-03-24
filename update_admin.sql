-- Update admin user credentials
UPDATE users 
SET 
  email = 'admin123@gmail.com',
  password_hash = '$2y$10$vZs5hxKHNlH6vDvMW2w4xuqxJZLTBDSvPQ8OcqQmqCZsZPj3MfBi2'
WHERE role = 'admin' OR is_admin = 1
LIMIT 1;

-- Verify the update
SELECT id, name, email, role, is_admin FROM users WHERE role = 'admin' OR is_admin = 1 LIMIT 1;
