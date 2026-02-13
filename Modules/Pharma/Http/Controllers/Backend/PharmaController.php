<?php

namespace Modules\Pharma\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Hash;
use Modules\Pharma\Http\Requests\PharmaRequest;
use Validator;
use Carbon\Carbon;
use Modules\Appointment\Models\Appointment;
use Modules\Clinic\Models\ClinicsService;
use Modules\Clinic\Models\Clinics;
use App\Models\Setting;
use Modules\Product\Models\Order;
use Modules\Product\Models\OrderGroup;
use Modules\Pharma\Models\Medicine;
use Modules\Earning\Models\EmployeeEarning;
use Modules\Appointment\Models\EncounterPrescription;
use Modules\Appointment\Models\PatientEncounter;
use Modules\Pharma\Models\MedicineForm;
use Modules\Pharma\Models\PurchasedOrder;
use Modules\Commission\Models\EmployeeCommission;
use DB;
use \Modules\Currency\Models\Currency as CurrencyModel;
use Modules\Pharma\Exports\PharmaExport;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Appointment\Models\EncounterPrescriptionBillingDetail;
use Modules\Commission\Models\CommissionEarning;
use Modules\Pharma\Models\Supplier;

class PharmaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected string $exportClass = '\Modules\Pharma\Exports\PharmaExport';
    public function __construct()
    {
        // Page Title
        $this->module_title = 'Pharma::messages.add_pharma';
        $this->edit_module_title = 'Pharma::messages.edit_pharma';
        // module name
        $this->module_name = 'pharma';

        view()->share([
            'module_title' => $this->module_title,
            'module_name' => $this->module_name,
            'edit_module_title' => $this->edit_module_title
        ]);
    }

    public function pharmaDashboard()
    {
        $current_user = setNamePrefix(User::find(auth()->user()->id));
        $today = Carbon::today();
        $action = $request->action ?? 'reset';


        $totalMedicine = Medicine::where('pharma_id', auth()->user()->id)

            ->count();
        $fromDate = $today->copy()->addDay(0);
        $toDate = $today->copy()->addDays(5);

        $upcomingMeidcineExpiry = Medicine::where('pharma_id', auth()->user()->id)
            ->whereBetween('expiry_date', [$fromDate, $toDate])
            ->count();

        $lowStockMeidcineCount = Medicine::where('pharma_id', auth()->user()->id)
            ->whereRaw('CAST(quntity AS UNSIGNED) <= CAST(re_order_level AS UNSIGNED)')
            ->whereDate('expiry_date', '>', $today)
            ->count();


        $topSold = EncounterPrescription::whereHas('medicine', function ($query) {
                $query->where('pharma_id', auth()->user()->id);
            })
            ->whereNull('deleted_at')
            ->select('medicine_id', DB::raw('COUNT(*) as total_sold'))
            ->groupBy('medicine_id')
            ->get();

        // Step 1: Get all totals sorted desc
        $sortedTotals = $topSold->pluck('total_sold')->sortDesc()->values();

        // Step 2: Take top 5 unique totals
        $top5Values = $sortedTotals->unique()->take(5);

        // Step 3: Count how many medicines have one of those top 5 totals
        $topMedicines = $topSold->filter(function ($item) use ($top5Values) {
            return $top5Values->contains($item->total_sold);
        })->count();


        $withdrawalamount = CommissionEarning::where('user_type', 'pharma')->where('employee_id', auth()->user()->id)->where('commission_status', 'unpaid')->sum('commission_amount') ?? 0;
        $toalEarning = CommissionEarning::where('user_type', 'pharma')->where('employee_id', auth()->user()->id)->whereIn('commission_status', ['unpaid','paid'])->sum('commission_amount') ?? 0;

        // $totalRevenue = PatientEncounter::
        $totalRevenue = EncounterPrescriptionBillingDetail::whereHas('encounter', function ($query) {
                $query->where('pharma_id', auth()->id())->where('prescription_payment_status', 1);
            })
            ->sum('total_amount');

        $pharmaId = auth()->user()->id;
        $clinicId = auth()->user()->clinic_id;

        $encounters = PatientEncounter::with(['user', 'doctor', 'prescriptions.medicine'])
            ->where('clinic_id', $clinicId)
            ->where('status', 0)
            ->where('prescription_payment_status', 0)
            ->whereNotNull('pharma_id')
            ->whereHas('prescriptions.medicine', function ($q) use ($pharmaId) {
                $q->where('pharma_id', $pharmaId);
            })
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $filteredEncounters = $encounters->filter(function ($encounter) use ($pharmaId) {
            $prescriptions = $encounter->prescriptions;

            if ($prescriptions->isEmpty()) {
                return false;
            }

            return $prescriptions->every(function ($prescription) use ($pharmaId) {
                return $prescription->medicine && $prescription->medicine->pharma_id == $pharmaId;
            });
        })->take(10);

        $latestPrescriptions = $filteredEncounters
            ->sortByDesc(function ($encounter) {
                return $encounter->prescriptions->max('created_at');
            })
            ->values();



        $pharmaId = auth()->id();

        $totalSuppliers = Supplier::where('pharma_id', $pharmaId)->where('status', 1)->count();



        $topSuppliers = Supplier::whereHas('medicine.purchasedOrders', function ($query) use ($pharmaId) {
            $query->where('pharma_id', $pharmaId)
                  ->whereIn('payment_status', ['completed','paid'])
                  ->where('order_status', 'delivered');
            })
            ->withCount(['medicine as purchase_count' => function ($query) use ($pharmaId) {
                $query->whereHas('purchasedOrders', function ($q) use ($pharmaId) {
                    $q->where('pharma_id', $pharmaId)
                    ->whereIn('payment_status', ['completed','paid'])
                    ->where('order_status', 'delivered');
                });
            }])
            ->whereNull('deleted_at')
            ->orderByDesc('purchase_count')
            ->limit(5)
            ->get()
            ->map(function ($supplier) {
                return [
                    'supplier_id' => $supplier->id,
                    'full_name' => $supplier->first_name . ' ' . $supplier->last_name,
                    'status' => $supplier->status,
                    'purchase_count' => $supplier->purchase_count ?? 0,
                ];
            });

        $expiredMedicineCount = Medicine::where('pharma_id', auth()->user()->id)
            ->whereDate('expiry_date', '<=', $today)
            ->where('pharma_id', auth()->user()->id)
            ->count();

        $availableMedicineCount = Medicine::where('pharma_id', auth()->id())
            ->whereDate('expiry_date', '>', $today)
            ->whereRaw('CAST(quntity AS UNSIGNED) > CAST(re_order_level AS UNSIGNED)')
            ->count();
        $date_range = '';
        $setting = Setting::where('name', 'date_formate')->first();
        $dateformate = $setting ? $setting->val : 'Y-m-d';

        $setting = Setting::where('name', 'time_formate')->first();
        $timeformate = $setting ? $setting->val : 'h:i A';



        $timeZoneSetting = Setting::where('name', 'default_time_zone')->first();
        $timeZone = $timeZoneSetting ? $timeZoneSetting->val : 'UTC';

        $data = [
            'total_medicine' => $totalMedicine ?? 0,
            'top_medicines' => $topMedicines ?? 0,
            'upcoming_meidcine_expiry' => $upcomingMeidcineExpiry ?? 0,
            'low_stock_meidcine_count' => $lowStockMeidcineCount ?? 0,
            'withdrawal_amount' => $withdrawalamount ?? 0,
            'toal_earnings' => $toalEarning ?? 0,
            'total_revenue_generated' => $totalRevenue ?? 0,
            'letest_prescriptions' => $latestPrescriptions ?? 0,
            'top_suppliers' => $topSuppliers,
            'low_stock' => $lowStockMeidcineCount,
            'expired_in_stock' => $expiredMedicineCount,
            'available_medicines' => $availableMedicineCount,
            'dateformate' => $dateformate,
            'timeformate' => $timeformate,
            'timeZone' => $timeZone,
            'total_supplier' => $totalSuppliers ?? 0,
        ];


        $totalServices = [];

        $data['total_commission'] = [];

        $data['total_commission'] = \Currency::format(0);

        $bookings = [];


        $data['top_services'] = [];

        $chartBookingRevenue = [];

        $data['revenue_chart']['xaxis'] = [];
        $data['revenue_chart']['total_bookings'] = [];
        $data['revenue_chart']['total_price'] = [];

        $orders = PurchasedOrder::where('pharma_id', auth()->user()->id)->where('order_status', 'delivered');

        $data['total_orders'] = $orders->count();

        $currency = CurrencyModel::where('is_primary', true)->first();


        return view('pharma::dashboard', compact('data', 'date_range', 'current_user', 'timeZone', 'currency'));
    }

    public function pharmaDashboardDaterange($daterange)
    {
        $current_user = setNamePrefix(User::find(auth()->user()->id));
        $today = Carbon::today();
        $action = $request->action ?? 'reset';
        if ($daterange === null) {
            $startDate = now()->subDays(7)->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
            $date_range = $startDate . ' to ' . $endDate;
        } else {
            $decodedDateRange = urldecode($daterange);
            $dateRangeParts = explode(' to ', $decodedDateRange);
            $startDate = $dateRangeParts[0] ?? now()->format('Y-m-d');
            $endDate = $dateRangeParts[1] ?? now()->format('Y-m-d');
            $date_range = $startDate . ' to ' . $endDate;
        }

        // Handle custom date range
        $startDate = $startDate ? Carbon::parse($startDate) : $today->copy()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : $today;

        $totalMedicine = Medicine::where('pharma_id', auth()->user()->id)->whereBetween('updated_at', [$startDate, $endDate])->count();

        $fromDate = $today->copy()->addDay(0);
        $toDate = $today->copy()->addDays(5);
        $upcomingMeidcineExpiry = Medicine::where('pharma_id', auth()->user()->id)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->whereBetween('expiry_date', [$fromDate, $toDate])
            ->count();

        $lowStockMeidcineCount = Medicine::where('pharma_id', auth()->user()->id)
            ->whereColumn('quntity', '<=', 're_order_level')
            ->whereDate('expiry_date', '>', $today)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();


        // Get top medicine threshold from settings or use default
        $topSold = EncounterPrescription::whereHas('medicine', function ($query) {
                $query->where('pharma_id', auth()->user()->id);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('medicine_id', DB::raw('COUNT(*) as total_sold'))
            ->groupBy('medicine_id')
            ->get();

        // Step 1: Get all totals sorted desc
        $sortedTotals = $topSold->pluck('total_sold')->sortDesc()->values();

        // Step 2: Take top 5 unique totals
        $top5Values = $sortedTotals->unique()->take(5);

        // Step 3: Count how many medicines have one of those top 5 totals
        $topMedicines = $topSold->filter(function ($item) use ($top5Values) {
            return $top5Values->contains($item->total_sold);
        })->count();


        $withdrawalamount = CommissionEarning::whereBetween('updated_at', [$startDate, $endDate])->where('user_type', 'pharma')->where('employee_id', auth()->user()->id)->where('commission_status', 'unpaid')->sum('commission_amount') ?? 0;

        $toalEarning = CommissionEarning::whereBetween('updated_at', [$startDate, $endDate])->where('user_type', 'pharma')->where('employee_id', auth()->user()->id)->whereIn('commission_status', ['unpaid','paid'])->sum('commission_amount') ?? 0;



        $totalRevenue = EncounterPrescriptionBillingDetail::whereHas('encounter', function ($query) use ($startDate, $endDate) {
            $query->where('pharma_id', auth()->id());
        })
        ->whereBetween('created_at', [$startDate, $endDate])
        ->sum('total_amount');

        $pharmaId = auth()->user()->id;
        $clinicId = auth()->user()->clinic_id;

        $encounters = PatientEncounter::with(['user', 'doctor', 'prescriptions.medicine'])
            ->where('clinic_id', $clinicId)
            ->whereHas('prescriptions.medicine', function ($q) use ($pharmaId) {
                $q->where('pharma_id', $pharmaId);
            })
            ->where(function ($q) {
                $q->where('prescription_payment_status', '!=', '1')
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'complete')
                            ->where('prescription_payment_status', '!=', '1');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->take(10)
            ->get();

        $filteredEncounters = $encounters->filter(function ($encounter) use ($pharmaId) {
            $prescriptions = $encounter->prescriptions;

            if ($prescriptions->isEmpty()) {
                return false;
            }

            return $prescriptions->every(function ($prescription) use ($pharmaId) {
                return $prescription->medicine && $prescription->medicine->pharma_id == $pharmaId;
            });
        })->take(10);

        $latestPrescriptions = $filteredEncounters->values();

        $totalSuppliers = Supplier::where('pharma_id', $pharmaId)->where('status', 1)->whereBetween('updated_at', [$startDate, $endDate])->count();

        $topSuppliers = Supplier::whereHas('medicine.purchasedOrders', function ($query) use ($pharmaId) {
                $query->where('pharma_id', $pharmaId)
                      ->where('payment_status', 'completed')
                      ->where('order_status', 'delivered');
                })
                ->withCount(['medicine as purchase_count' => function ($query) use ($pharmaId) {
                    $query->whereHas('purchasedOrders', function ($q) use ($pharmaId) {
                        $q->where('pharma_id', $pharmaId)
                        ->where('payment_status', 'completed')
                        ->where('order_status', 'delivered');
                    });
                }])
                ->whereNull('deleted_at')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderByDesc('purchase_count')
                ->limit(5)
                ->get()
                ->map(function ($supplier) {
                    return [
                        'supplier_id' => $supplier->id,
                        'full_name' => $supplier->first_name . ' ' . $supplier->last_name,
                        'status' => $supplier->status,
                        'purchase_count' => $supplier->purchase_count ?? 0,
                    ];
                });

        $expiredMedicineCount = Medicine::where('pharma_id', auth()->user()->id)
            ->whereDate('expiry_date', '<=', $today)
            ->count();

        $availableMedicineCount = Medicine::where('pharma_id', auth()->user()->id)
            ->where('quntity', '>', 0)
            ->whereDate('expiry_date', '>', $today)
            ->count();

        $setting = Setting::where('name', 'date_formate')->first();
        $dateformate = $setting ? $setting->val : 'Y-m-d';

        $setting = Setting::where('name', 'time_formate')->first();
        $timeformate = $setting ? $setting->val : 'h:i A';

        $timeZoneSetting = Setting::where('name', 'default_time_zone')->first();
        $timeZone = $timeZoneSetting ? $timeZoneSetting->val : 'UTC';

        $data = [
            'total_medicine' => $totalMedicine ?? 0,
            'top_medicines' => $topMedicines ?? 0,
            'upcoming_meidcine_expiry' => $upcomingMeidcineExpiry ?? 0,
            'low_stock_meidcine_count' => $lowStockMeidcineCount ?? 0,
            'withdrawal_amount' => $withdrawalamount ?? 0,
            'toal_earnings' => $toalEarning ?? 0,
            'total_revenue_generated' => $totalRevenue ?? 0,
            'letest_prescriptions' => $latestPrescriptions ?? 0,
            'top_suppliers' => $topSuppliers,
            'low_stock' => $lowStockMeidcineCount,
            'expired_in_stock' => $expiredMedicineCount,
            'available_medicines' => $availableMedicineCount,
            'dateformate' => $dateformate,
            'timeformate' => $timeformate,
            'timeZone' => $timeZone,
            'total_supplier' => $totalSuppliers ?? 0,
        ];

        $orders = PurchasedOrder::where('pharma_id', auth()->user()->id)
            ->where('payment_status', 'completed')
            ->where('order_status', 'delivered')
            ->whereBetween('updated_at', [$startDate, $endDate]);

        $data['total_orders'] = $orders->count();

        $data['total_commission'] = \Currency::format(0);
        $data['top_services'] = [];
        $data['revenue_chart'] = [
            'xaxis' => [],
            'total_bookings' => [],
            'total_price' => [],
        ];

        return view('pharma::dashboard', compact('data', 'date_range', 'current_user', 'timeZone'));
    }

    public function index(Request $request)
    {
        $module_action = 'List';
        $user = auth()->user();

        $module_title = __('pharma::messages.pharma');
        $create_title = __('pharma::messages.create');

        $filter = [
            'status' => $request->status,
            'payment_status' => $request->payment_status,
        ];

        $export_import = true;
        $export_columns = [
            ['value' => 'pharma_name', 'text' => __('pharma::messages.pharma_name')],
            ['value' => 'mobile', 'text' => __('pharma::messages.contact_number')],
            ['value' => 'clinic_name', 'text' => __('pharma::messages.clinic')],
            ['value' => 'verification_status', 'text' => __('clinic.lbl_verification_status')],
            ['value' => 'status', 'text' => __('pharma::messages.status')],
        ];
        $export_url = route('backend.pharma.export');
        return view('pharma::pharma.index_datatable', compact('module_action', 'module_title', 'filter', 'create_title', 'export_import', 'export_columns', 'export_url'));
    }

    public function index_data(Datatables $datatable, Request $request)
    {
        $query = User::pharmaRole(auth()->user())->with('clinic')
            ->where('user_type', 'pharma');


        $orderColumnIndex = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir');
        $columns = $request->input('columns');
        $filter = $request->filter;
        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }

        if (isset($filter)) {
            if (isset($filter['pharma'])) {
                $query->where('id', $filter['pharma']);
            }
            if (isset($filter['clinic'])) {
                $query->where('clinic_id', $filter['clinic']);
            }
            if (isset($filter['contact_number'])) {

                $query->where('id', $filter['contact_number']);
            }
        }

        $isReceptionist = auth()->user()->hasRole('receptionist');

        return $datatable->eloquent($query)
            ->addColumn('check', function ($data) use ($isReceptionist) {
                if ($isReceptionist) {
                    return '';
                }
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $data->id . '"  name="datatable_ids[]" value="' . $data->id . '" onclick="dataTableRowCheck(' . $data->id . ')">';
            })

            ->addColumn('action', function ($data) {
                return view('pharma::pharma.action_column', compact('data'));
            })

            ->addColumn('pharma_name', function ($data) {
                return view('pharma::pharma.pharma_id', compact('data'));
            })
            ->filterColumn('pharma_name', function ($query, $keyword) {
                if (!empty($keyword)) {
                    $query->where('first_name', 'like', '%' . $keyword . '%')->orWhere('last_name', 'like', '%' . $keyword . '%')->orWhere('email', 'like', '%' . $keyword . '%');
                }
            })
            ->orderColumn('pharma_name', function ($query, $order) {
                $query->orderBy('first_name', $order);
            })


            ->editColumn('mobile', function ($data) {
                return $data->mobile;
            })
            ->filterColumn('mobile', function ($query, $keyword) {
                if (!empty($keyword)) {
                    $query->where('mobile', 'like', '%' . $keyword . '%');
                }
            })

            ->editColumn('clinic_name', function ($data) {
                return view('pharma::pharma.clinic_id', compact('data'));
            })
            ->filterColumn('clinic_name', function ($query, $keyword) {
                $query->whereHas('clinic', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->orderColumn('clinic_name', function ($query, $order) {
                $query->whereHas('clinic', function ($q) use ($order) {
                    $q->orderBy('name', $order);
                });
            }, 1)

            ->editColumn('email_verified_at', function ($data) {
                return view('pharma::pharma.verify_action', compact('data'));
            })

            ->editColumn('status', function ($data) use ($isReceptionist) {
                if ($isReceptionist) {
                    return $data->status ? '<span class="badge bg-success">' . __('messages.active') . '</span>' : '<span class="badge bg-danger">' . __('messages.inactive') . '</span>';
                }
                $checked = '';
                if ($data->status) {
                    $checked = 'checked="checked"';
                }

                return '
                    <div class="form-check form-switch ">
                        <input type="checkbox" data-url="' . route('backend.pharma.update-status', $data->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input"  id="datatable-row-' . $data->id . '"  name="status" value="' . $data->id . '" ' . $checked . '>
                    </div>
                ';
            })
            ->rawColumns(['action', 'clinic_name', 'status', 'check', 'image', 'description'])
            ->orderColumns(['id'], '-:column $1')
            ->toJson();
    }

    public function bulk_action(Request $request)
    {
        if (auth()->user()->hasRole('receptionist')) {
            abort(403, 'You do not have permission to perform bulk actions.');
        }
        $ids = explode(',', $request->rowIds);
        $actionType = $request->action_type;

        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                // Need To Add Role Base
                $employee = User::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('pharma::messages.pharma_update');
                break;

            default:
                return response()->json(['status' => false, 'message' => __('clinic.invalid_action')]);
                break;
        }

        return response()->json(['status' => true, 'message' => $message]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (auth()->user()->hasRole('receptionist')) {
            abort(403, 'You do not have permission to create pharma.');
        }
        $isEdit = false;
        return view('pharma::pharma.create', compact('isEdit'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PharmaRequest $request)
    {
        if (auth()->user()->hasRole('receptionist')) {
            abort(403, 'You do not have permission to create pharma.');
        }


        $data = $request->validated();

        // Hash the password
        $data['password'] = bcrypt($data['password']);
        $data['mobile'] = $data['contact_number'];
        $data['address'] = $data['address'] ?? null;
        $data['date_of_birth'] = $data['dob'];
        $data['email_verified_at'] = now();
        $data['clinic_id'] = $data['clinic'];
        $data['user_type'] = 'pharma';
        // Save to the users table (replace with your model if different)
        $pharma = User::create($data);
        $pharma->syncRoles(['pharma']);

        // Send add_pharma notification to doctor/admin/vendor
        sendNotification([
            'notification_type' => 'add_pharma',
            'pharma_id' => $pharma->id,
            'pharma_name' => $pharma->first_name . ' ' . $pharma->last_name,
            'clinic_id' => $pharma->clinic_id,
            'pharma' => $pharma,
            'vendor_id' => $pharma->clinic->vendor_id,
        ]);

        // Check if this is an API request
        $isApiRequest = $this->isApiRequest($request);

        if (isset($request->pharma_commission) && $request->has('pharma_commission') && $request->pharma_commission !== null) {
            // Handle both API (comma-separated string) and web (array) formats
            $commissions = $isApiRequest ? explode(',', $request->pharma_commission) : $request->pharma_commission;

            // Ensure it's an array
            if (!is_array($commissions)) {
                $commissions = [$commissions];
            }

            foreach ($commissions as $value) {
                $commission_data = [
                    'employee_id' => $pharma->id,
                    'commission_id' => (int) $value,
                ];

                EmployeeCommission::create($commission_data);
            }
        }

        if ($request->has('profile_image') && !empty($request->profile_image)) {
            storeMediaFile($pharma, $request->file('profile_image'), 'profile_image');
        }

        // Return appropriate response based on request type
        if ($isApiRequest) {
            return response()->json([
                'message' => 'Pharma user created successfully.',
                'data' => $pharma,
                'status' => true
            ], 200);
        } else {
            return redirect()
                ->route('backend.pharma.index')
                ->with('success', 'Pharma user created successfully.');
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('pharma::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (auth()->user()->hasRole('receptionist')) {
            abort(403, 'You do not have permission to edit pharma.');
        }
        $isEdit = true;
        $edit_module_title = __('pharma::messages.edit_pharma');
        $pharmaDetail = User::pharmaRole(auth()->user())->with(['clinic', 'commissionData'])->where('id', $id)->first();
        if ($pharmaDetail == null) {
            abort(403, 'You are not authorized to access this supplier.');
        }

        return view('pharma::pharma.create', compact('pharmaDetail', 'isEdit', 'edit_module_title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (auth()->user()->hasRole('receptionist')) {
            abort(403, 'You do not have permission to update pharma.');
        }

        // Get request data without validation
        $data = $request->all();

        // Find the existing user
        $pharma = User::findOrFail($id);

        // Handle password
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        // Map request fields to DB columns
        $data['mobile'] = $data['contact_number'];
        $data['date_of_birth'] = $data['dob'];
        $data['clinic_id'] = $data['clinic'];
        $data['address'] = $data['address'] ?? null;

        // Remove non-User table fields
        unset($data['contact_number'], $data['dob'], $data['clinic']);

        // Update the user
        $pharma->update($data);

        // Ensure role assignment
        if (!$pharma->roles()->where('name', 'pharma')->exists()) {
            $pharma->assignRole('pharma');
        }

        // Check if this is an API request
        $isApiRequest = $this->isApiRequest($request);

        // ✅ Update pharma commission if provided
        if (!empty($request->pharma_commission)) {

            $commissions = $isApiRequest ? explode(',', $request->pharma_commission) : $request->pharma_commission;

            if (!is_array($commissions)) {
                $commissions = [$commissions];
            }

            EmployeeCommission::where('employee_id', $pharma->id)->delete();

            foreach ($commissions as $value) {
                EmployeeCommission::create([
                    'employee_id' => $pharma->id,
                    'commission_id' => (int) $value,
                ]);
            }
        }

        if ($request->hasFile('profile_image')) {
            $pharma->clearMediaCollection('profile_image');
            storeMediaFile($pharma, $request->file('profile_image'), 'profile_image');
        }

        if ($isApiRequest) {
            return response()->json([
                'message' => 'Pharma user updated successfully.',
                'data' => $pharma,
                'status' => true
            ], 200);
        }

        return redirect()
            ->route('backend.pharma.index')
            ->with('success', 'Pharma user updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (auth()->user()->hasRole('receptionist')) {
            abort(403, 'You do not have permission to delete pharma.');
        }

    }

    public function updateStatus(Request $request, User $id)
    {
        if (auth()->user()->hasRole('receptionist')) {
            abort(403, 'You do not have permission to update pharma status.');
        }
        $id->update(['status' => $request->status]);
        return response()->json(['status' => true, 'message' => __('pharma::messages.pharma_update')]);
    }

    public function pharmaDetail($pharmaId)
    {

        $pharmaDetail = User::pharmaRole(auth()->user())->with('clinic')->where('id', $pharmaId)->first();

        $html = view('pharma::pharma.partials.details', compact('pharmaDetail'))->render();

        return response()->json(['html' => $html]);
    }
    public function changePassword($pharmaId)
    {
        if (auth()->user()->hasRole('receptionist')) {
            abort(403, 'You do not have permission to change pharma password.');
        }

        $pharmaDetail = User::with('clinic')->where('id', $pharmaId)->first();

        $html = view('pharma::pharma.partials.change_password', compact('pharmaDetail'))->render();

        return response()->json(['html' => $html]);
    }

    public function updatePassword(Request $request, $pharmaId)
    {
        if (auth()->user()->hasRole('receptionist')) {
            abort(403, 'You do not have permission to update pharma password.');
        }
        $data = $request->all();

        $validator = Validator::make($request->all(), [
            'new-password' => 'required',
            'confirm-password' => 'required|same:new-password',
        ], [
            'new-password.required' => 'Password is required.',
            'confirm-password.required' => 'Confirm Password is required.',
            'confirm-password.same' => 'Password and Confirm Password must match.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::findOrFail($pharmaId);
        $user->update([
            'password' => Hash::make($request->input('new-password'))
        ]);

        $message = __('messages.password_update');
        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function getRevenuechartData(Request $request)
    {
        $userId = auth()->id();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : null;
        $endDate   = $request->end_date ? Carbon::parse($request->end_date) : null;


        $customChartData = [];
        $customCategories = [];

        if ($startDate && $endDate) {
            $customTotals = EncounterPrescriptionBillingDetail::whereHas('encounter', function ($query) use ($userId) {
                    $query->where('pharma_id', $userId)->where('prescription_payment_status', 1);
                })
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total_amount')
                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate->copy()->addDay());

            foreach ($period as $date) {
                $label = $date->format('Y-m-d'); // e.g. "2025-09-05"
                $customCategories[] = $date->format('d M'); // e.g. "05 Sep"
                $dayTotal = $customTotals->get($label);
                $customChartData[] = $dayTotal ? (float)$dayTotal->total_amount : 0;
            }
        }


        $monthlyTotals = EncounterPrescriptionBillingDetail::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total_amount) as total_amount')
            ->whereHas('encounter', function ($query) use ($userId) {
                $query->where('pharma_id', $userId)->where('prescription_payment_status', 1);
            })->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get();

        $yearChartData = [];
        for ($month = 1; $month <= 12; $month++) {
            $found = false;
            foreach ($monthlyTotals as $total) {
                if ((int)$total->month === $month) {
                    $yearChartData[] = (float)$total->total_amount;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $yearChartData[] = 0;
            }
        }

        $monthNames = [
            __('pharma::messages.jan'),
            __('pharma::messages.feb'),
            __('pharma::messages.mar'),
            __('pharma::messages.apr'),
            __('pharma::messages.may'),
            __('pharma::messages.jun'),
            __('pharma::messages.jul'),
            __('pharma::messages.aug'),
            __('pharma::messages.sep'),
            __('pharma::messages.oct'),
            __('pharma::messages.nov'),
            __('pharma::messages.dec'),
        ];

        // === Monthly Chart (Weekly Revenue) ===
        $firstWeek = Carbon::now()->startOfMonth()->week;

        $monthlyWeekTotals = EncounterPrescriptionBillingDetail::whereHas('encounter', function ($query) use ($userId) {
                $query->where('pharma_id', $userId)->where('prescription_payment_status', 1);
            })->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, WEEK(created_at) as week, SUM(total_amount) as total_amount')
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->groupBy('year', 'month', 'week')
            ->orderBy('year')
            ->orderBy('month')
            ->orderBy('week')
            ->get();

        $monthlyChartData = [];
        for ($i = $firstWeek; $i <= $firstWeek + 4; $i++) {
            $found = false;
            foreach ($monthlyWeekTotals as $total) {
                if ((int)$total->month === $currentMonth && (int)$total->week === $i) {
                    $monthlyChartData[] = (float)$total->total_amount;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $monthlyChartData[] = 0;
            }
        }

        $weekNames = [
            __('pharma::messages.week_1'),
            __('pharma::messages.week_2'),
            __('pharma::messages.week_3'),
            __('pharma::messages.week_4'),
            __('pharma::messages.week_5'),
        ];

        // === Weekly Chart (Daily Revenue) ===
        $currentWeekStartDate = Carbon::now()->startOfWeek();
        $lastDayOfWeek = Carbon::now()->endOfWeek();

        $weeklyDayTotals = EncounterPrescriptionBillingDetail::whereHas('encounter', function ($query) use ($userId) {
                $query->where('pharma_id', $userId)->where('prescription_payment_status', 1);
            })->selectRaw('DAY(created_at) as day, SUM(total_amount) as total_amount')
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->whereBetween('created_at', [$currentWeekStartDate, $lastDayOfWeek])
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $weeklyChartData = [];
        $day = $currentWeekStartDate->copy();
        while ($day <= $lastDayOfWeek) {
            $found = false;
            foreach ($weeklyDayTotals as $total) {
                if ((int)$total->day === $day->day) {
                    $weeklyChartData[] = (float)$total->total_amount;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $weeklyChartData[] = 0;
            }
            $day->addDay();
        }

        $dayNames = [
            __('pharma::messages.monday'),
            __('pharma::messages.tuesday'),
            __('pharma::messages.wednesday'),
            __('pharma::messages.thursday'),
            __('pharma::messages.friday'),
            __('pharma::messages.saturday'),
            __('pharma::messages.sunday'),
        ];

        $data = [
            'year_chart_data' => $yearChartData,
            'month_names' => $monthNames,
            'month_chart_data' => $monthlyChartData,
            'weekNames' => $weekNames,
            'week_chart_data' => $weeklyChartData,
            'dayNames' => $dayNames,
            'custom_chart_data' => $customChartData,
            'custom_categories' => $customCategories,
        ];

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => __('appointment.revenue_chart_data'),
        ], 200);
    }

    public function getMedicineUsageChartData(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $type = $request->input('type', 'weekly');

        $pharmaId = auth()->id();

        // Get medicine forms
        $forms = MedicineForm::pluck('name')->toArray();
        $chartData = [];

        // Fetch prescriptions with relations
        $prescriptions = EncounterPrescription::with(['medicine.form', 'encounter'])
            ->whereHas('encounter', function ($q) use ($pharmaId, $startDate, $endDate) {
                $q->where('pharma_id', $pharmaId);

                if ($startDate && $endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                }
            })
            ->where('deleted_at', null)
            ->get();

        // Weekly mode
        if ($type === 'weekly') {
            $categories = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

            // Translate days
            $translatedCategories = array_map(function ($day) {
                return __('pharma::messages.' . strtolower($day));
            }, $categories);

            // Group data by day name and form
            $grouped = [];

            foreach ($prescriptions as $prescription) {
                $day = Carbon::parse($prescription->encounter->created_at)->format('l'); // Monday, Tuesday, etc.
                $form = $prescription->medicine->form->name ?? 'Unknown';

                $grouped[$form][$day] = ($grouped[$form][$day] ?? 0) + $prescription->quantity;
            }

        } else {
            // Monthly/weekly mode (simplified to weekly in month)
            $categories = [
                __('pharma::messages.week_1'),
                __('pharma::messages.week_2'),
                __('pharma::messages.week_3'),
                __('pharma::messages.week_4')
            ];
            $translatedCategories = $categories;

            // Group data by week of month and form
            $grouped = [];

            foreach ($prescriptions as $prescription) {
                $date = Carbon::parse($prescription->encounter->created_at);
                $week = intval(floor(($date->day - 1) / 7)) + 1;
                $label = __('pharma::messages.week_' . $week);

                $form = $prescription->medicine->form->name ?? 'Unknown';
                $grouped[$form][$label] = ($grouped[$form][$label] ?? 0) + $prescription->quantity;
            }
        }

        // Format data for chart
        foreach ($forms as $form) {
            $data = [];
            foreach ($categories as $cat) {
                $data[] = $grouped[$form][$cat] ?? 0;
            }
            $chartData[] = [
                'name' => $form,
                'data' => $data,
            ];
        }

        return response()->json([
            'status' => true,
            'data' => $chartData,
            'categories' => $translatedCategories,
        ]);
    }


    public function getEarningsChartData(Request $request)
    {
        $type = $request->get('type', 'weekly');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $userId = auth()->id();
        if ($type === 'custom') {
            $start = $startDate ? Carbon::parse($startDate) : now()->startOfWeek();
            $end   = $endDate ? Carbon::parse($endDate) : now()->endOfWeek();

            $query = CommissionEarning::select(
                    DB::raw('DATE(created_at) as label'),
                    DB::raw('SUM(commission_amount) as total_earnings')
                )
                ->where('user_type', 'pharma')
                ->where('employee_id', $userId)
                ->whereIn('commission_status', ['unpaid', 'paid'])
                ->whereBetween('created_at', [$start, $end])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->get()
                ->keyBy('label');

            $data = [];
            $categories = [];

            // loop each day in range so missing dates show as 0
            $period = new \DatePeriod($start, new \DateInterval('P1D'), $end->copy()->addDay());

            foreach ($period as $date) {
                $label = $date->format('Y-m-d');
                $dayEarning = $query->get($label);
                $data[] = $dayEarning ? (float) $dayEarning->total_earnings : 0;
                $categories[] = $date->format('d M'); // e.g., "19 Sep"
            }

            $totalEarnings = array_sum($data);
        }


        if ($type === 'weekly') {
            $start = $startDate ? Carbon::parse($startDate) : now()->startOfWeek();
            $end = $endDate ? Carbon::parse($endDate) : now()->endOfWeek();

            $query = CommissionEarning::select(
                    DB::raw('DAYOFWEEK(created_at) as weekday'),
                    DB::raw('SUM(commission_amount) as total_earnings')
                )
                ->where('user_type', 'pharma')
                ->where('employee_id', $userId)
                ->whereIn('commission_status', ['unpaid', 'paid'])
                ->whereBetween('created_at', [$start, $end])
                ->groupBy(DB::raw('DAYOFWEEK(created_at)'))
                ->get()
                ->keyBy('weekday');

            if ($query->isEmpty()) {
                $latestDate = CommissionEarning::where('user_type', 'pharma')
                    ->where('employee_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->value('created_at');

                if ($latestDate) {
                    $start = Carbon::parse($latestDate)->startOfWeek();
                    $end = Carbon::parse($latestDate)->endOfWeek();

                    $query = CommissionEarning::select(
                            DB::raw('DAYOFWEEK(created_at) as weekday'),
                            DB::raw('SUM(commission_amount) as total_earnings')
                        )
                        ->where('user_type', 'pharma')
                        ->where('employee_id', $userId)
                        ->whereIn('commission_status', ['unpaid', 'paid'])
                        ->whereBetween('created_at', [$start, $end])
                        ->groupBy(DB::raw('DAYOFWEEK(created_at)'))
                        ->get()
                        ->keyBy('weekday');
                }
            }

            $dayMap = [
                1 => __('pharma::messages.sunday'),
                2 => __('pharma::messages.monday'),
                3 => __('pharma::messages.tuesday'),
                4 => __('pharma::messages.wednesday'),
                5 => __('pharma::messages.thursday'),
                6 => __('pharma::messages.friday'),
                7 => __('pharma::messages.saturday'),
            ];

            $data = [];
            $categories = [];

            foreach (range(1, 7) as $dayNum) {
                $dayEarning = $query->get($dayNum);
                $data[] = $dayEarning ? (float) $dayEarning->total_earnings : 0;
                $categories[] = $dayMap[$dayNum];
            }

            $totalEarnings = array_sum($data);
        }

        elseif ($type === 'monthly') {
            $start = $startDate ? Carbon::parse($startDate) : now()->startOfYear();
            $end = $endDate ? Carbon::parse($endDate) : now()->endOfYear();

            $query = CommissionEarning::select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(commission_amount) as total_earnings')
                )
                ->where('user_type', 'pharma')
                ->where('employee_id', $userId)
                ->whereIn('commission_status', ['unpaid', 'paid'])
                ->whereBetween('created_at', [$start, $end])
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->get()
                ->keyBy('month');

            $monthMap = [
                1 => __('pharma::messages.jan'),
                2 => __('pharma::messages.feb'),
                3 => __('pharma::messages.mar'),
                4 => __('pharma::messages.apr'),
                5 => __('pharma::messages.may'),
                6 => __('pharma::messages.jun'),
                7 => __('pharma::messages.jul'),
                8 => __('pharma::messages.aug'),
                9 => __('pharma::messages.sep'),
                10 => __('pharma::messages.oct'),
                11 => __('pharma::messages.nov'),
                12 => __('pharma::messages.dec'),
            ];

            $data = [];
            $categories = [];

            foreach (range(1, 12) as $monthNum) {
                $monthEarning = $query->get($monthNum);
                $data[] = $monthEarning ? (float) $monthEarning->total_earnings : 0;
                $categories[] = $monthMap[$monthNum];
            }

            $totalEarnings = array_sum($data);
        }

        elseif ($type === 'yearly') {
            $start = $startDate ? Carbon::parse($startDate)->startOfDecade() : now()->subYears(9)->startOfYear();
            $end = $endDate ? Carbon::parse($endDate)->endOfYear() : now()->endOfYear();

            $query = CommissionEarning::select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('SUM(commission_amount) as total_earnings')
                )
                ->where('user_type', 'pharma')
                ->where('employee_id', $userId)
                ->whereIn('commission_status', ['unpaid', 'paid'])
                ->whereBetween('created_at', [$start, $end])
                ->groupBy(DB::raw('YEAR(created_at)'))
                ->get()
                ->keyBy('year');

            $yearRange = range($start->year, $end->year);

            $data = [];
            $categories = [];

            foreach ($yearRange as $year) {
                $yearEarning = $query->get($year);
                $data[] = $yearEarning ? (float) $yearEarning->total_earnings : 0;
                $categories[] = $year;
            }

            $totalEarnings = array_sum($data);
        }

        $currencySymbolData = GetCurrencySymbol();
        $currencySymbol = is_array($currencySymbolData) ? ($currencySymbolData['symbol'] ?? '$') : ($currencySymbolData ?: '$');

        return response()->json([
            'status' => true,
            'data' => $data,
            'categories' => $categories,
            // 'total_earnings' => \Currency::format($totalEarnings),
            'total_earnings' => $totalEarnings,
            'currency_symbol' => $currencySymbol,
            'type' => $type
        ]);
    }

    public function verifyPharma(Request $request, $id)
    {
        $data = User::where('user_type', 'pharma')->findOrFail($id);
        $current_time = Carbon::now();

        $data->update(['email_verified_at' => $current_time]);

        return response()->json(['status' => true, 'message' => __('pharma::messages.pharma_verify')]);
    }


    public function checkEmail(Request $request)
    {
        $query = DB::table('users')->where('email', $request->email);

        if ($request->id) {
            $query->where('id', '!=', $request->id);
        }

        return response()->json(['exists' => $query->exists()]);
    }

    public function checkContact(Request $request)
    {
        $query = DB::table('users')->where('mobile', $request->contact_number);

        if ($request->id) {
            $query->where('id', '!=', $request->id);
        }

        return response()->json(['exists' => $query->exists()]);
    }

    /**
     * Check if the request is an API request
     *
     * @param Request $request
     * @return bool
     */
    protected function isApiRequest(Request $request): bool
    {
        // Check multiple conditions to reliably detect API requests
        return $request->expectsJson()
            || $request->wantsJson()
            || $request->is('api/*')
            || str_starts_with($request->path(), 'api/')
            || $request->routeIs('api.*')
            || $request->header('Accept') === 'application/json'
            || $request->header('Content-Type') === 'application/json';
    }
}
