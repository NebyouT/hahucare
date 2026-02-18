<?php

namespace Modules\Laboratory\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Laboratory\Models\Lab;
use Modules\Clinic\Models\Clinics;
use Yajra\DataTables\DataTables;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class LabController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_labs', ['only' => ['index', 'index_data']]);
        $this->middleware('permission:create_labs', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit_labs', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_labs', ['only' => ['destroy']]);
    }

    public function index()
    {
        // Debug: Check if we can reach this method
        try {
            $labCount = Lab::count();
            // Get all labs with relationships for the simple table
            $allLabs = Lab::with(['user', 'clinic'])->get();
            // Get a few sample labs for debugging
            $sampleLabs = Lab::take(3)->get(['id', 'name', 'lab_code', 'email']);
            return view('laboratory::labs.index', compact('labCount', 'sampleLabs', 'allLabs'));
        } catch (\Exception $e) {
            // Return error info for debugging
            return response()->json([
                'error' => 'Error in index method: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function index_data(Request $request)
    {
        // Simple test - return raw data first
        $labs = Lab::with(['user', 'clinic'])->get();
        
        $data = [];
        foreach ($labs as $lab) {
            $data[] = [
                'id' => $lab->id,
                'lab_code' => $lab->lab_code,
                'name' => $lab->name,
                'clinic_name' => $lab->clinic ? $lab->clinic->name : 'N/A',
                'user_name' => $lab->user ? $lab->user->first_name . ' ' . $lab->user->last_name : 'N/A',
                'phone_number' => $lab->phone_number,
                'email' => $lab->email,
                'is_active' => $lab->is_active,
                'status' => '<div class="form-check form-switch">
                    <input class="form-check-input status-toggle" type="checkbox" data-id="'.$lab->id.'" '.($lab->is_active ? 'checked' : '').'>
                </div>',
                'action' => '<div class="btn-group">
                    <a href="'.route('backend.labs.edit', $lab->id).'" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                    <button type="button" class="btn btn-sm btn-danger delete-btn" data-url="'.route('backend.labs.destroy', $lab->id).'"><i class="fas fa-trash"></i></button>
                </div>'
            ];
        }
        
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data
        ]);
    }

    public function create()
    {
        $clinics = Clinics::where('status', 1)->orderBy('name')->get();
        return view('laboratory::labs.create', compact('clinics'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'clinic_id' => 'required|exists:clinic,id',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'operating_hours' => 'nullable|array',
            'time_slot_duration' => 'nullable|integer|min:15',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        // Create lab user account
        $user = User::create([
            'first_name' => $validated['name'],
            'last_name' => 'Lab', // Default last name for lab users
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'lab_technician', // Set user_type for login compatibility
            'status' => $validated['is_active'] ?? 1,
            'email_verified_at' => now(),
            'created_by' => auth()->id(),
        ]);

        // Assign lab_technician role to the user
        $labRole = Role::where('name', 'lab_technician')->first();
        if ($labRole) {
            $user->assignRole($labRole);
        }

        // Create lab record
        $labData = [
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'clinic_id' => $validated['clinic_id'],
            'user_id' => $user->id,
            'lab_code' => 'TEMP_' . time(), // Temporary lab code
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'country' => $validated['country'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'operating_hours' => $validated['operating_hours'] ?? null,
            'time_slot_duration' => $validated['time_slot_duration'] ?? 30, // Default 30 minutes
            'is_active' => $validated['is_active'] ?? true,
            'is_featured' => $validated['is_featured'] ?? false,
            'created_by' => auth()->id(),
        ];

        $lab = Lab::create($labData);

        // Generate lab code using the lab ID
        $labCode = 'LAB' . str_pad($lab->id, 6, '0', STR_PAD_LEFT);
        $lab->lab_code = $labCode;
        $lab->save();

        return redirect()->route('backend.labs.index')
            ->with('success', 'Lab and user account created successfully. Lab Code: ' . $labCode);
    }

    public function edit($id)
    {
        $lab = Lab::findOrFail($id);
        $clinics = Clinics::where('status', 1)->orderBy('name')->get();
        return view('laboratory::labs.edit', compact('lab', 'clinics'));
    }

    public function update(Request $request, $id)
    {
        $lab = Lab::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'clinic_id' => 'required|exists:clinic,id',
            'lab_code' => 'required|string|unique:labs,lab_code,' . $id,
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'operating_hours' => 'nullable|array',
            'time_slot_duration' => 'nullable|integer|min:15',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['updated_by'] = auth()->id();
        
        $lab->update($validated);

        return redirect()->route('backend.labs.index')
            ->with('success', 'Lab updated successfully');
    }

    public function destroy($id)
    {
        $lab = Lab::findOrFail($id);
        
        if ($lab->labOrders()->count() > 0) {
            return response()->json(['message' => 'Cannot delete lab with associated orders'], 400);
        }
        
        $lab->deleted_by = auth()->id();
        $lab->save();
        $lab->delete();

        return response()->json(['message' => 'Lab deleted successfully']);
    }

    public function update_status(Request $request, $id)
    {
        $lab = Lab::findOrFail($id);
        $lab->is_active = $request->status;
        $lab->updated_by = auth()->id();
        $lab->save();

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function getLabsByClinic($clinic_id)
    {
        $labs = Lab::where('clinic_id', $clinic_id)
                   ->where('is_active', true)
                   ->orderBy('name')
                   ->get(['id', 'name', 'lab_code']);

        return response()->json($labs);
    }
}
