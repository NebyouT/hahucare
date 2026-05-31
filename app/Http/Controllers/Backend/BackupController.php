<?php

namespace App\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laracasts\Flash\Flash;
use Modules\Clinic\Models\Clinics;
use Modules\Clinic\Models\ClinicsService;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    // use Authorizable;

    protected $module_title;
    protected $module_name;
    protected $module_path;
    protected $module_model;
    protected $module_icon;

    public function __construct()
    {
        // Page Title
        $this->module_title = 'backup.title';

        // module name
        $this->module_name = 'backups';

        // directory path of the module
        $this->module_path = 'backups';

        // module model name, path
        $this->module_model = "App\Models\ActivityLog";

        // module icon
        $this->module_icon = 'fas fa-archive';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function indexed()
    // {
    //     $module_title = $this->module_title;
    //     $module_name = $this->module_name;
    //     $module_path = $this->module_path;
    //     $module_icon = $this->module_icon;
    //     $module_name_singular = Str::singular($module_name);

    //     $module_action = 'List';

    //     $disk = Storage::disk('local');

    //     $files = $disk->files(config('backup.backup.name'));

    //     $$module_name = [];

    //     // make an array of backup files, with their filesize and creation date
    //     foreach ($files as $k => $f) {
    //         // only take the zip files into account
    //         if (substr($f, -4) == '.zip' && $disk->exists($f)) {
    //             $$module_name[] = [
    //                 'file_path' => $f,
    //                 'file_name' => str_replace(config('backup.backup.name') . '/', '', $f),
    //                 'file_size_byte' => $disk->size($f),
    //                 'file_size' => humanFilesize($disk->size($f)),
    //                 'last_modified_timestamp' => $disk->lastModified($f),
    //                 'date_created' => Carbon::createFromTimestamp($disk->lastModified($f))->toDateTimeString(),
    //                 'date_ago' => Carbon::createFromTimestamp($disk->lastModified($f))->diffForHumans(Carbon::now()),
    //             ];
    //         }
    //     }

    //     // reverse the backups, so the newest one would be on top
    //     $$module_name = array_reverse($$module_name);

    //     return view(
    //         "backend.$module_path.backups",
    //         compact('module_title', 'module_name', "$module_name", 'module_path', 'module_icon', 'module_action', 'module_name_singular')
    //     );
    // }

    public function index()
    {
        $module_title = __('backup.title');
        $module_name = $this->module_name;
        $module_icon = $this->module_icon;
        return view('backend.backups.index_datatable', compact('module_title', 'module_name', 'module_icon'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try {
            // start the backup process
            Artisan::call('backup:run');
            $output = Artisan::output();

            // Log the results
            Log::info("Backpack\BackupManager -- new backup started from admin interface \r\n" . $output);

            // return the results as a response to the ajax call
            flash("<i class='fas fa-check'></i> New backup created")->success()->important();

            return redirect()->back();
        } catch (Exception $e) {
            Flash::error($e->getMessage());

            return redirect()->back();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createDBBk()
    {
        // try {
        // Clear cache before backup (optional)
        Artisan::call('cache:clear');

        // Run only database backup
        //    Artisan::call('backup:run --only-db');

        Artisan::call('backup:run --only-db --verbose');

        // // Check if backup file exists
        // $files = Storage::disk('local')->files('laravel-backups');

        // if (!empty($files)) {
        //     $latestBackup = collect($files)->last(); // Get latest file
        //     $backupPath = storage_path("app/{$latestBackup}");

        //     // Log or display backup file path
        Log::info("Database backup created");

        return back()->with('success', 'Database backup created at');

        // } catch (\Exception $e) {
        //     Log::error("Backup failed: " . $e->getMessage());
        //     return back()->with('error', 'Backup failed: ' . $e->getMessage());
        // }
    }

    /**
     * Downloads a backup zip file.
     *
     * TODO: make it work no matter the flysystem driver (S3 Bucket, etc).
     */


    public function download($file_name)
    {
        $file_name = urldecode($file_name); // In case it was URL-encoded

        $file = 'app/' . config('backup.backup.name') . '/' . $file_name;
        $filePath = storage_path($file);

        if (!file_exists($filePath)) {
            abort(404, "The backup file doesn't exist.");
        }

        return response()->streamDownload(function () use ($filePath) {
            readfile($filePath);
        }, $file_name, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $file_name . '"',
        ]);
    }

    /**
     * Deletes a backup file.
     */
    public function delete($file_name)
    {
        $disk = Storage::disk('local');
        $file = config('backup.backup.name') . '/' . $file_name;

        if ($disk->exists($file)) {
            $disk->delete($file);
            $message = __('messages.delete_form', ['form' => __('backup.file')]);
            return response()->json(['message' => $message, 'status' => true], 200);
        } else {
            return response()->json(['message' => __('messages.record_not_found'), 'status' => false], 404);
        }
    }

    /**
     * Restore database from backup file.
     *
     * @param string $file_name
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($file_name)
    {
        try {
            $disk = Storage::disk('local');
            $file = config('backup.backup.name') . '/' . $file_name;

            if (!$disk->exists($file)) {
                return redirect()->back()->with('error', "The backup file doesn't exist.");
            }

            // Get the full path to the backup file
            $backupPath = storage_path('app/' . $file);

            // Get database configuration
            $dbConfig = config('database.connections.mysql');

            dd($dbConfig);
            $dbName = $dbConfig['database'];
            $dbUser = $dbConfig['username'];
            $dbPass = $dbConfig['password'];
            $dbHost = $dbConfig['host'];
            $dbPort = $dbConfig['port'] ?? '3306';

            // Build the mysql command with proper escaping
            $command = sprintf(
                'mysql --host=%s --port=%s --user=%s --password=%s %s < %s 2>&1',
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                escapeshellarg($backupPath)
            );

            // Execute the command
            $output = [];
            $returnVar = null;

            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                $errorOutput = implode("\n", $output);
                Log::error("Database restore failed (Exit Code: $returnVar): " . $errorOutput);

                // Check if mysql command is available
                exec('which mysql', $whichOutput, $whichReturn);
                if ($whichReturn !== 0) {
                    return redirect()->back()->with('error', 'MySQL client is not installed or not in the system PATH.');
                }

                return redirect()->back()->with('error', 'Failed to restore database. Check logs for details. Error: ' . $errorOutput);
            }

            // Clear the application cache
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');

            // Log the restore action
            Log::info("Database successfully restored from backup: " . $file_name);

            return redirect()->back()->with('success', 'Database has been successfully restored from backup.');
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            Log::error("Database restore error: " . $errorMessage);
            return redirect()->back()->with('error', 'An error occurred while restoring the database: ' . $errorMessage);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function activityLogIndex()
    {
        $module_title = __('messages.activity_log');
        $isDataLog = request()->routeIs('backend.data-log.*');
        $dataUrl = $isDataLog ? route('backend.data-log.index_data') : route('backend.backups.activity_log_index_data');
        $viewUrl = $isDataLog ? 'backend.data-log.view' : 'backend.backups.logs.view';
        $rollbackUrl = $isDataLog ? 'backend.data-log.rollback' : 'backend.backups.rollback';
        return view('backend.backups.activity_log_index_datatable', compact(
            'module_title', 'isDataLog', 'dataUrl', 'viewUrl', 'rollbackUrl'
        ));
    }

    /**
     * Datatable endpoint for Activity Logs
     */
    public function activity_log_index_data(DataTables $datatable, Request $request)
    {
        $query = ActivityLog::query();
        
        // Filter by event type if provided
        if ($request->has('event_type') && $request->event_type != 'all') {
            $query->where('event', $request->event_type);
        }
        
        // Filter by causer role if provided
        if ($request->has('causer_role') && $request->causer_role != 'all') {
            $query->whereHas('causer', function ($q) use ($request) {
                $q->role($request->causer_role);
            });
        }
        
        return $datatable->eloquent($query)
            ->addColumn('created_at', function ($log) {
                return $log->created_at ? formatDate($log->created_at)  : '';
            })
            ->addColumn('subject_type', function ($log) {
                return $log->subject_type ? class_basename($log->subject_type) : '-';
            })
            ->addColumn('description', function ($log) {
                return $log->description ?? '-';
            })
            ->addColumn('event', function ($log) {
                $event = $log->event ?? 'unknown';
                $badgeClass = match($event) {
                    'created' => 'bg-success',
                    'updated' => 'bg-primary',
                    'deleted' => 'bg-danger',
                    default => 'bg-secondary'
                };
                return '<span class="badge ' . $badgeClass . '">' . ucfirst($event) . '</span>';
            })
            ->addColumn('causer', function ($log) {
                if ($log->causer) {
                    $roles = $log->causer->roles->pluck('name')->toArray();
                    $roleLabels = [
                        'admin' => 'Admin',
                        'demo_admin' => 'Demo Admin',
                        'vendor' => 'Clinic Admin',
                        'doctor' => 'Doctor',
                        'receptionist' => 'Receptionist',
                        'user' => 'Patient',
                        'pharmacist' => 'Pharmacist',
                        'lab_technician' => 'Lab Technician'
                    ];
                    
                    $roleBadges = '';
                    foreach ($roles as $role) {
                        $label = $roleLabels[$role] ?? ucfirst($role);
                        $roleBadges .= '<span class="badge bg-info me-1">' . $label . '</span>';
                    }
                    
                    return '<div class="d-flex flex-column">
                        <span class="fw-medium">' . $log->causer->full_name ?? $log->causer->email . '</span>
                        <div class="mt-1">' . $roleBadges . '</div>
                    </div>';
                }
                return '<span class="text-muted">System</span>';
            })
            ->addColumn('action', function ($log) {
                $actions = '<a style="cursor:pointer" onclick="getHistory(' . $log->id . ')"><i class="ph ph-eye align-middle text-secondary me-2" data-bs-toggle="tooltip" title="View"></i></a>';
                if (auth()->user()->hasRole(['admin', 'demo_admin'])) {
                    $props = $log->properties;
                    if (isset($props['old']) && !empty((array)$props['old'])) {
                        $actions .= '<a style="cursor:pointer" onclick="confirmRollback(' . $log->id . ')" class="text-warning"><i class="ph ph-arrow-counter-clockwise" data-bs-toggle="tooltip" title="Rollback"></i></a>';
                    }
                }
                return $actions;
            })
            ->editColumn('updated_at', function ($data) {


                $diff = Carbon::now()->diffInHours($data->updated_at);

                if ($diff < 25) {
                    return $data->updated_at->diffForHumans();
                } else {
                    return $data->updated_at->isoFormat('llll');
                }
            })

            ->rawColumns(['description', 'action', 'event', 'causer'])
            ->toJson();
    }

    /**
     * Datatable endpoint for Backup Files (AJAX, paginated)
     */
    public function index_data(Request $request)
    {
        $disk = Storage::disk('local');
        $files = $disk->files(config('backup.backup.name'));
        $backups = [];
        foreach ($files as $f) {
            if (substr($f, -4) == '.zip' && $disk->exists($f)) {
                $backups[] = [
                    'file_path' => $f,
                    'file_name' => str_replace(config('backup.backup.name') . '/', '', $f),
                    'file_size_byte' => $disk->size($f),
                    'file_size' => humanFilesize($disk->size($f)),
                    'last_modified_timestamp' => $disk->lastModified($f),
                    'date_created' => Carbon::createFromTimestamp($disk->lastModified($f))->toDateTimeString(),
                    'date_ago' => Carbon::createFromTimestamp($disk->lastModified($f))->diffForHumans(Carbon::now()),
                ];
            }
        }
        // Sort newest first
        $backups = array_reverse($backups);
        // Pagination
        $perPage = $request->input('length', 10);
        $start = $request->input('start', 0);
        $total = count($backups);
        $paged = array_slice($backups, $start, $perPage);
        $data = [];
        foreach ($paged as $key => $backup) {
            $data[] = [
                // Show the highest number for the newest (top) row, descending
                'index' => $total - ($start + $key),
                'file_name' => $backup['file_name'],
                'file_size' => $backup['file_size'],
                'date_created' => formatDate($backup['date_created']),
                'date_ago' => $backup['date_ago'],
                'action' => view('backend.backups.partials.action_column', ['backup' => $backup])->render(),
            ];
        }
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => count($backups),
            'recordsFiltered' => count($backups),
            'data' => $data,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    /**
     * Rollback a specific activity log entry.
     * Restores the old property values from the activity log.
     * Only super admin can rollback.
     */
    public function rollback($id)
    {
        if (!auth()->user()->hasRole(['admin', 'demo_admin'])) {
            return response()->json(['message' => 'Unauthorized. Only super admin can rollback.', 'status' => false], 403);
        }

        try {
            DB::beginTransaction();

            $activity = Activity::findOrFail($id);
            $properties = $activity->properties;

            if (!isset($properties['old']) || empty($properties['old'])) {
                return response()->json(['message' => 'No old data available to rollback.', 'status' => false], 400);
            }

            $subjectType = $activity->subject_type;
            $subjectId = $activity->subject_id;

            if (!$subjectType || !$subjectId) {
                return response()->json(['message' => 'No subject associated with this activity log.', 'status' => false], 400);
            }

            $model = $subjectType::find($subjectId);
            if (!$model) {
                return response()->json(['message' => 'The subject record no longer exists.', 'status' => false], 404);
            }

            $oldData = (array) $properties['old'];
            $fillable = $model->getFillable();

            $restorable = array_intersect_key($oldData, array_flip($fillable));
            if (empty($restorable)) {
                return response()->json(['message' => 'No rollback-able fields found.', 'status' => false], 400);
            }

            $model->update($restorable);

            activity()
                ->performedOn($model)
                ->causedBy(auth()->user())
                ->withProperties([
                    'attributes' => $model->toArray(),
                    'old' => $oldData,
                    'rollback_from_activity' => $activity->id,
                ])
                ->event('rollback')
                ->log('rollback_' . $activity->event);

            DB::commit();

            return response()->json(['message' => 'Rollback completed successfully.', 'status' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Rollback failed: ' . $e->getMessage(), 'status' => false], 500);
        }
    }

    public function activityLogView($id)
    {

        $html = '';
        $module_title = $this->module_title;
        $module_name = $this->module_name;
        $module_path = $this->module_path;
        $module_icon = $this->module_icon;
        $module_model = $this->module_model;
        $module_name_singular = Str::singular($module_name);
        $$module_name_singular = ActivityLog::where('id', '=', $id)->first();
        if (isset($$module_name_singular) && !empty($$module_name_singular)) {
            $PropertiesData = !empty($$module_name_singular['properties']) ? json_decode($$module_name_singular['properties']) : NULL;

            $newData = (isset($PropertiesData->attributes) && !empty($PropertiesData->attributes)) ? $PropertiesData->attributes : NULL;
            $oldData = (isset($PropertiesData->old) && !empty($PropertiesData->old)) ? $PropertiesData->old : NULL;



            $html = '<div class="col-lg-12">';
            $html .= '<div class="row">';
            $html .= '<div class="col-lg-6">';
            $html .= '<h5>' . __('messages.new_data') . ' </h5> <hr>';
            if (!empty($newData)) {
                foreach ($newData as $key => $value) {
                    if ($key == 'user_id' || $key == 'doctor_id' || $key == 'patient_id' || $key == 'otherpatient_id' || $key == 'created_by' || $key == 'updated_by' || $key == 'deleted_by') {
                        $user = User::find($value);
                        $html .= '<p> <span class="h6 m-0">' . ucwords(str_replace('_', ' ', $key)) . '</span> : ' . ($user ? $user->first_name . ' ' . $user->last_name : '-') . '</p>';
                    } elseif ($key == 'reason') {

                        $key = 'Cancellation Reason';

                        $html .= '<p> <span class="h6 m-0"> ' . ucwords(str_replace('_', ' ', $key)) . ' </span> : ' . ($value) . '</p>';
                    } elseif ($key == 'clinic_id') {
                        $clinic = Clinics::find($value);
                        $html .= '<p> <span class="h6 m-0"> ' . ucwords(str_replace('_', ' ', $key)) . ' </span> : ' . ($clinic ? $clinic->name : '-') . '</p>';
                    } elseif ($key == 'service_id') {
                        $service = ClinicsService::find($value);
                        $html .= '<p> <span class="h6 m-0"> ' . ucwords(str_replace('_', ' ', $key)) . ' </span> : ' . ($service ? $service->name : '-') . '</p>';
                    } elseif ($key == 'created_at' || $key == 'updated_at'  || $key == 'start_date_time') {
                        $html .= '<p> <span class="h6 m-0"> ' . ucwords(str_replace('_', ' ', $key)) . ' </span> : ' . formatDate($value) . '</p>';
                    } elseif ($key == 'appointment_time') {
                        $setting = Setting::where('name', 'time_formate')->first();
                        $timeformate = $setting ? $setting->val : 'h:i A';
                        $formattedTime = $value ? Carbon::parse($value)->format($timeformate) : '';
                        $html .= '<p> <span class="h6 m-0"> ' . ucwords(str_replace('_', ' ', $key)) . ' </span> : ' . $formattedTime . '</p>';
                    } elseif ($key == 'appointment_date') {
                        $setting = Setting::where('name', 'date_formate')->first();
                        $dateformate = $setting ? $setting->val : 'Y-m-d';
                        $formattedDate = $value ? Carbon::parse($value)->format($dateformate) : '';
                        $html .= '<p> <span class="h6 m-0"> ' . ucwords(str_replace('_', ' ', $key)) . ' </span> : ' . $formattedDate . '</p>';
                    } elseif ($key == 'start_video_link' || $key == 'tax_percentage' || $key == 'inclusive_tax') {
                        $html .= '<p class="text-break"> <span class="h6 m-0"> ' . ucwords(str_replace('_', ' ', $key)) . ' </span> : ' . $value . '</p>';
                    } else {
                        $html .= '<p> <span class="h6 m-0"> ' . ucwords(str_replace('_', ' ', $key)) . ' </span> : ' . $value . '</p>';
                    }
                }
            }
            $html .= '</div>';
            $html .= '<div class="col-lg-6">';
            $html .= '<h5>' . __('messages.old_data') . ' </h5> <hr>';
            if (!empty($oldData)) {
                foreach ($oldData as $key => $value) {
                    if ($key == 'user_id' || $key == 'doctor_id' || $key == 'patient_id' || $key == 'otherpatient_id' || $key == 'created_by' || $key == 'updated_by' || $key == 'deleted_by') {
                        $user = User::find($value);
                        $html .= '<p> <span class="h6 m-0">' . ucwords(str_replace('_', ' ', $key)) . '</span> : ' . ($user ? $user->first_name . ' ' . $user->last_name : '-') . '</p>';
                    } elseif ($key == 'clinic_id') {
                        $clinic = Clinics::find($value);
                        $html .= '<p> <span class="h6 m-0"> ' . ucwords(str_replace('_', ' ', $key)) . ' </span> : ' . ($clinic ? $clinic->name : '-') . '</p>';
                    } elseif ($key == 'service_id') {
                        $service = ClinicsService::find($value);
                        $html .= '<p> <span class="h6 m-0"> ' . ucwords(str_replace('_', ' ', $key)) . ' </span> : ' . ($service ? $service->name : '-') . '</p>';
                    } elseif ($key == 'created_at' || $key == 'updated_at' || $key == 'start_date_time') {
                        $html .= '<p> <span class="h6 m-0"> ' . ucwords(str_replace('_', ' ', $key)) . ' </span> : ' . formatDate($value) . '</p>';
                    } elseif ($key == 'appointment_time') {
                        $setting = Setting::where('name', 'time_formate')->first();
                        $timeformate = $setting ? $setting->val : 'h:i A';
                        $formattedTime = $value ? Carbon::parse($value)->format($timeformate) : '';
                        $html .= '<p> <span class="h6 m-0"> ' . ucwords(str_replace('_', ' ', $key)) . ' </span> : ' . $formattedTime . '</p>';
                    } elseif ($key == 'appointment_date') {
                        $setting = Setting::where('name', 'date_formate')->first();
                        $dateformate = $setting ? $setting->val : 'Y-m-d';
                        $formattedDate = $value ? Carbon::parse($value)->format($dateformate) : '';
                        $html .= '<p> <span class="h6 m-0"> ' . ucwords(str_replace('_', ' ', $key)) . ' </span> : ' . $formattedDate . '</p>';
                    } elseif ($key == 'start_video_link' || $key == 'tax_percentage') {
                        $html .= '<p class="text-break"> <span class="h6 m-0"> ' . ucwords(str_replace('_', ' ', $key)) . ' </span> : ' . $value . '</p>';
                    } else {
                        $html .= '<p> <span class="h6 m-0"> ' . ucwords(str_replace('_', ' ', $key)) . ' </span> : ' . $value . '</p>';
                    }
                }
            }
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }

        return $html;
    }
}
