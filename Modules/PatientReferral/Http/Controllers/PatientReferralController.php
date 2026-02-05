<?php

namespace Modules\PatientReferral\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\PatientReferral\Models\PatientReferral;
use Modules\Appointment\Models\Appointment;
use Illuminate\Support\Facades\Auth; // <-- ADD THIS IMPORT

class PatientReferralController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            abort(401, 'Please login to access this page.');
        }
        
        $user = Auth::user();
        
        if ($user->user_type === 'doctor') {
            // Doctors can only see referrals where they are the referred_to doctor
            $referrals = PatientReferral::with(['patient', 'referredByDoctor', 'referredToDoctor'])
                ->where('referred_to', $user->id)
                ->latest()
                ->paginate(10);
        } else {
            // Admins and other roles can see all referrals
            $referrals = PatientReferral::with(['patient', 'referredByDoctor', 'referredToDoctor'])
                ->latest()
                ->paginate(10);
        }

        return view('patientreferral::backend.index', compact('referrals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            abort(401, 'Please login to access this page.');
        }
        
        $user = Auth::user();
        
        if ($user->user_type === 'doctor') {
            // For doctors: get all patients, but only themselves as referred_by
            $patients = \App\Models\User::where('user_type', 'user')
                ->where('status', 1)
                ->select('id', 'first_name', 'last_name', 'email')
                ->get();

            // Only the logged-in doctor can be selected as referred_by
            $doctors = collect([$user]); // Collection with only the current doctor
            
            // For referred_to, show all doctors except themselves
            $referredToDoctors = \App\Models\User::where('user_type', 'doctor')
                ->where('status', 1)
                ->where('id', '!=', $user->id)
                ->select('id', 'first_name', 'last_name', 'email')
                ->get();

            return view('patientreferral::backend.create', compact('patients', 'doctors', 'referredToDoctors'));
        } else {
            // For admins and other roles: show all patients and doctors
            $patients = \App\Models\User::where('user_type', 'user')
                ->where('status', 1)
                ->select('id', 'first_name', 'last_name', 'email')
                ->get();

            $doctors = \App\Models\User::where('user_type', 'doctor')
                ->where('status', 1)
                ->select('id', 'first_name', 'last_name', 'email')
                ->get();

            return view('patientreferral::backend.create', compact('patients', 'doctors'));
        }
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
                'status' => 'required|in:pending,accepted,rejected',
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
                'status' => 'required|in:pending,accepted,rejected',
                'referral_date' => 'required|date',
            ], [
                'referred_to.different' => 'The "Referred To" doctor must be different from the "Referred By" doctor.'
            ]);
        }

        PatientReferral::create($validated);
        
        return redirect()->route('backend.patientreferral.index')
            ->with('success', 'Referral created successfully');
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
        
        return view('patientreferral::backend.show', compact('referral'));
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
            // For doctors: get all patients, but only themselves as referred_by
            $patients = \App\Models\User::where('user_type', 'user')
                ->where('status', 1)
                ->select('id', 'first_name', 'last_name', 'email')
                ->get();

            // Only the logged-in doctor can be selected as referred_by
            $doctors = collect([$user]);
            
            // For referred_to, show all doctors except themselves
            $referredToDoctors = \App\Models\User::where('user_type', 'doctor')
                ->where('status', 1)
                ->where('id', '!=', $user->id)
                ->select('id', 'first_name', 'last_name', 'email')
                ->get();

            return view('patientreferral::backend.edit', compact('patientReferral', 'patients', 'doctors', 'referredToDoctors'));
        } else {
            // For admins and other roles: show all patients and doctors
            $patients = \App\Models\User::where('user_type', 'user')
                ->where('status', 1)
                ->select('id', 'first_name', 'last_name', 'email')
                ->get();

            $doctors = \App\Models\User::where('user_type', 'doctor')
                ->where('status', 1)
                ->select('id', 'first_name', 'last_name', 'email')
                ->get();

            return view('patientreferral::backend.edit', compact('patientReferral', 'patients', 'doctors'));
        }
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
            
            // Check if referral is already accepted
            if ($referral->status !== 'accepted') {
                return redirect()->route('backend.patientreferral.show', $referral)
                    ->with('error', 'This referral must be accepted before booking an appointment.');
            }
            
            // Get data needed for appointment booking
            $clinics = \Modules\Clinic\Models\Clinics::where('status', 1)->get();
            $services = \Modules\Clinic\Models\ClinicsService::where('status', 1)->get();
            
            // Get the doctor's clinic information
            $doctorClinicMapping = \Modules\Clinic\Models\DoctorClinicMapping::where('doctor_id', $referral->referred_to)
                ->with('clinics')
                ->first();
                
            $doctorClinic = null;
            if ($doctorClinicMapping && $doctorClinicMapping->clinics) {
                $doctorClinic = $doctorClinicMapping->clinics;
            }
            
            // Debug: Log the data
            \Log::info('Referral booking data', [
                'referral_id' => $referral->id,
                'doctor_id' => $referral->referred_to,
                'clinic_id' => $doctorClinic ? $doctorClinic->id : null,
                'clinics_count' => $clinics->count(),
                'services_count' => $services->count()
            ]);
            
            return view('patientreferral::backend.book_appointment', compact('referral', 'clinics', 'services', 'doctorClinic'));
            
        } catch (\Exception $e) {
            \Log::error('Referral booking error: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a simple error view for debugging
            return response()->view('errors.500', [
                'message' => 'Error loading booking page: ' . $e->getMessage()
            ], 500);
        }
    }

// Modules/PatientReferral/Http/Controllers/PatientReferralController.php

public function __construct()
{
    $this->middleware('permission:view_patient_referral')->only(['index', 'show']);
    $this->middleware('permission:add_patient_referral')->only(['create', 'store']);
    $this->middleware('permission:edit_patient_referral')->only(['edit', 'update', 'acceptReferral', 'bookAppointment']);
    $this->middleware('permission:delete_patient_referral')->only(['destroy']);
}

}