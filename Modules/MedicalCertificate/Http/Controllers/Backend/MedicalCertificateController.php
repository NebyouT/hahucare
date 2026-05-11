<?php

namespace Modules\MedicalCertificate\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\MedicalCertificate\Models\MedicalCertificate;
use Yajra\DataTables\DataTables;

class MedicalCertificateController extends Controller
{
    use Authorizable;

    public function __construct()
    {
        $this->module_title = 'medicalcertificate.title';
        $this->module_name = 'medicalcertificate';
        $this->module_icon = 'fa-solid fa-file-medical';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
    }

    public function index(Request $request)
    {
        $module_action = 'List';
        return view('medicalcertificate::backend.medical-certificates.index', compact('module_action'));
    }

    public function index_data(Request $request)
    {
        $query = MedicalCertificate::with(['patient', 'doctor', 'encounter', 'clinic']);

        if (auth()->user()->hasRole('doctor')) {
            $query->where('doctor_id', auth()->id());
        }

        if (auth()->user()->hasRole('vendor')) {
            $clinicId = auth()->user()->clinic_id;
            $query->where('clinic_id', $clinicId);
        }

        $data = $query->latest()->get();

        return DataTables::of($data)
            ->addColumn('action', function ($data) {
                return view('medicalcertificate::backend.medical-certificates.action_column', compact('data'));
            })
            ->editColumn('patient_id', function ($data) {
                return $data->patient ? $data->patient->full_name : 'N/A';
            })
            ->editColumn('doctor_id', function ($data) {
                return $data->doctor ? $data->doctor->full_name : 'N/A';
            })
            ->editColumn('certificate_type', function ($data) {
                return ucfirst(str_replace('_', ' ', $data->certificate_type));
            })
            ->editColumn('issue_date', function ($data) {
                return $data->issue_date ? $data->issue_date->format('Y-m-d') : 'N/A';
            })
            ->editColumn('status', function ($data) {
                $statusClass = match($data->status) {
                    'issued' => 'badge-primary',
                    'printed' => 'badge-success',
                    'cancelled' => 'badge-danger',
                    default => 'badge-secondary'
                };
                return '<span class="badge ' . $statusClass . '">' . ucfirst($data->status) . '</span>';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function create()
    {
        $module_action = 'Create';
        return view('medicalcertificate::backend.medical-certificates.create', compact('module_action'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:users,id',
            'encounter_id' => 'nullable|exists:encounters,id',
            'certificate_type' => 'required|in:medical_leave,fitness,recovery,other',
            'issue_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'diagnosis' => 'nullable|string',
            'reason' => 'required|string',
            'recommendations' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $durationDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;

        $medicalCertificate = MedicalCertificate::create([
            'patient_id' => $request->patient_id,
            'doctor_id' => auth()->id(),
            'encounter_id' => $request->encounter_id,
            'clinic_id' => auth()->user()->clinic_id,
            'certificate_number' => MedicalCertificate::generateCertificateNumber(),
            'certificate_type' => $request->certificate_type,
            'issue_date' => $request->issue_date,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'duration_days' => $durationDays,
            'diagnosis' => $request->diagnosis,
            'reason' => $request->reason,
            'recommendations' => $request->recommendations,
            'notes' => $request->notes,
            'status' => 'issued',
            'is_printed' => false,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => __('messages.save_form', ['form' => __('medicalcertificate.medical_certificate')]),
            'status' => true,
        ]);
    }

    public function show($id)
    {
        $medicalCertificate = MedicalCertificate::with(['patient', 'doctor', 'encounter', 'clinic', 'createdBy'])->findOrFail($id);
        $module_action = 'Show';
        return view('medicalcertificate::backend.medical-certificates.show', compact('medicalCertificate', 'module_action'));
    }

    public function edit($id)
    {
        $medicalCertificate = MedicalCertificate::findOrFail($id);
        $module_action = 'Edit';
        return view('medicalcertificate::backend.medical-certificates.edit', compact('medicalCertificate', 'module_action'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'certificate_type' => 'required|in:medical_leave,fitness,recovery,other',
            'issue_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'diagnosis' => 'nullable|string',
            'reason' => 'required|string',
            'recommendations' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $medicalCertificate = MedicalCertificate::findOrFail($id);

        $durationDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;

        $medicalCertificate->update([
            'certificate_type' => $request->certificate_type,
            'issue_date' => $request->issue_date,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'duration_days' => $durationDays,
            'diagnosis' => $request->diagnosis,
            'reason' => $request->reason,
            'recommendations' => $request->recommendations,
            'notes' => $request->notes,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => __('messages.update_form', ['form' => __('medicalcertificate.medical_certificate')]),
            'status' => true,
        ]);
    }

    public function destroy($id)
    {
        $medicalCertificate = MedicalCertificate::findOrFail($id);
        $medicalCertificate->update(['deleted_by' => auth()->id()]);
        $medicalCertificate->delete();

        return response()->json([
            'message' => __('messages.delete_form', ['form' => __('medicalcertificate.medical_certificate')]),
            'status' => true,
        ]);
    }

    public function print($id)
    {
        $medicalCertificate = MedicalCertificate::with(['patient', 'doctor', 'encounter', 'clinic'])->findOrFail($id);
        
        $medicalCertificate->update([
            'is_printed' => true,
            'printed_at' => now(),
            'status' => 'printed',
        ]);

        $pdf = \PDF::loadView('medicalcertificate::backend.medical-certificates.print', compact('medicalCertificate'));
        return $pdf->download('medical_certificate_' . $medicalCertificate->certificate_number . '.pdf');
    }

    public function createFromEncounter($encounter_id)
    {
        $encounter = \Modules\Appointment\Models\Encounter::with(['patient', 'doctor', 'clinic'])->findOrFail($encounter_id);
        $module_action = 'Create';
        return view('medicalcertificate::backend.medical-certificates.create-from-encounter', compact('encounter', 'module_action'));
    }

    public function storeFromEncounter(Request $request, $encounter_id)
    {
        $request->validate([
            'certificate_type' => 'required|in:medical_leave,fitness,recovery,other',
            'issue_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'diagnosis' => 'nullable|string',
            'reason' => 'required|string',
            'recommendations' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $encounter = \Modules\Appointment\Models\Encounter::findOrFail($encounter_id);

        $durationDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;

        $medicalCertificate = MedicalCertificate::create([
            'patient_id' => $encounter->patient_id,
            'doctor_id' => auth()->id(),
            'encounter_id' => $encounter_id,
            'clinic_id' => $encounter->clinic_id,
            'certificate_number' => MedicalCertificate::generateCertificateNumber(),
            'certificate_type' => $request->certificate_type,
            'issue_date' => $request->issue_date,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'duration_days' => $durationDays,
            'diagnosis' => $request->diagnosis,
            'reason' => $request->reason,
            'recommendations' => $request->recommendations,
            'notes' => $request->notes,
            'status' => 'issued',
            'is_printed' => false,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => __('messages.save_form', ['form' => __('medicalcertificate.medical_certificate')]),
            'status' => true,
        ]);
    }
}
