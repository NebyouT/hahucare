<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BodyChartSetting;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Socialite;
use Google\Client;
use Google\Service\Oauth2;
use Google_Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
class SettingController extends Controller
{
    public function __construct()
    {
        // Page Title
        $this->module_title = 'settings.title';

        // module name
        $this->module_name = 'settings';

        // module icon
        $this->module_icon = 'fas fa-cogs';

        view()->share([
            'module_title' => $this->module_title,
            'module_name' => $this->module_name,
            'module_icon' => $this->module_icon,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $module_action = 'List';
        

        return view('backend.settings.index', compact('module_action'));
    }

    public function index_data(Request $request)
    {
        if (!isset($request->fields)) {
            return response()->json($data, 404);
        }
        // dd($request->fields);
        $fields = explode(',', $request->fields);
        $data = Setting::whereIn('name', $fields)->get();

        $responseData = [];
        foreach ($data as $setting) {
            $field = $setting->name;
            $value = $setting->val;

            // Process specific fields, like asset URLs
            if (in_array($field, ['logo', 'mini_logo', 'dark_logo', 'dark_mini_logo', 'favicon'])) {
                $value = asset($value);
            }

            $responseData[$field] = $value;
        }
        $responseData['clinic_booking_url'] = route('app.quick-booking');
        // dd($responseData);
        return response()->json($responseData, 200);
    }

    public function store(Request $request)
    {
        if($request->body_chart_images){

            $this->bodychartSetting($request);
        }
        $data = $request->all();
        if ($request->has('json_file')) {
            $file = $request->file('json_file');
            $fileName = $file->getClientOriginalName();
            $directoryPath = storage_path('app/data');

            if (!File::isDirectory($directoryPath)) {
                File::makeDirectory($directoryPath, 0777, true, true);
            }

            $files = File::files($directoryPath);

            foreach ($files as $existingFile) {
                if (strtolower($existingFile->getExtension()) === 'json') {
                    File::delete($existingFile->getPathname());
                }
            }
            $file->move($directoryPath, $fileName);
        }

        unset($data['json_file']);
        if ($request->wantsJson()) {
            $rules = Setting::getSelectedValidationRules(array_keys($request->all()));
        } else {
            $rules = Setting::getValidationRules();
        }

        $data = $this->validate($request, $rules);

        $validSettings = array_keys($rules);

        foreach ($data as $key => $val) {
            if (in_array($key, $validSettings)) {
                $mimeTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/vnd.microsoft.icon'];
                if (gettype($val) == 'object') {
                    if ($val->getType() == 'file' && in_array($val->getmimeType(), $mimeTypes)) {
                        $setting = Setting::add($key, '', Setting::getDataType($key), Setting::getType($key));
                        $mediaItems = $setting->addMedia($val)->toMediaCollection($key);
                        $setting->update(['val' => $mediaItems->getUrl()]);
                    }
                } else {
                    $setting = Setting::add($key, $val, Setting::getDataType($key), Setting::getType($key));
                }
            }
        }

        // Set app locale if default_language is present
        if (isset($data['default_language'])) {
            $locale = $data['default_language'];
            app()->call('App\\Http\\Controllers\\LanguageController@switch', ['language' => $locale]);
        }
        if ($request->wantsJson()) {
            $message = __('settings.save_setting');

            return response()->json(['message' => $message, 'status' => true], 200);
        } else {
            return redirect()->back()->with('status', __('messages.setting_save'));
        }
    }

    public function clear_cache()
    {
        Setting::flushCache();

        $message = __('messages.cache_cleard');

        return response()->json(['message' => $message, 'status' => true], 200);
    }

    public function verify_email(Request $request)
    {
        $mailObject = $request->all();
        try {
            \Config::set('mail', $mailObject);
            Mail::raw('This is a smtp mail varification test mail!', function ($message) use ($mailObject) {
                $message->to($mailObject['email'])->subject('Test Email');
            });

            return response()->json(['message' => 'Verification Successful', 'status' => true], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Verification Failed', 'status' => false], 500);
        }
    }

    public function googleKey(Request $request)
    {
        $googleMeetVal = Setting::where('name', 'google_meet_method')->first()?->val ?? null;
        $isZoomVal = Setting::where('name', 'is_zoom')->first()?->val ?? null;

        $data = ($googleMeetVal == 1 || $isZoomVal == 1) ? 1 : 0;
        return response()->json($data);
    }


    public function bodychartSetting($bodychartData)
    {
        $jsonData = $bodychartData->body_chart_images;
        $data = json_decode($jsonData, true);

        $existingIds = collect($data)->pluck('uniqueId')->filter();
        if ($existingIds->isNotEmpty()) {
            $deletedata = BodyChartSetting::whereNotIn('uniqueId', $existingIds);
            $deletedata->delete();
            //$deletedata->clearMediaCollection('bodychart_template');
        }
        if (empty($data)) {
            BodyChartSetting::truncate();
        }
        foreach ($data as $index => $item) {
            if (isset($item['uniqueId'])) {
                $existingBodyChart = BodyChartSetting::where('uniqueId', $item['uniqueId'])->first();
            }
            if ($existingBodyChart) {
                $existingBodyChart->name = $item['name'];
                $existingBodyChart->default = $item['default'];
                $existingBodyChart->image_name=$item['image_name'];
                $existingBodyChart->save();
                if ($bodychartData->hasFile($index)) {
                    $existingBodyChart->clearMediaCollection('bodychart_template');
                    $existingBodyChart->addMedia($bodychartData->$index)->toMediaCollection('bodychart_template');
                    $existingBodyChart->full_url = $existingBodyChart->getFirstMediaUrl('bodychart_template');
                    $existingBodyChart->save();
                }
            } else {
                $bodyChart = BodyChartSetting::create([
                    'name' => $item['name'],
                    'default' => $item['default'],
                    'uniqueId' => $item['uniqueId'],
                    'image_name'=>$item['image_name'],
                ]);
                if ($bodychartData->hasFile($index)) {
                $bodyChart->addMedia($bodychartData->$index)->toMediaCollection('bodychart_template');
                $bodyChart->full_url = $bodyChart->getFirstMediaUrl('bodychart_template');
                $bodyChart->save();
                }
            }
        }
    }
    public function googleId(Request $request)
    {
        $setting = Setting::where('type', 'google_meet_method')->where('name', 'google_clientid')->first();
        
        $redirectUri = env('GOOGLE_REDIRECT');
        $clientId = env('GOOGLE_CLIENT_ID');
        $clientSecret = env('GOOGLE_CLIENT_SECRET');
        
        // Enhanced debugging
        \Log::info('Google OAuth Configuration Check:', [
            'redirect_uri' => $redirectUri,
            'redirect_uri_empty' => empty($redirectUri),
            'client_id' => $clientId ? substr($clientId, 0, 10) . '...' : 'NULL',
            'client_id_empty' => empty($clientId),
            'has_secret' => !empty($clientSecret),
            'secret_empty' => empty($clientSecret),
            'env_file_exists' => file_exists(base_path('.env')),
            'app_url' => env('APP_URL'),
        ]);
        
        $missingVars = [];
        if (empty($clientId)) $missingVars[] = 'GOOGLE_CLIENT_ID';
        if (empty($clientSecret)) $missingVars[] = 'GOOGLE_CLIENT_SECRET';
        if (empty($redirectUri)) $missingVars[] = 'GOOGLE_REDIRECT';
        
        if (!empty($missingVars)) {
            $errorMessage = 'Missing Google OAuth configuration: ' . implode(', ', $missingVars);
            \Log::error($errorMessage, [
                'missing_vars' => $missingVars,
                'instructions' => 'Add these variables to your .env file and run: php artisan config:clear'
            ]);
            
            return response()->json([
                'error' => 'Google OAuth is not configured',
                'message' => $errorMessage,
                'missing_variables' => $missingVars,
                'instructions' => [
                    '1. Add the following to your .env file:',
                    'GOOGLE_CLIENT_ID=your_client_id_here',
                    'GOOGLE_CLIENT_SECRET=your_client_secret_here',
                    'GOOGLE_REDIRECT=https://yourdomain.com/app/auth/google/callback',
                    '2. Run: php artisan config:clear',
                    '3. Get credentials from: https://console.cloud.google.com/apis/credentials'
                ]
            ], 500);
        }
        
        try {
            $client = new Client([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'scopes' => ['https://www.googleapis.com/auth/calendar.events', 'https://www.googleapis.com/auth/userinfo.email'],
                'client_secret' => $clientSecret,
                'access_type' => 'offline',
                'prompt' => 'consent',
            ]);
            $authUrl = $client->createAuthUrl();
            
            \Log::info('Google OAuth Auth URL Generated Successfully:', [
                'url' => $authUrl,
                'redirect_uri' => $redirectUri
            ]);
            
            return response()->json($authUrl);
        } catch (\Exception $e) {
            \Log::error('Google OAuth Client Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'error' => 'Failed to initialize Google OAuth',
                'message' => $e->getMessage(),
                'details' => 'Check server logs for more information'
            ], 500);
        }
    }

    public function handleGoogleCallback(Request $request)
    {
        \Log::info('Google OAuth Callback Started', [
            'has_code' => $request->filled('code'),
            'has_error' => $request->has('error'),
            'error' => $request->get('error'),
            'user_id' => Auth::id(),
            'full_url' => $request->fullUrl()
        ]);

        try {
            $client = new Google_Client();

            $clientId = env('GOOGLE_CLIENT_ID');
            $clientSecret = env('GOOGLE_CLIENT_SECRET');
            $redirectUri = env('GOOGLE_REDIRECT');

            if (empty($clientId) || empty($clientSecret) || empty($redirectUri)) {
                \Log::error('Google OAuth Callback: Missing credentials');
                return redirect('/app/profile')->with('error', 'Google OAuth is not configured properly. Please contact administrator.');
            }

            $client->setClientId($clientId);
            $client->setClientSecret($clientSecret);
            $client->setRedirectUri($redirectUri);
            $client->setAccessType('offline');
            $client->setPrompt('consent');
            $client->addScope('https://www.googleapis.com/auth/calendar.events');
            $client->addScope('https://www.googleapis.com/auth/userinfo.email');

            if (!$request->filled('code')) {
                \Log::info('No code provided, redirecting to Google auth');
                return redirect()->to($client->createAuthUrl());
            }

            \Log::info('Authenticating with Google using code');
            
            // Exchange authorization code for access token
            $client->authenticate($request->input('code'));
            $accessToken = $client->getAccessToken();

            \Log::info('Access token received', [
                'has_access_token' => isset($accessToken['access_token']),
                'has_refresh_token' => isset($accessToken['refresh_token']),
                'expires_in' => $accessToken['expires_in'] ?? null
            ]);

            // Extract access token and expiration time
            $token = $accessToken['access_token'];
            $expiresIn = $accessToken['expires_in'];
            $refreshToken = $accessToken['refresh_token'] ?? null;

            // Calculate expiration timestamp (expires_in is in seconds from now)
            $expiresAt = now()->addSeconds($expiresIn);

            // Store the access token and expiration timestamp in the user's table
            $user = Auth::user();
            $user->google_access_token = json_encode($accessToken);
            $user->google_refresh_token = $refreshToken;
            $user->token_expires_at = $expiresAt;
            $user->is_telmet = 1;
            $user->save();

            \Log::info('Google OAuth completed successfully', [
                'user_id' => $user->id,
                'has_refresh_token' => !empty($refreshToken),
                'expires_at' => $expiresAt
            ]);

            // Redirect back to profile with success message and set localStorage
            return redirect('/app/profile')->with('success', 'Google account connected successfully!')
                ->cookie('google_oauth_success', 'true', 1);
        } catch (\Exception $e) {
            \Log::error('Google OAuth Callback Error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect('/app/profile')->with('error', 'Failed to connect Google account: ' . $e->getMessage());
        }
    }

    public function storeToken(Request $request)
    {
        $user = auth()->user(); // Assuming the user is authenticated
        $user->google_access_token = json_encode($request->all()); // Convert array to JSON
        $user->is_telmet = $request->is_telmet;
        $user->save();

        return response()->json(['message' => 'Token stored successfully']);
    }

    public function revokeToken(Request $request)
    {
        $user = auth()->user(); // Assuming the user is authenticated
        $user->google_access_token = null;
        $user->is_telmet = 0;
        $user->save();

        return response()->json(['message' => 'Logged out successfully']);
    }
    public function downloadJson(){
        $directory = 'data';
        $files = Storage::files($directory);
        $jsonFiles = array_map(function($file) {
            return pathinfo($file, PATHINFO_BASENAME);
        }, array_filter($files, function($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'json';
        }));
        return response()->json($jsonFiles);
    }

}
