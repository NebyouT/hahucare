-- Restrict Lab Technician to ONLY view lab orders
-- This removes all other lab permissions and hardcodes the access

-- First, get the lab_technician role ID
SET @lab_tech_role_id = (SELECT id FROM roles WHERE name = 'lab_technician');

-- Remove ALL lab permissions from lab_technician
DELETE FROM role_has_permissions 
WHERE role_id = @lab_tech_role_id 
AND permission_id IN (
    SELECT id FROM permissions 
    WHERE name LIKE 'lab_%' OR name = 'order_lab_tests'
);

-- Add ONLY view_lab_orders permission to lab_technician
INSERT INTO role_has_permissions (permission_id, role_id)
SELECT p.id, @lab_tech_role_id
FROM permissions p
WHERE p.name = 'view_lab_orders'
AND NOT EXISTS (
    SELECT 1 FROM role_has_permissions 
    WHERE permission_id = p.id AND role_id = @lab_tech_role_id
);

-- Verify the permissions
SELECT r.name as role, p.name as permission
FROM role_has_permissions rp
JOIN roles r ON rp.role_id = r.id
JOIN permissions p ON rp.permission_id = p.id
WHERE r.name = 'lab_technician';
