-- Fix Google OAuth Settings in Database
-- Run these SQL queries to update your database settings

-- 1. Check current Google settings
SELECT name, val, type 
FROM settings 
WHERE name IN ('google_meet_method', 'google_clientid', 'google_secret_key', 'is_zoom')
ORDER BY name;

-- 2. Update Google Client ID
UPDATE settings 
SET val = '864058558222-272i0ut7kc4nl5peim49on08h7aoko23.apps.googleusercontent.com' 
WHERE name = 'google_clientid';

-- 3. Update Google Client Secret
UPDATE settings 
SET val = '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' 
WHERE name = 'google_secret_key';

-- 4. Enable Google Meet
UPDATE settings 
SET val = '1' 
WHERE name = 'google_meet_method';

-- 5. Disable Zoom (optional, to prioritize Google Meet)
UPDATE settings 
SET val = '0' 
WHERE name = 'is_zoom';

-- 6. Verify the updates
SELECT name, val, type 
FROM settings 
WHERE name IN ('google_meet_method', 'google_clientid', 'google_secret_key', 'is_zoom')
ORDER BY name;
