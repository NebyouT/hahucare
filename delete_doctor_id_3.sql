-- STEP 1: Find the user_id from doctors table (doctors.id = 3 is from URL /doctor-details/3)
-- The 'doctor_id' column in 'doctors' table is the FK to 'users.id'
SET @userId = (SELECT doctor_id FROM doctors WHERE id = 3);

-- Verify we found a user
SELECT @userId AS user_id_to_delete;

-- STEP 2: Delete from all related tables using the user_id

-- User profile
DELETE FROM user_profiles WHERE user_id = @userId;

-- Doctor service mappings (doctor_id = user_id)
DELETE FROM doctor_service_mappings WHERE doctor_id = @userId;

-- Doctor clinic mappings
DELETE FROM doctor_clinic_mapping WHERE doctor_id = @userId;

-- Doctor documents
DELETE FROM doctor_documents WHERE doctor_id = @userId;

-- Doctor sessions
DELETE FROM doctor_session WHERE doctor_id = @userId;

-- Doctor ratings/reviews
DELETE FROM doctor_ratings WHERE doctor_id = @userId;

-- Employee commissions (doctorCommission uses employee_id = doctor_id)
DELETE FROM employee_commissions WHERE employee_id = @userId;

-- Appointments (cancel non-checkout ones first, then delete or leave cancelled)
-- Option A: Cancel pending appointments
UPDATE appointments SET status = 'cancelled' WHERE doctor_id = @userId AND status NOT IN ('checkout', 'check_in');
-- Option B: Delete all appointments for this doctor (uncomment if you want hard delete)
-- DELETE FROM appointments WHERE doctor_id = @userId;

-- Patient encounters (appointment-related)
-- DELETE FROM patient_encounters WHERE doctor_id = @userId;

-- Personal access tokens (API tokens)
DELETE FROM personal_access_tokens WHERE tokenable_id = @userId AND tokenable_type = 'App\\Models\\User';

-- Activity log (optional, uncomment to remove logs)
-- DELETE FROM activity_log WHERE causer_id = @userId;

-- The doctor record itself (doctors table)
DELETE FROM doctors WHERE id = 3;

-- Finally, delete the user record
DELETE FROM users WHERE id = @userId;
