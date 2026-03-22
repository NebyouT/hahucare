-- Insert FAQ permissions
INSERT INTO permissions (name, is_fixed, created_at, updated_at) VALUES 
('view_faqs', 1, NOW(), NOW()),
('add_faqs', 1, NOW(), NOW()),
('edit_faqs', 1, NOW(), NOW()),
('delete_faqs', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE name=name;

-- Assign permissions to admin role
INSERT INTO role_has_permissions (role_id, permission_id)
SELECT r.id, p.id 
FROM roles r 
CROSS JOIN permissions p 
WHERE r.name = 'admin' AND p.name IN ('view_faqs', 'add_faqs', 'edit_faqs', 'delete_faqs')
ON DUPLICATE KEY UPDATE role_id=role_id;

-- Assign permissions to demo_admin role
INSERT INTO role_has_permissions (role_id, permission_id)
SELECT r.id, p.id 
FROM roles r 
CROSS JOIN permissions p 
WHERE r.name = 'demo_admin' AND p.name IN ('view_faqs', 'add_faqs', 'edit_faqs', 'delete_faqs')
ON DUPLICATE KEY UPDATE role_id=role_id;
