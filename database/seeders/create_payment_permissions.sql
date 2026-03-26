-- Insert Payment permissions
INSERT INTO permissions (name, is_fixed, created_at, updated_at) VALUES 
('view_payments', 1, NOW(), NOW()),
('add_payments', 1, NOW(), NOW()),
('edit_payments', 1, NOW(), NOW()),
('delete_payments', 1, NOW(), NOW()),
('update_payment_status', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE name = name;

-- Assign payment permissions to admin role
INSERT INTO role_has_permissions (role_id, permission_id)
SELECT r.id, p.id 
FROM roles r, permissions p 
WHERE r.name = 'admin' 
AND p.name IN ('view_payments', 'add_payments', 'edit_payments', 'delete_payments', 'update_payment_status')
AND r.guard_name = 'web'
ON DUPLICATE KEY UPDATE role_id = role_id;

-- Assign payment permissions to demo_admin role
INSERT INTO role_has_permissions (role_id, permission_id)
SELECT r.id, p.id 
FROM roles r, permissions p 
WHERE r.name = 'demo_admin' 
AND p.name IN ('view_payments', 'add_payments', 'edit_payments', 'delete_payments', 'update_payment_status')
AND r.guard_name = 'web'
ON DUPLICATE KEY UPDATE role_id = role_id;

-- Assign limited payment permissions to receptionist role
INSERT INTO role_has_permissions (role_id, permission_id)
SELECT r.id, p.id 
FROM roles r, permissions p 
WHERE r.name = 'receptionist' 
AND p.name IN ('view_payments', 'update_payment_status')
AND r.guard_name = 'web'
ON DUPLICATE KEY UPDATE role_id = role_id;
