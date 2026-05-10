-- Update demo credentials - HahuCare
-- Run this SQL script to update existing demo users in the database
-- Password hash for 'P0o9i8u7!': $2y$10$mN.cqqo2BLJOvoE6b0MDUOqLy0aXdDJyj3e8NUA9EEx3uP20Ygrci

-- First, delete any old kivicare.com users to avoid duplicates
DELETE FROM model_has_roles WHERE model_id IN (SELECT id FROM users WHERE email LIKE '%@kivicare.com');
DELETE FROM users WHERE email LIKE '%@kivicare.com';

-- Update admin user (only if exists with hahucare.com, update password)
UPDATE users 
SET password = '$2y$10$mN.cqqo2BLJOvoE6b0MDUOqLy0aXdDJyj3e8NUA9EEx3uP20Ygrci'
WHERE email = 'admin@hahucare.com';

-- Update demo_admin user
UPDATE users 
SET password = '$2y$10$mN.cqqo2BLJOvoE6b0MDUOqLy0aXdDJyj3e8NUA9EEx3uP20Ygrci'
WHERE email = 'demo@hahucare.com';

-- Update vendor (clinic admin) user
UPDATE users 
SET password = '$2y$10$mN.cqqo2BLJOvoE6b0MDUOqLy0aXdDJyj3e8NUA9EEx3uP20Ygrci'
WHERE email = 'vendor@hahucare.com';

-- Update pharma user
UPDATE users 
SET password = '$2y$10$mN.cqqo2BLJOvoE6b0MDUOqLy0aXdDJyj3e8NUA9EEx3uP20Ygrci'
WHERE email = 'pharma@hahucare.com';

-- Insert lab technician user if not exists
INSERT INTO users (first_name, last_name, email, password, mobile, date_of_birth, country, state, city, pincode, gender, email_verified_at, created_at, updated_at, user_type, clinic_id)
SELECT 'Lab', 'Technician', 'lab@hahucare.com', '$2y$10$mN.cqqo2BLJOvoE6b0MDUOqLy0aXdDJyj3e8NUA9EEx3uP20Ygrci', '+91 9998887776', '1985-03-15', 230, 3812, 41432, '12345', 'Male', NOW(), NOW(), NOW(), 'lab_technician', 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'lab@hahucare.com');

-- Assign lab_technician role to the new user
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT r.id, 'App\Models\User', u.id
FROM roles r
CROSS JOIN users u
WHERE r.name = 'lab_technician' AND u.email = 'lab@hahucare.com'
AND NOT EXISTS (SELECT 1 FROM model_has_roles WHERE model_type = 'App\Models\User' AND model_id = u.id);
