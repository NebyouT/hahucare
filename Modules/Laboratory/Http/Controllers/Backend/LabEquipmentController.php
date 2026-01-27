<?php

namespace Modules\Laboratory\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Laboratory\Models\LabEquipment;
use Yajra\DataTables\DataTables;

class LabEquipmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_lab_equipment', ['only' => ['index', 'index_data']]);
        $this->middleware('permission:create_lab_equipment', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit_lab_equipment', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_lab_equipment', ['only' => ['destroy']]);
    }

    public function index()
    {
        return view('laboratory::lab-equipment.index');
    }

    public function index_data(Request $request)
    {
        $query = LabEquipment::query();

        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('equipment_name', 'like', "%{$search}%")
                  ->orWhere('equipment_code', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('status_badge', function($row) {
                $badges = [
                    'active' => 'success',
                    'maintenance' => 'warning',
                    'inactive' => 'secondary',
                    'retired' => 'danger',
                ];
                $class = $badges[$row->status] ?? 'secondary';
                return '<span class="badge bg-'.$class.'">'.ucfirst($row->status).'</span>';
            })
            ->addColumn('maintenance_status', function($row) {
                if ($row->next_maintenance_date) {
                    $daysUntil = now()->diffInDays($row->next_maintenance_date, false);
                    if ($daysUntil < 0) {
                        return '<span class="badge bg-danger">Overdue</span>';
                    } elseif ($daysUntil <= 30) {
                        return '<span class="badge bg-warning">Due Soon</span>';
                    } else {
                        return '<span class="badge bg-success">Up to Date</span>';
                    }
                }
                return '-';
            })
            ->addColumn('action', function($row) {
                $editUrl = route('backend.lab-equipment.edit', $row->id);
                $deleteUrl = route('backend.lab-equipment.destroy', $row->id);
                
                return '<div class="btn-group">
                    <a href="'.$editUrl.'" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                    <button type="button" class="btn btn-sm btn-danger delete-btn" data-url="'.$deleteUrl.'"><i class="fas fa-trash"></i></button>
                </div>';
            })
            ->rawColumns(['status_badge', 'maintenance_status', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('laboratory::lab-equipment.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_name' => 'required|string|max:255',
            'equipment_code' => 'required|string|unique:lab_equipment,equipment_code',
            'description' => 'nullable|string',
            'manufacturer' => 'nullable|string',
            'model_number' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'last_maintenance_date' => 'nullable|date',
            'next_maintenance_date' => 'nullable|date',
            'status' => 'required|in:active,maintenance,inactive,retired',
            'location' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        LabEquipment::create($validated);

        return redirect()->route('backend.lab-equipment.index')
            ->with('success', 'Equipment created successfully');
    }

    public function edit($id)
    {
        $equipment = LabEquipment::findOrFail($id);
        return view('laboratory::lab-equipment.edit', compact('equipment'));
    }

    public function update(Request $request, $id)
    {
        $equipment = LabEquipment::findOrFail($id);

        $validated = $request->validate([
            'equipment_name' => 'required|string|max:255',
            'equipment_code' => 'required|string|unique:lab_equipment,equipment_code,' . $id,
            'description' => 'nullable|string',
            'manufacturer' => 'nullable|string',
            'model_number' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'last_maintenance_date' => 'nullable|date',
            'next_maintenance_date' => 'nullable|date',
            'status' => 'required|in:active,maintenance,inactive,retired',
            'location' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();
        $equipment->update($validated);

        return redirect()->route('backend.lab-equipment.index')
            ->with('success', 'Equipment updated successfully');
    }

    public function destroy($id)
    {
        $equipment = LabEquipment::findOrFail($id);
        $equipment->deleted_by = auth()->id();
        $equipment->save();
        $equipment->delete();

        return response()->json(['message' => 'Equipment deleted successfully']);
    }

    public function update_status(Request $request, $id)
    {
        $equipment = LabEquipment::findOrFail($id);
        $equipment->status = $request->status;
        $equipment->updated_by = auth()->id();
        $equipment->save();

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function bulk_action(Request $request)
    {
        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            LabEquipment::whereIn('id', $ids)->update(['deleted_by' => auth()->id()]);
            LabEquipment::whereIn('id', $ids)->delete();
            return response()->json(['message' => 'Selected equipment deleted successfully']);
        }

        if ($action === 'activate') {
            LabEquipment::whereIn('id', $ids)->update(['status' => 'active', 'updated_by' => auth()->id()]);
            return response()->json(['message' => 'Selected equipment activated successfully']);
        }

        if ($action === 'deactivate') {
            LabEquipment::whereIn('id', $ids)->update(['status' => 'inactive', 'updated_by' => auth()->id()]);
            return response()->json(['message' => 'Selected equipment deactivated successfully']);
        }

        return response()->json(['message' => 'Invalid action'], 400);
    }
}
