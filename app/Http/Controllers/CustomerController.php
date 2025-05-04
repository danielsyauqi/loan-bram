<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Products;
use App\Models\Salaries;
use App\Models\Addresses;
use App\Models\LoanModule;
use App\Models\Employments;
use App\Models\LoanModules;
use App\Models\Redemptions;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\WorkflowRemarks;
use App\Models\LoanApplications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class CustomerController extends Controller
{
    /**
     * Display the customer dashboard.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            // Get all loan applications for the current user
            $applications = LoanApplications::where('customer_id', $user->id)
                ->with('module', 'product')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Get all active loan modules
            $modules = LoanModules::where('status', 'Active')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Calculate metrics
            $metrics = [
                'total' => $applications->count(),
                'new' => $applications->where('status', 'New')->count(),
                'processing' => $applications->whereIn('status', ['Processing', 'Pending', 'Pending@Agency', 'Pending@Bank', 'Ready to Submit'])->count(),
                'approved' => $applications->where('status', 'Approved')->where('status', 'Disbursed')->count(),
                'rejected' => $applications->whereIn('status', ['Rejected','Delete Request'])->count(),
            ];
            
            return Inertia::render('CustomerDashboard', [
                'applications' => $applications,
                'modules' => $modules,
                'metrics' => $metrics,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            
            return Inertia::render('CustomerDashboard', [
                'applications' => [],
                'modules' => [],
                'metrics' => [
                    'total' => 0,
                    'new' => 0,
                    'processing' => 0,
                    'approved' => 0,
                    'rejected' => 0,
                    'disbursed' => 0,
                ],
                'error' => 'Failed to load dashboard data'
            ]);
        }
    }

    public function show($referenceNumber)
    {
        try {
            $application = LoanApplications::where('reference_id', $referenceNumber)->first();
            
            // If application doesn't exist, create empty default array
            if (!$application) {
                return Inertia::render('Customer/Applications/Show', [
                    'application' => [],
                    'module' => [
                        'title' => 'Pending',
                        'description' => '',
                        'banner' => asset('images/loan-modules/default.png'),
                        'interestRate' => 'N/A',
                        'minAmount' => 0,
                        'maxAmount' => 0,
                        'tenure' => 'N/A',
                        'status' => 'Pending',
                    ],
                    'product' => [],
                    'agents' => [],
                    'sub_agent' => [],
                    'workflow_remarks' => [],
                    'customer' => [],
                    'address' => [],
                    'employment' => [],
                    'salary' => [],
                    'redemption' => [],
                    'currentSubAgent' => [],
                    'companyAddress' => []
                ]);
            }
            
            $customer = User::where('id', $application->customer_id)->first();
            $address = Addresses::where('user_id', $application->customer_id)->first();
            $employment = Employments::where('user_id', $application->customer_id)->first();
            $companyAddress = $employment ? Addresses::where('employment_id', $employment->id)->first() : null;
            $salary = Salaries::where('customer_id', $application->customer_id)->first();
            $redemption = Redemptions::where('customer_id', $application->customer_id)->first();
            $module = LoanModules::where('id', $application->module_id)->first();
            $product = Products::where('id', $application->product_id)->first();
            $agent = User::where('id', $application->agent_id)->first();
            $sub_agent = User::where('role' , 'sub agent')->get();
            $currentSubAgent = User::where('id', $application->sub_agent_id)->first();
            $workflow_remarks = WorkflowRemarks::where('application_id', $application->id)->get();

            // Attach user name and role to each workflow remark
            $workflow_remarks = $workflow_remarks->map(function ($remark) {
                $user = User::find($remark->user_id);
                $remark->user_name = $user ? $user->name : null;
                $remark->user_role = $user ? $user->role : null;
                return $remark;
            });
            
            return Inertia::render('Customer/Applications/Show', [
                'application' => $application ?? [],
                'module' => [
                    'title' => $module->name ?? 'Pending',
                    'description' => $module->description ?? '',
                    'banner' => $module && $module->logo ? asset($module->logo) : asset('images/loan-modules/default.png'),
                    'interestRate' => $this->calculateInterestRate($module) ?? 'N/A',
                    'minAmount' => $module ? $module->products()->min('minimum_loan') ?? 0 : 0,
                    'maxAmount' => $module ? $module->products()->max('maximum_loan') ?? 0 : 0,
                    'tenure' => $this->calculateTenure($module) ?? 'N/A',
                ],
                'product' => $product ?? [],
                'agents' => $agent ?? [],
                'sub_agent' => $sub_agent ?? [],
                'workflow_remarks' => $workflow_remarks ?? [],
                'customer' => $customer ?? [],
                'address' => $address ?? [],
                'employment' => $employment ?? [],
                'currentSubAgent' => $currentSubAgent ?? [],
                'salary' => $salary ?? [],
                'redemption' => $redemption ?? [],
                'companyAddress' => $companyAddress ?? []
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Application view error: ' . $e->getMessage());
            
            // Return a friendly error view
            return Inertia::render('Customer/Applications/Show', [
                'application' => [],
                'module' => [
                    'title' => 'Error',
                    'description' => 'There was an error loading the application data',
                    'banner' => asset('images/loan-modules/default.png'),
                    'interestRate' => 'N/A',
                    'minAmount' => 0,
                    'maxAmount' => 0,
                    'tenure' => 'N/A',
                    'status' => 'Error',
                ],
                'error' => 'Unable to load application details. Please try again later.',
                'product' => [],
                'agents' => [],
                'sub_agent' => [],
                'currentSubAgent' => [],
                'workflow_remarks' => [],
                'customer' => [],
                'address' => [],
                'employment' => [],
                'salary' => [],
                'redemption' => [],
                'companyAddress' => []
            ]);
        }
    }

    /**
     * Calculate interest rate for a module
     */
    private function calculateInterestRate($module)
    {
        // Check if module is null
        if (!$module) {
            return 'N/A';
        }
        
        // Get all products for this module
        $products = $module->products()->get();
        
        // Collect all rate values from all products
        $allRates = [];
        foreach ($products as $product) {
            if ($product->rate) {
                // Decode the JSON string to get the array of rates
                $rateArray = json_decode($product->rate, true);
                if (is_array($rateArray)) {
                    // Add all numeric values to our collection
                    foreach ($rateArray as $rate) {
                        if (is_numeric($rate)) {
                            $allRates[] = (float)$rate;
                        }
                    }
                }
            }
        }                 
        // Check if we have any valid rates
        if (empty($allRates)) {
            return 'N/A';
        } else {
            $min = min($allRates);
            $max = max($allRates);
            // Format the numbers to 2 decimal places
            $min = number_format($min, 2, '.', '');
            $max = number_format($max, 2, '.', '');
            return $min === $max ? "$min%" : "$min% - $max%";
        }
    }

    /**
     * Calculate tenure for a module
     */
    private function calculateTenure($module)
    {
        // Check if module is null
        if (!$module) {
            return 'N/A';
        }
        
        $text = "";
        if($module->products()->min('tenure') == $module->products()->max('tenure')){
            $text = ($module->products()->min('tenure') ?? 0) . ' Years';
        }else{
            $text = ($module->products()->min('tenure') ?? 0) . ' Years - ' . ($module->products()->max('tenure') ?? 0) . ' Years';
        }
        return $text;
    }

    public function create()
    {   
        $user = Auth::user();
        $customer = User::where('id', $user->id)->first();
        $address = Addresses::where('user_id', $user->id)->first() ?? [];
        $user_address = ($address->address_line_1 ?? '') . 
        (isset($address->address_line_2) ? ', ' . $address->address_line_2 : '') . 
        (isset($address->city) ? ', ' . $address->city : '') . 
        (isset($address->state) ? ', ' . $address->state : '') . 
        (isset($address->zip) ? ', ' . $address->zip : '') . 
        (isset($address->country) ? ', ' . $address->country : '');
        $employment = Employments::where('user_id', $user->id)->first() ?? [];
        $employmentAddress = Addresses::where('employment_id', $employment->id ?? 0)->first() ?? [];
        $employment_address = ($employmentAddress->address_line_1 ?? '') . 
        (isset($employmentAddress->address_line_2) ? ', ' . $employmentAddress->address_line_2 : '') . 
        (isset($employmentAddress->city) ? ', ' . $employmentAddress->city : '') . 
        (isset($employmentAddress->state) ? ', ' . $employmentAddress->state : '') . 
        (isset($employmentAddress->zip) ? ', ' . $employmentAddress->zip : '') . 
        (isset($employmentAddress->country) ? ', ' . $employmentAddress->country : '');
        $salary = Salaries::where('customer_id', $user->id)->first() ?? [];
        $redemption = Redemptions::where('customer_id', $user->id)->first() ?? [];
       
        $loanStatus = false;
        $loanApplicationLatest = LoanApplications::where('customer_id', $user->id)->latest()->first();

        if($loanApplicationLatest){
            if($loanApplicationLatest->status === 'Processing' || $loanApplicationLatest->status === 'Pending' || $loanApplicationLatest->status === 'Pending@Agency' || $loanApplicationLatest->status === 'Pending@Bank' )
            {
                $loanStatus = false;
            }else{
                $loanStatus = true;
            }
        }else{
            $loanStatus = true;
        }


        return Inertia::render('Customer/Applications/Create', [
            'customer' => $customer ?? [],
            'address' => $user_address ?? '',
            'employment' => $employment ?? [],
            'employment_address' => $employment_address ?? '',
            'salary' => $salary ?? [],
            'redemption' => $redemption ?? [],
            'loanStatus' => $loanStatus ?? false,
            'flash' => [
                'success' => session('flash.success', ''),
                'error' => session('flash.error', '')
            ]
        ]);
    }

    public function store(Request $request)
    {
        try {
            // Generate a unique reference ID with random string prefix
            do {
                $randomStr = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 3));
                $randomNum = mt_rand(100000, 999999);
                $reference_id = $randomStr . '-' . $randomNum;
            } while (LoanApplications::where('reference_id', $reference_id)->exists());
            
            $user = Auth::user();
            $application = new LoanApplications();
            $application->customer_id = $user->id;
            $application->reference_id = $reference_id;
            $application->status = 'New';
            $application->date_received = now();
            $application->save();



            foreach(User::where('role', 'Admin')->get() as $role){
                    Log::info('Notification excuted');
                    $notification = new Notification();
                    $notification->status = 'unread';
                    $notification->sender_id = $user->id;
                    $notification->receiver_id = $role->id;
                    $notification->reference_id = $reference_id;
                    $notification->message = 'New application created by '  . $user->username  . ' using reference ID #'  . $reference_id;
                    $notification->save();
               
            }

            WorkflowRemarks::create([
                'application_id' => $application->id,
                'remarks' => $request->request_details,
                'user_id' => $user->id,
                'status' => 'New',
            ]);

            

            // Return with structured success flash message
            return redirect()->route('customer.applications.new')
                ->with('flash', [
                    'success' => $request->reference_id,
                    'referenceId' => $reference_id
                ]);
                
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Application creation error: ' . $e->getMessage());
            
            // Return with structured error flash message
            return redirect()->route('customer.applications.new')
                ->with('flash', [
                    'error' => 'Failed to create application. Please try again.'
                ]);
        }
       
    }

    /**
     * Display a listing of the customer's applications
     */
    public function applications()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return redirect()->route('login');
            }

            
            
            // Get all loan applications for the current user with related data
            try {
                $applications = LoanApplications::where('customer_id', $user->id)
                    ->with(['module', 'product'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($application) {
                        // Format module data
                        
                        // Format product data
                        $formattedProduct = null;
                        
                        // Return the formatted application
                        return [
                            'id' => $application->id,
                            'reference_id' => $application->reference_id,
                            'amount_applied' => $application->amount_applied,
                            'amount_approved' => $application->amount_approved,
                            'date_received' => $application->date_received,
                            'date_submitted' => $application->date_submitted,
                            'date_approved' => $application->date_approved,
                            'date_rejected' => $application->date_rejected,
                            'date_disbursed' => $application->date_disbursed,
                            'status' => $application->status,
                            'created_at' => $application->created_at,
                            'updated_at' => $application->updated_at,
                        ];
                    });

                // Get module and product data for the view
                $module = null;
                $product = null;
                
                // Update each application to include its module and product
                $applications = $applications->map(function ($application) {
                    $loanApp = LoanApplications::find($application['id']);
                    if ($loanApp) {
                        $application['module'] = $loanApp->module;
                        $application['product'] = $loanApp->product;
                    }
                    return $application;
                });
            
                
                return Inertia::render('Customer/Applications/Index', [
                    'applications' => $applications,
                    'module' => $module,
                    'product' => $product,
                    'flash' => [
                        'success' => session('flash.success', ''),
                        'error' => session('flash.error', '')
                    ]
                ]);
            } catch (\Exception $e) {
                Log::error('Error fetching customer applications: ' . $e->getMessage());
                
                return Inertia::render('Customer/Applications/Index', [
                    'applications' => [],
                    'module' => null,
                    'product' => null,
                    'flash' => [
                        'success' => '',
                        'error' => 'Failed to load applications data. Please try again.'
                    ]
                ]);
            }
            
        } catch (\Exception $e) {
            // Log the error
            Log::error('Customer applications index error: ' . $e->getMessage());
            
            // Return with empty data and error message
            return Inertia::render('Customer/Applications/Index', [
                'applications' => [],
                'error' => 'Failed to load applications. Please try again later.',
                'flash' => [
                    'success' => '',
                    'error' => 'Failed to load applications. Please try again later.'
                ]
            ]);
        }
    }

    /**
     * Display the specified application
     */
    public function showApplication($id)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return redirect()->route('login');
            }
            
            // Find the application by ID and verify it belongs to the current user
            $application = LoanApplications::where('id', $id)
                ->where('customer_id', $user->id)
                ->first();
                
            // If application doesn't exist or doesn't belong to the user
            if (!$application) {
                return redirect()->route('customer.applications.index')
                    ->with('flash', [
                        'error' => 'Application not found or you do not have permission to view it.'
                    ]);
            }
            
            // Get all related data
            $address = Addresses::where('user_id', $application->customer_id)->first();
            $employment = Employments::where('user_id', $application->customer_id)->first();
            $companyAddress = $employment ? Addresses::where('employment_id', $employment->id)->first() : null;
            $salary = Salaries::where('customer_id', $application->customer_id)->first();
            $redemption = Redemptions::where('customer_id', $application->customer_id)->first();
            $module = LoanModules::where('id', $application->module_id)->first();
            $product = Products::where('id', $application->product_id)->first();
            $agent = User::where('id', $application->agent_id)->first();
            $sub_agent = User::where('id', $application->sub_agent_id)->first();
            $workflow_remarks = WorkflowRemarks::where('application_id', $application->id)->get();
            $sub_agents = User::where('role', 'Sub Agent')->get();

            // Attach user name and role to each workflow remark
            $workflow_remarks = $workflow_remarks->map(function ($remark) {
                $user = User::find($remark->user_id);
                $remark->user_name = $user ? $user->name : null;
                $remark->user_role = $user ? $user->role : null;
                return $remark;
            });
            
            return Inertia::render('Customer/Applications/Show', [
                'application' => $application,
                'module' => [
                    'title' => $module->name ?? 'Unknown',
                    'description' => $module->description ?? '',
                    'banner' => $module && $module->logo ? asset($module->logo) : asset('images/loan-modules/default.png'),
                    'interestRate' => $this->calculateInterestRate($module) ?? 'N/A',
                    'minAmount' => $module ? $module->products()->min('minimum_loan') ?? 0 : 0,
                    'maxAmount' => $module ? $module->products()->max('maximum_loan') ?? 0 : 0,
                    'tenure' => $this->calculateTenure($module) ?? 'N/A',
                    'status' => $module->status ?? 'Unknown',
                ],
                'product' => $product ?? [],
                'agents' => $agent ?? [],
                'sub_agents' => $sub_agents ?? [],
                'workflow_remarks' => $workflow_remarks ?? [],
                'customer' => $user,
                'address' => $address ?? [],
                'employment' => $employment ?? [],
                'salary' => $salary ?? [],
                'sub_agent' => $sub_agent ?? [],
                'redemption' => $redemption ?? [],
                'companyAddress' => $companyAddress ?? []
            ]);
            
        } catch (\Exception $e) {
            // Log the error
            Log::error('Application view error: ' . $e->getMessage());
            
            return redirect()->route('customer.applications.index')
                ->with('flash', [
                    'error' => 'An error occurred while loading the application. Please try again later.'
                ]);
        }
    }

    public function identityForm()
    {
        $user = Auth::user();
        $address = Addresses::where('user_id', $user->id)->first();
        $employment = Employments::where('user_id', $user->id)->first();
        $salary = Salaries::where('customer_id', $user->id)->first();
        $redemption = Redemptions::where('customer_id', $user->id)->first();
        $companyAddress = $employment ? Addresses::where('employment_id', $employment->id)->first() : null;

        return Inertia::render('Customer/IdentityForm/IdentityForm', [
            'user' => $user,
            'address' => $address,
            'employment' => $employment,
            'companyAddress' => $companyAddress,
            'salary' => $salary,
            'redemption' => $redemption,
            'flash' => [
                'success' => session('flash.success', ''),
                'error' => session('flash.error', '')
            ]
        ]);
    }

    public function IdentityFormStore(Request $request)
    {
        try {
            $user = Auth::user();
            // Update user information
            Log::info($request->all());
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'ic_num' => $request->ic_num,
                    'bank_name' => $request->bank_name,
                    'bank_account' => $request->bank_account,
                    'phone_num' => $request->phone_num,
                ]);
            
            // Update or create address
           Addresses::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'address_line_1' => $request->address_line_1,
                    'address_line_2' => $request->address_line_2,
                    'city' => $request->city,
                    'state' => $request->state,
                    'zip' => $request->zip,
                    'country' => 'Malaysia',
                ]
            );

           

            // Update or create employment
            $employment = Employments::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $request->company_name,
                    'job_title' => $request->job_title,
                    'phone_num' => $request->phone_num_employment,
                    'bank' => $request->bank,
                    'account_num' => $request->account_num,
                    'pension' => $request->pension,
                    'emp_type' => $request->emp_type,
                ]
            );

            Addresses::updateOrCreate(
                ['employment_id' => $employment->id],
                [
                    'address_line_1' => $request->address_line_1_employment,
                    'address_line_2' => $request->address_line_2_employment,
                    'city' => $request->city_employment,
                    'state' => $request->state_employment,
                    'zip' => $request->zipcode_employment,
                    'country' => $request->country_employment,
                ]
            );
            
            // Update or create salary information
            $salary = Salaries::updateOrCreate(
                ['customer_id' => $user->id],
                [
                    'month' => $request->month,
                    'year' => $request->year,
                ]
            );

            // Handle file attachment if provided
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = time() . '_' . $user->id . '_' . $file->getClientOriginalName();

                $salaries= Salaries::where('customer_id', $user->id)->first();
                
                Log::info($salaries->attachments);
                Log::info(Storage::url($salaries->attachments));
                // Also check the 'attachments' field for backward compatibility
                if ($salaries->attachments) {
                    Storage::disk('public')->delete($salaries->attachments);
                    Log::info('File deleted successfully');

                }
                
                $path = $file->storeAs('salary_attachments', $filename, 'public');
                
                // Update both attachment and attachments fields for compatibility
                $salary->attachments = $path;
                $salary->save();
            }

            // Update or create redemption information (optional)
            if ($request->filled('bank_coop') || $request->filled('redemption_amount')) {
                Redemptions::updateOrCreate(
                    ['customer_id' => $user->id],
                    [
                        'bank_coop' => $request->bank_coop,
                        'expiry_date' => $request->expiry_date,
                        'redemption_amount' => $request->redemption_amount,
                        'monthly_installment' => $request->monthly_installment,
                        'remark' => $request->remark,
                    ]
                );
            }

            // Update user status to 'active'
            DB::table('users')
                ->where('id', $user->id)
                ->update(['status' => 'active']);
            
            return redirect()->back()->with('flash', [
                'success' => 'Your identity form has been submitted successfully! You are now eligible to apply for loans.'
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Profile update error: ' . $e->getMessage());
            
            return redirect()->back()->with('flash', [
                'error' => 'Profile update failed: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteRequest($referenceNumber, Request $request)
    {
        $application = LoanApplications::where('reference_id', $referenceNumber)->first();
        $application->status = 'Delete Request';    
        $application->save();

        $workflow_remarks = new WorkflowRemarks();
        $workflow_remarks->application_id = $application->id;
        $workflow_remarks->status = 'Delete Request';
        $workflow_remarks->remarks = "Reason for delete request: " . $request->remark;
        $workflow_remarks->save();

        foreach(User::where('role', 'Admin')->get() as $role){
            Log::info('Notification excuted');
            $notification = new Notification();
            $notification->status = 'unread';
            $notification->sender_id = Auth::user()->id;
            $notification->receiver_id = $role->id;
            $notification->reference_id = $application->id;
            $notification->message = 'Delete request sent by '  . Auth::user()->username  . ' using reference ID #'  . $application->reference_id;
            $notification->save();
       
        }

        return redirect()->route('customer.applications.index')
            ->with('flash', [
                'success' => 'Delete request sent successfully'
            ]);
    }
    
}
