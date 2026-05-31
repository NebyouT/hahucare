<?php
namespace Modules\PatientReferral\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\PatientReferral\Models\PatientReferral;
use Modules\Appointment\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use PDF;

class PatientReferralController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::check()) {
            abort(401, 'Please login to access this page.');
        }

        $user = Auth::user();
        $clinicDoctorIds = [];
        $clinicId = null;

        if (in_array($user->user_type, ['doctor', 'receptionist']) || $user->hasRole('doctor') || $user->hasRole('receptionist')) {
            // Get the user's clinic ID and all doctors in that clinic
            if ($user->hasRole('receptionist')) {
                $record = \Modules\Clinic\Models\Receptionist::where('receptionist_id', $user->id)->first();
                if ($record) {
                    $clinicId = $record->clinic_id;
                }
            } else {
                // Doctor or other clinic-based role
                $mapping = \Modules\Clinic\Models\DoctorClinicMapping::where('doctor_id', $user->id)->first();
                if ($mapping) {
                    $clinicId = $mapping->clinic_id;
                }
            }

            if ($clinicId) {
                $clinicDoctorIds = \Modules\Clinic\Models\DoctorClinicMapping::where('clinic_id', $clinicId)
                    ->pluck('doctor_id')
                    ->toArray();
            }

            // Only show referrals where at least one doctor (referred_by or referred_to) is in this clinic
            $allClinicReferrals = PatientReferral::with(['patient', 'referredByDoctor', 'referredToDoctor'])
                ->where(function ($q) use ($clinicDoctorIds) {
                    $q->whereIn('referred_by', $clinicDoctorIds)
                      ->orWhereIn('referred_to', $clinicDoctorIds);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            // In Referral: referred_to doctor is in our clinic (incoming / internal)
            $inReferrals = $allClinicReferrals->filter(function ($r) use ($clinicDoctorIds) {
                return in_array($r->referred_to, $clinicDoctorIds);
            });

            // Out Referral: referred_by is in our clinic AND referred_to is NOT in our clinic
            $outReferrals = $allClinicReferrals->filter(function ($r) use ($clinicDoctorIds) {
                return in_array($r->referred_by, $clinicDoctorIds) && !in_array($r->referred_to, $clinicDoctorIds);
            });

            // Items where both are in the same clinic go ONLY to In Referral (already handled above)
        } else {
            // Admin / demo_admin: show all referrals, no clinic filter
            $allReferrals = PatientReferral::with(['patient', 'referredByDoctor', 'referredToDoctor'])
                ->orderBy('created_at', 'desc')
                ->get();

            $inReferrals = $allReferrals;
            $outReferrals = $allReferrals;
        }

        return view('patientreferral::backend.index', compact('inReferrals', 'outReferrals', 'clinicDoctorIds'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::check()) {
            abort(401, 'Please login to access this page.');
        }

        $user = Auth::user();
        $patients = \App\Models\User::where('user_type', 'user')
            ->where('status', 1)
            ->select('id', 'first_name', 'last_name', 'email')
            ->get();

        if ($user->user_type === 'doctor') {
            // Only the logged-in doctor can be selected as referred_by
            $doctors = collect([$user]);

            $referredToDoctors = \App\Models\User::where('user_type', 'doctor')
                ->where('status', 1)
                ->where('id', '!=', $user->id)
                ->select('id', 'first_name', 'last_name', 'email')
                ->get();

            return view('patientreferral::backend.create', compact('patients', 'doctors', 'referredToDoctors'));
        }

        if ($user->hasRole('receptionist')) {
            // Receptionist: only doctors from their clinic can be selected as referred_by
            $receptionist = \Modules\Clinic\Models\Receptionist::where('receptionist_id', $user->id)->first();
            $clinicDoctorIds = [];
            if ($receptionist) {
                $clinicDoctorIds = \Modules\Clinic\Models\DoctorClinicMapping::where('clinic_id', $receptionist->clinic_id)
                    ->pluck('doctor_id')
                    ->toArray();
            }

            $doctors = \App\Models\User::where('user_type', 'doctor')
                ->where('status', 1)
                ->whereIn('id', $clinicDoctorIds)
                ->select('id', 'first_name', 'last_name', 'email')
                ->get();

            // For referred_to, show all doctors
            $referredToDoctors = \App\Models\User::where('user_type', 'doctor')
                ->where('status', 1)
                ->select('id', 'first_name', 'last_name', 'email')
                ->get();

            return view('patientreferral::backend.create', compact('patients', 'doctors', 'referredToDoctors'));
        }

        // For admins and other roles: show all patients and doctors
        $doctors = \App\Models\User::where('user_type', 'doctor')
            ->where('status', 1)
            ->select('id', 'first_name', 'last_name', 'email')
            ->get();

        return view('patientreferral::backend.create', compact('patients', 'doctors'));
    }

    /**
     * Show the form for creating a new advanced referral.
     */
    public function createAdvanced()
    {
        if (!Auth::check()) {
            abort(401, 'Please login to access this page.');
        }

        $user = Auth::user();
        
        $patients = \App\Models\User::where('user_type', 'user')
            ->where('status', 1)
            ->select('id', 'first_name', 'last_name', 'email')
            ->get();

        $allDoctors = \App\Models\User::where('user_type', 'doctor')
            ->where('status', 1)
            ->select('id', 'first_name', 'last_name', 'email')
            ->get();

        if ($user->hasRole('receptionist')) {
            $receptionist = \Modules\Clinic\Models\Receptionist::where('receptionist_id', $user->id)->first();
            $clinicDoctorIds = [];
            if ($receptionist) {
                $clinicDoctorIds = \Modules\Clinic\Models\DoctorClinicMapping::where('clinic_id', $receptionist->clinic_id)
                    ->pluck('doctor_id')
                    ->toArray();
            }
            $doctors = $allDoctors->whereIn('id', $clinicDoctorIds);
        } else {
            $doctors = $allDoctors;
        }

        return view('patientreferral::backend.create_advanced', compact('patients', 'doctors', 'allDoctors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            abort(401, 'Please login to access this page.');
        }
        
        $user = Auth::user();
        
        if ($user->user_type === 'doctor') {
            // For doctors: validate without referred_by (it's fixed)
            $validated = $request->validate([
                'patient_id' => 'required|exists:users,id',
                'referred_to' => 'required|exists:users,id|different:referred_by',
                'reason' => 'required|string',
                'notes' => 'nullable|string',
                'referral_date' => 'required|date',
            ], [
                'referred_to.different' => 'The "Referred To" doctor must be different from your own profile.'
            ]);

            // Set referred_by to current doctor's ID
            $validated['referred_by'] = $user->id;
        } else {
            // For admins and other roles: normal validation
            $validated = $request->validate([
                'patient_id' => 'required|exists:users,id',
                'referred_by' => 'required|exists:users,id',
                'referred_to' => 'required|exists:users,id|different:referred_by',
                'reason' => 'required|string',
                'notes' => 'nullable|string',
                'referral_date' => 'required|date',
            ], [
                'referred_to.different' => 'The "Referred To" doctor must be different from the "Referred By" doctor.'
            ]);
        }

        // Default new quick referrals to pending status
        $validated['status'] = 'pending';

        // Set referral_type to quick for standard referrals
        $validated['referral_type'] = 'quick';
        
        PatientReferral::create($validated);
        
        return redirect()->route('backend.patientreferral.index')
            ->with('success', 'Referral created successfully');
    }

    /**
     * Store a newly created advanced referral in storage.
     */
    public function storeAdvanced(Request $request)
    {
        if (!Auth::check()) {
            abort(401, 'Please login to access this page.');
        }
        
        $validated = $request->validate([
            'patient_id' => 'required|exists:users,id',
            'referred_by' => 'required|exists:users,id',
            'referred_to' => 'required|exists:users,id|different:referred_by',
            'referral_date' => 'required|date',
            'referral_type' => 'required|in:quick,advanced',
            'patient_age' => 'nullable|integer',
            'patient_sex' => 'nullable|string|max:20',
            'patient_address' => 'nullable|string',
            'referring_faculty' => 'nullable|string|max:255',
            'receiving_faculty' => 'nullable|string|max:255',
            'chief_complaint' => 'required|string',
            'history_findings' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment_given' => 'nullable|string',
            'investigation_done' => 'nullable|string',
            'referring_clinic_name' => 'nullable|string|max:255',
            'contact_information' => 'nullable|string',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
            'encounter_ids' => 'nullable|array',
            'encounter_ids.*' => 'exists:patient_encounters,id',
        ]);

        // Generate unique referral code
        $validated['referral_code'] = 'REF' . date('Ymd') . str_pad(PatientReferral::count() + 1, 4, '0', STR_PAD_LEFT);
        $validated['status'] = 'pending';
        $validated['encounter_ids'] = $request->input('encounter_ids', []);

        $referral = PatientReferral::create($validated);
        
        // Sync encounters to pivot table
        if (!empty($validated['encounter_ids'])) {
            $referral->encounters()->sync($validated['encounter_ids']);
        }
        
        return redirect()->route('backend.patientreferral.index')
            ->with('success', 'Advanced referral created successfully');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            abort(401, 'Please login to access this page.');
        }
        
        $user = Auth::user();
        $referral = PatientReferral::with(['patient', 'referredByDoctor', 'referredToDoctor'])
            ->findOrFail($id);
        
        // Check permissions: doctors can only view referrals where they are the referred_to doctor
        if ($user->user_type === 'doctor' && $referral->referred_to !== $user->id) {
            abort(403, 'You are not authorized to view this referral.');
        }
        
        $clinicDoctorIds = [];
        if ($user->hasRole('receptionist')) {
            $receptionist = \Modules\Clinic\Models\Receptionist::where('receptionist_id', $user->id)->first();
            if ($receptionist) {
                $clinicDoctorIds = \Modules\Clinic\Models\DoctorClinicMapping::where('clinic_id', $receptionist->clinic_id)
                    ->pluck('doctor_id')
                    ->toArray();
            }
        }

        return view('patientreferral::backend.show', compact('referral', 'clinicDoctorIds'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            abort(401, 'Please login to access this page.');
        }
        
        $user = Auth::user();
        $patientReferral = PatientReferral::findOrFail($id);
        
        // Check permissions: doctors can only edit referrals where they are the referred_to doctor
        if ($user->user_type === 'doctor' && $patientReferral->referred_to !== $user->id) {
            abort(403, 'You are not authorized to edit this referral.');
        }
        
        if ($user->user_type === 'doctor') {
            $patients = \App\Models\User::where('user_type', 'user')
                ->where('status', 1)
                ->select('id', 'first_name', 'last_name', 'email')
                ->get();

            $doctors = collect([$user]);
            
            $referredToDoctors = \App\Models\User::where('user_type', 'doctor')
                ->where('status', 1)
                ->where('id', '!=', $user->id)
                ->select('id', 'first_name', 'last_name', 'email')
                ->get();

            return view('patientreferral::backend.edit', compact('patientReferral', 'patients', 'doctors', 'referredToDoctors'));
        }

        $patients = \App\Models\User::where('user_type', 'user')
            ->where('status', 1)
            ->select('id', 'first_name', 'last_name', 'email')
            ->get();

        $allDoctors = \App\Models\User::where('user_type', 'doctor')
            ->where('status', 1)
            ->select('id', 'first_name', 'last_name', 'email')
            ->get();

        if ($user->hasRole('receptionist')) {
            $receptionist = \Modules\Clinic\Models\Receptionist::where('receptionist_id', $user->id)->first();
            $clinicDoctorIds = [];
            if ($receptionist) {
                $clinicDoctorIds = \Modules\Clinic\Models\DoctorClinicMapping::where('clinic_id', $receptionist->clinic_id)
                    ->pluck('doctor_id')
                    ->toArray();
            }
            $doctors = $allDoctors->whereIn('id', $clinicDoctorIds);
        } else {
            $doctors = $allDoctors;
        }

        return view('patientreferral::backend.edit', compact('patientReferral', 'patients', 'doctors', 'allDoctors'));
    }

    /**
     * Show the form for editing the specified advanced referral.
     */
    public function editAdvanced($id)
    {
        if (!Auth::check()) {
            abort(401, 'Please login to access this page.');
        }

        $user = Auth::user();
        $referral = PatientReferral::findOrFail($id);
        
        $patients = \App\Models\User::where('user_type', 'user')
            ->where('status', 1)
            ->select('id', 'first_name', 'last_name', 'email')
            ->get();

        $allDoctors = \App\Models\User::where('user_type', 'doctor')
            ->where('status', 1)
            ->select('id', 'first_name', 'last_name', 'email')
            ->get();

        if ($user->hasRole('receptionist')) {
            $receptionist = \Modules\Clinic\Models\Receptionist::where('receptionist_id', $user->id)->first();
            $clinicDoctorIds = [];
            if ($receptionist) {
                $clinicDoctorIds = \Modules\Clinic\Models\DoctorClinicMapping::where('clinic_id', $receptionist->clinic_id)
                    ->pluck('doctor_id')
                    ->toArray();
            }
            $doctors = $allDoctors->whereIn('id', $clinicDoctorIds);
        } else {
            $doctors = $allDoctors;
        }

        return view('patientreferral::backend.edit_advanced', compact('referral', 'patients', 'doctors', 'allDoctors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            abort(401, 'Please login to access this page.');
        }
        
        $user = Auth::user();
        $patientReferral = PatientReferral::findOrFail($id);
        
        // Check permissions: doctors can only update referrals where they are the referred_to doctor
        if ($user->user_type === 'doctor' && $patientReferral->referred_to !== $user->id) {
            abort(403, 'You are not authorized to update this referral.');
        }
        
        if ($user->user_type === 'doctor') {
            // For doctors: validate without referred_by (it's fixed)
            $validated = $request->validate([
                'patient_id' => 'required|exists:users,id',
                'referred_to' => 'required|exists:users,id|different:referred_by',
                'reason' => 'required|string',
                'notes' => 'nullable|string',
                'status' => 'required|in:pending,accepted,rejected',
                'referral_date' => 'required|date',
            ]);

            // Don't allow changing referred_by for doctors
            $validated['referred_by'] = $user->id;
        } else {
            // For admins and other roles: normal validation
            $validated = $request->validate([
                'patient_id' => 'required|exists:users,id',
                'referred_by' => 'required|exists:users,id',
                'referred_to' => 'required|exists:users,id|different:referred_by',
                'reason' => 'required|string',
                'notes' => 'nullable|string',
                'status' => 'required|in:pending,accepted,rejected',
                'referral_date' => 'required|date',
            ]);
        }
        
        $patientReferral->update($validated);
        
        return redirect()->route('backend.patientreferral.index')
            ->with('success', 'Referral updated successfully');
    }

    /**
     * Update the specified advanced referral in storage.
     */
    public function updateAdvanced(Request $request, $id): RedirectResponse
    {
        if (!Auth::check()) {
            abort(401, 'Please login to access this page.');
        }
        
        $referral = PatientReferral::findOrFail($id);
        
        $validated = $request->validate([
            'patient_id' => 'required|exists:users,id',
            'referred_by' => 'required|exists:users,id',
            'referred_to' => 'required|exists:users,id|different:referred_by',
            'referral_date' => 'required|date',
            'patient_age' => 'nullable|integer',
            'patient_sex' => 'nullable|string|max:20',
            'patient_address' => 'nullable|string',
            'referring_faculty' => 'nullable|string|max:255',
            'receiving_faculty' => 'nullable|string|max:255',
            'chief_complaint' => 'required|string',
            'history_findings' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment_given' => 'nullable|string',
            'investigation_done' => 'nullable|string',
            'referring_clinic_name' => 'nullable|string|max:255',
            'contact_information' => 'nullable|string',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
            'encounter_ids' => 'nullable|array',
            'encounter_ids.*' => 'exists:patient_encounters,id',
        ]);

        $validated['encounter_ids'] = $request->input('encounter_ids', []);
        
        $referral->update($validated);
        
        // Sync encounters to pivot table
        $referral->encounters()->sync($validated['encounter_ids']);
        
        return redirect()->route('backend.patientreferral.index')
            ->with('success', 'Advanced referral updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            abort(401, 'Please login to access this page.');
        }
        
        $user = Auth::user();
        $patientReferral = PatientReferral::findOrFail($id);
        
        // Check permissions: doctors can only delete referrals where they are the referred_to doctor
        if ($user->user_type === 'doctor' && $patientReferral->referred_to !== $user->id) {
            abort(403, 'You are not authorized to delete this referral.');
        }
        
        $patientReferral->delete();
        
        return redirect()->route('backend.patientreferral.index')
            ->with('success', 'Referral deleted successfully');
    }

    /**
     * Accept a referral and create an appointment.
     */
    public function acceptReferral($id): RedirectResponse
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            abort(401, 'Please login to access this page.');
        }
        
        $user = Auth::user();
        $referral = PatientReferral::findOrFail($id);
        
        // Check permissions: only the referred_to doctor can accept the referral, unless user is admin/demo_admin
        if ($user->user_type === 'doctor' && $referral->referred_to !== $user->id) {
            abort(403, 'You are not authorized to accept this referral.');
        }
        
        // For receptionists: only allow if the referred_to doctor is in their clinic
        if ($user->hasRole('receptionist')) {
            $receptionist = \Modules\Clinic\Models\Receptionist::where('receptionist_id', $user->id)->first();
            if (!$receptionist) {
                abort(403, 'You are not associated with any clinic.');
            }
            $clinicDoctorIds = \Modules\Clinic\Models\DoctorClinicMapping::where('clinic_id', $receptionist->clinic_id)
                ->pluck('doctor_id')
                ->toArray();
            if (!in_array($referral->referred_to, $clinicDoctorIds)) {
                abort(403, 'You are not authorized to accept this referral.');
            }
        }
        
        // Check if referral is already accepted
        if ($referral->status === 'accepted') {
            return redirect()->route('backend.patientreferral.index')
                ->with('info', 'This referral has already been accepted.');
        }
        
        // Update referral status to accepted
        $referral->status = 'accepted';
        $referral->save();
        
        // Redirect to appointment booking page for this referral
        return redirect()->route('backend.patientreferral.book', $referral->id)
            ->with('success', 'Referral accepted. Please complete the appointment booking.');
    }

    /**
     * Reject a referral.
     */
    public function rejectReferral($id): RedirectResponse
    {
        if (!Auth::check()) {
            abort(401, 'Please login to access this page.');
        }

        $user = Auth::user();
        $referral = PatientReferral::findOrFail($id);

        // Only in-referral pending referrals can be rejected by authorized users
        if ($user->user_type === 'doctor' && $referral->referred_to !== $user->id) {
            abort(403, 'You are not authorized to reject this referral.');
        }

        if ($user->hasRole('receptionist')) {
            $receptionist = \Modules\Clinic\Models\Receptionist::where('receptionist_id', $user->id)->first();
            if (!$receptionist) {
                abort(403, 'You are not associated with any clinic.');
            }
            $clinicDoctorIds = \Modules\Clinic\Models\DoctorClinicMapping::where('clinic_id', $receptionist->clinic_id)
                ->pluck('doctor_id')
                ->toArray();
            if (!in_array($referral->referred_to, $clinicDoctorIds)) {
                abort(403, 'You are not authorized to reject this referral.');
            }
        }

        if ($referral->status !== 'pending') {
            return redirect()->route('backend.patientreferral.index')
                ->with('info', 'This referral has already been processed.');
        }

        $referral->status = 'rejected';
        $referral->save();

        return redirect()->route('backend.patientreferral.index')
            ->with('success', 'Referral rejected successfully.');
    }

    /**
     * Show appointment booking form for referral.
     */
    public function bookAppointment($id)
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                abort(401, 'Please login to access this page.');
            }
            
            $user = Auth::user();
            $referral = PatientReferral::findOrFail($id);
            
            // Check permissions: only the referred_to doctor can book, unless user is admin/demo_admin
            if ($user->user_type === 'doctor' && $referral->referred_to !== $user->id) {
                abort(403, 'You are not authorized to book this referral.');
            }
            
            // For receptionists: only allow if the referred_to doctor is in their clinic
            if ($user->hasRole('receptionist')) {
                $receptionist = \Modules\Clinic\Models\Receptionist::where('receptionist_id', $user->id)->first();
                if (!$receptionist) {
                    abort(403, 'You are not associated with any clinic.');
                }
                $clinicDoctorIds = \Modules\Clinic\Models\DoctorClinicMapping::where('clinic_id', $receptionist->clinic_id)
                    ->pluck('doctor_id')
                    ->toArray();
                if (!in_array($referral->referred_to, $clinicDoctorIds)) {
                    abort(403, 'You are not authorized to book this referral.');
                }
            }
            
            // Check if referral is already accepted
            if ($referral->status !== 'accepted') {
                return redirect()->route('backend.patientreferral.show', $referral)
                    ->with('error', 'This referral must be accepted before booking an appointment.');
            }
            
            // Get the doctor's clinic
            $doctorClinicMapping = \Modules\Clinic\Models\DoctorClinicMapping::where('doctor_id', $referral->referred_to)
                ->with('clinics')
                ->first();

            $doctorClinic = $doctorClinicMapping?->clinics ?? null;

            if (!$doctorClinic) {
                return redirect()->route('backend.patientreferral.show', $referral)
                    ->with('error', 'No clinic found for the referred doctor. Please assign the doctor to a clinic first.');
            }

            return view('patientreferral::backend.book_appointment', compact('referral', 'doctorClinic'));

        } catch (\Exception $e) {
            \Log::error('Referral booking error: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->route('backend.patientreferral.show', $id)
                ->with('error', 'Error loading booking page: ' . $e->getMessage());
        }
    }

    /**
     * Get patient data for advanced referral form (API endpoint).
     */
    public function getPatientData($patientId)
    {
        $patient = \App\Models\User::where('id', $patientId)
            ->where('user_type', 'user')
            ->first();

        if (!$patient) {
            return response()->json(['error' => 'Patient not found'], 404);
        }

        // Calculate age from date of birth if available
        $age = null;
        if ($patient->date_of_birth) {
            $age = \Carbon\Carbon::parse($patient->date_of_birth)->age;
        }

        // Get patient encounters
        $encounters = \Modules\Appointment\Models\PatientEncounter::where('patient_id', $patientId)
            ->orderBy('encounter_date', 'desc')
            ->take(10)
            ->get(['id', 'encounter_date', 'encounter_type'])
            ->map(function ($encounter) {
                return [
                    'id' => $encounter->id,
                    'date' => $encounter->encounter_date ? $encounter->encounter_date->format('Y-m-d') : 'N/A',
                    'type' => $encounter->encounter_type ?? 'General',
                ];
            });

        return response()->json([
            'age' => $age,
            'sex' => $patient->gender ?? 'N/A',
            'address' => $patient->address ?? 'N/A',
            'encounters' => $encounters,
        ]);
    }

    /**
     * Download referral as PDF.
     */
    public function downloadPDF($id)
    {
        if (!Auth::check()) {
            abort(401, 'Please login to access this page.');
        }
        
        $referral = PatientReferral::with(['patient', 'referredByDoctor', 'referredToDoctor', 'encounters'])
            ->findOrFail($id);
        
        // Get referral stamp from settings
        $referralStamp = \App\Models\Setting::where('name', 'referral_stamp')->value('val');
        
        $pdf = \PDF::loadView('patientreferral::backend.pdf', compact('referral', 'referralStamp'));
        
        return $pdf->download('referral_' . $referral->referral_code . '.pdf');
    }
}
