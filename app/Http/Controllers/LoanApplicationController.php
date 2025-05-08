<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Products;
use App\Models\Salaries;
use App\Models\Addresses;
use App\Models\Employments;
use App\Models\LoanModules;
use App\Models\Redemptions;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\WorkflowRemarks;
use App\Models\LoanApplications;
use App\Models\SalaryAttachments;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LoanApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param  int  $moduleId
     * @return \Inertia\Response
     */
    
    public function index($moduleSlug, Request $request)
    {
        // Check if the module exists
        $module = LoanModules::where('slug', $moduleSlug)->first();
        $moduleId = $module->id;
        
        // Check if this is a search request
        $foundUser = null;
        $error = null;
        
        if ($request->isMethod('post') && $request->has('ic_number')) {

            // Remove all non-digit characters from IC number before searching
            $cleanIcNumber = preg_replace('/\D/', '', $request->ic_number);
            $request->merge(['ic_number' => $cleanIcNumber]);
            Log::info('Searching for user with IC: ' . $cleanIcNumber);
            
            // Search for user by IC number
            $user = User::where('ic_num', $cleanIcNumber)->first();
            
            if ($user) {
                // Log found user for debugging
                Log::info('User found: ' . $user->name);
                
                // Only return necessary user data
                $foundUser = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_num' => $user->phone_num,
                    'ic_num' => $user->ic_num
                ];
            } else {
                // Log not found for debugging
                Log::info('User not found for IC: ' . $request->ic_number);
                $error = 'User not found';
            }
        }
        
        $customer_id = LoanApplications::where('module_id', $moduleId)->get()->pluck('customer_id')->toArray();

        $customers = User::whereIn('id', $customer_id)->get();
        

        if(Auth::user()->role === 'admin'){
        // Get all applications for this module
        Log::info('Admin');
        $applications = LoanApplications::where('module_id', $moduleId)
            ->get()
            ->map(function ($application) {
                return [
                    'id' => $application->id ?? 0,
                    'customer_id' => $application->customer_id ?? 0,
                    'product_id' => $application->product_id ?? 0,
                    'agent_id' => $application->agent_id ?? 0,
                    'module_id' => $application->module_id ?? 0,
                    'biro' => $application->biro ?? '',
                    'banca' => $application->banca ?? '',
                    'rates' => $application->rates ?? 0,
                    'document_checklist' => $application->document_checklist ? json_decode($application->document_checklist) : [],
                    'date_received' => $application->date_received ?? null,
                    'date_rejected' => $application->date_rejected ?? null,
                    'date_approved' => $application->date_approved ?? null,
                    'date_disbursed' => $application->date_disbursed ?? null,
                    'date_submitted' => $application->date_submitted ?? null,
                    'tenure_applied' => $application->tenure_applied ?? 0,
                    'tenure_approved' => $application->tenure_approved ?? 0,
                    'amount_applied' => $application->amount_applied ?? 0,
                    'amount_approved' => $application->amount_approved ?? 0,
                    'amount_disbursed' => $application->amount_disbursed ?? 0,
                    'created_at' => $application->created_at ? $application->created_at->format('d M Y') : '',
                    'updated_at' => $application->updated_at ? $application->updated_at->format('d M Y') : '',
                    'reference_id' => $application->reference_id ?? '',
                    'customer_name' => $application->customer->name ?? '',
                    'status' => $application->status ?? '', 
                ];
            });
        }else if(Auth::user()->role === 'agent'){
            Log::info('Agent');
            $applications = LoanApplications::where('agent_id', Auth::user()->id)
            ->get()
            ->map(function ($application) {
                return [
                    'id' => $application->id ?? 0,
                    'customer_id' => $application->customer_id ?? 0,
                    'product_id' => $application->product_id ?? 0,
                    'agent_id' => $application->agent_id ?? 0,
                    'module_id' => $application->module_id ?? 0,
                    'biro' => $application->biro ?? '',
                    'banca' => $application->banca ?? '',
                    'rates' => $application->rates ?? 0,
                    'document_checklist' => $application->document_checklist ? json_decode($application->document_checklist) : [],
                    'date_received' => $application->date_received ?? null,
                    'date_rejected' => $application->date_rejected ?? null,
                    'date_approved' => $application->date_approved ?? null,
                    'date_disbursed' => $application->date_disbursed ?? null,
                    'date_submitted' => $application->date_submitted ?? null,
                    'tenure_applied' => $application->tenure_applied ?? 0,
                    'tenure_approved' => $application->tenure_approved ?? 0,
                    'amount_applied' => $application->amount_applied ?? 0,
                    'amount_approved' => $application->amount_approved ?? 0,
                    'amount_disbursed' => $application->amount_disbursed ?? 0,
                    'created_at' => $application->created_at ? $application->created_at->format('d M Y') : '',
                    'updated_at' => $application->updated_at ? $application->updated_at->format('d M Y') : '',
                    'reference_id' => $application->reference_id ?? '',
                    'customer_name' => $application->customer->name ?? '',
                    'status' => $application->status ?? '', 
                ];
            });
            
        }else if(Auth::user()->role === 'sub agent'){
            Log::info('Sub Agent');
            $applications = LoanApplications::where('agent_id', Auth::user()->id)
            ->get()
            ->map(function ($application) {
                return [
                    'id' => $application->id ?? 0,
                    'customer_id' => $application->customer_id ?? 0,
                    'product_id' => $application->product_id ?? 0,
                    'agent_id' => $application->agent_id ?? 0,
                    'module_id' => $application->module_id ?? 0,
                    'biro' => $application->biro ?? '',
                    'banca' => $application->banca ?? '',
                    'rates' => $application->rates ?? 0,
                    'document_checklist' => $application->document_checklist ? json_decode($application->document_checklist) : [],
                    'date_received' => $application->date_received ?? null,
                    'date_rejected' => $application->date_rejected ?? null,
                    'date_approved' => $application->date_approved ?? null,
                    'date_disbursed' => $application->date_disbursed ?? null,
                    'date_submitted' => $application->date_submitted ?? null,
                    'tenure_applied' => $application->tenure_applied ?? 0,
                    'tenure_approved' => $application->tenure_approved ?? 0,
                    'amount_applied' => $application->amount_applied ?? 0,
                    'amount_approved' => $application->amount_approved ?? 0,
                    'amount_disbursed' => $application->amount_disbursed ?? 0,
                    'created_at' => $application->created_at ? $application->created_at->format('d M Y') : '',
                    'updated_at' => $application->updated_at ? $application->updated_at->format('d M Y') : '',
                    'reference_id' => $application->reference_id ?? '',
                    'customer_name' => $application->customer->name ?? '',
                    'status' => $application->status ?? '', 
                ];
            });
                    
        }
        // Check if user is admin   
        $isAdmin = Auth::check() && Auth::user()->role === 'admin';

        $modulePermission = Auth::user()->module_permissions;
        $modulePermission = json_decode($modulePermission, true) ?? [];
        
        $modulePermission = array_map(function($item) {
            return (int)$item;
        }, $modulePermission);
        
        $hasModulePermission = in_array((int)$moduleId, $modulePermission);

        $modulePermissionValue = true;
        if (!$hasModulePermission) {
            $modulePermissionValue = false;
        }


        return Inertia::render('LoanApplications/Index', [
            'moduleId' => (int)$moduleId,
            'module' => [
                'id' => $module->id,
                'slug' => $module->slug,
                'title' => $module->name ?? '',
                'description' => $module->description ?? '',
                'banner' => $module->logo ? asset($module->logo) : asset('images/loan-modules/default.png'),
                'interestRate' => $this->calculateInterestRate($module),
                'minAmount' => $module->products()->min('minimum_loan') ?? 1000,
                'maxAmount' => $module->products()->max('maximum_loan') ?? 50000,
                'tenure' => $this->calculateTenure($module),
                'status' => $module->status ?? '',
            ],
            'applications' => $applications,
            'customers' => $customers,
            'foundUser' => $foundUser,
            'flash' => session()->get('flash') ?? null,
            'error' => $error,
            'isAdmin' => $isAdmin,
            'modulePermissionValue' => $modulePermissionValue,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($moduleSlug, Request $request)
    {
        $module = LoanModules::where('slug', $moduleSlug)->first();
        $moduleId = $module->id;
        
        // Check if this is a search request
        $foundUser = null;
        $error = null;
        $applications = [];
        
        if ($request->isMethod('post') && $request->has('ic_number')) {
            // Log the search request for debugging
            Log::info('Searching for user with IC: ' . $request->ic_number);
            
            // Search for user by IC number
            $user = User::where('ic_num', $request->ic_number)->first() ?? null;
            
            if ($user) {
                // Get user details
                $employments = Employments::where('user_id', $user->id)->first() ?? null;
                $salaries = Salaries::where('customer_id', $user->id)->first() ?? null;
                $employmentAddresses = $employments ? Addresses::where('employment_id', $employments->id)->first(): null;
                $addresses = Addresses::where('user_id', $user->id)->first() ?? null;
                $redemption = Redemptions::where('customer_id', $user->id)->first() ?? null;
                $applications = LoanApplications::where('customer_id', $user->id)
                    ->get();

                $user_address = ($addresses->address_line_1 ?? '') . 
                (isset($addresses->address_line_2) ? ', ' . $addresses->address_line_2 : '') . 
                (isset($addresses->city) ? ', ' . $addresses->city : '') . 
                (isset($addresses->state) ? ', ' . $addresses->state : '') . 
                (isset($addresses->zip) ? ', ' . $addresses->zip : '') . 
                (isset($addresses->country) ? ', ' . $addresses->country : '');

                $employment_address = ($employmentAddresses->address_line_1 ?? '') . 
                (isset($employmentAddresses->address_line_2) ? ', ' . $employmentAddresses->address_line_2 : '') . 
                (isset($employmentAddresses->city) ? ', ' . $employmentAddresses->city : '') . 
                (isset($employmentAddresses->state) ? ', ' . $employmentAddresses->state : '') . 
                (isset($employmentAddresses->zip) ? ', ' . $employmentAddresses->zip : '') . 
                (isset($employmentAddresses->country) ? ', ' . $employmentAddresses->country : '');
                
                // Log found user for debugging
                Log::info('User found: ' . $user->name);


                
                // Only return necessary user data
                $foundUser = [
                    'id' => $user->id ?? 0,
                    'name' => $user->name ?? '',
                    'email' => $user->email ?? '',
                    'phone_num' => $user->phone_num ?? '',
                    'bank_name' => $user->bank_name ?? '',
                    'bank_account' => $user->bank_account ?? '',
                    'role' => $user->role ?? '',
                    'status' => $user->status ?? '',
                    'ic_num' => $user->ic_num ?? '',
                    'email' => $user->email ?? '',
                    'password' => $user->password ?? '',
                    'user_photo' => $user->user_photo ?? '',

                    'job_title' => $employments->job_title ?? '',
                    'phone_num_employment' => $employments->phone_num ?? '',
                    'bank' => $employments->bank ?? '',
                    'pension' => $employments->pension ?? '',
                    'company_name' => $employments->company_name ?? '',
                    'date_joined' => $employments->date_joined ?? '',
                    'account_num' => $employments->account_num ?? '',
                    'emp_type' => $employments->emp_type ?? '',

                    'basic_income' => $salaries->basic_income ?? '',
                    'month' => $salaries->month ?? '',
                    'year' => $salaries->year ?? '',
                    'income' => $salaries->income ?? '',
                    'deduction' => $salaries->deduction ?? '',

                    'attachements' => $salaries->attachements ?? '',

                    'bank_coop' => $redemption->bank_coop ?? '',
                    'expiry_date' => $redemption->expiry_date ?? '',
                    'redemption_amount' => $redemption->redemption_amount ?? '',
                    'monthly_installment' => $redemption->monthly_installment ?? '',
                    'redemptionRemarks' => $redemption->remark ?? '',
                    
                    'user_address' => $user_address,

                    'employment_address' => $employment_address,
                ];
            } else {
                // Log not found for debugging
                Log::info('User not found for IC: ' . $request->ic_number);
                $error = 'User not found';
            }
        }
        
        // Fetch all users with role 'Agent' to populate the agent selection dropdown
        $agents = User::where('role', 'agent')
            ->orWhere('role', 'sub agent')
            ->select('id', 'name', 'email', 'phone_num', 'role')
            ->get();

        $products = Products::where('module_id', $moduleId)->get();
        $admins = User::where('role', 'admin')->get();

        
        // Always return an Inertia response
        return Inertia::render('LoanApplications/Create', [
            'applications' => $applications,
            'moduleId' => (int)$moduleId,
            'module' => [
                'id' => $module->id,
                'slug' => $module->slug,
                'title' => $module->name ?? '',
                'description' => $module->description ?? '',
                'banner' => $module->logo ? asset($module->logo) : asset('images/loan-modules/default.png'),
                'interestRate' => $this->calculateInterestRate($module),
                'minAmount' => $module->products()->min('minimum_loan') ?? 1000,
                'maxAmount' => $module->products()->max('maximum_loan') ?? 50000,
                'tenure' => $this->calculateTenure($module),
                'status' => 'Active',
                'slug' => $module->slug,
            ],
            'agents' => $agents,
            'products' => $products,
            'foundUser' => $foundUser,
            'applications' => $applications,
            'error' => $error,
            'admins' => $admins,

        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Find the user by IC number
            $user = User::where('ic_num', $request->ic_number)->first();

            if (!$user) {
                // Return to the form with the input preserved and show the error
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'User not found');
            }

            // Generate a unique reference ID with random string prefix
            do {
                $randomStr = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 3));
                $randomNum = mt_rand(100000, 999999);
                $reference_id = $randomStr . '-' . $randomNum;
            } while (LoanApplications::where('reference_id', $reference_id)->exists());

            // Create a new application if this is a create_new request or no specific flag
            $application = new LoanApplications();
            $application->reference_id = $reference_id;
            $application->customer_id = $user->id;
            $application->module_id = $request->module_id;

            // Get the module
            $module = LoanModules::where('id', $request->module_id)->first();
            
            // If we have an agent ID already, assign it
            if ($request->has('agent_id') && $request->agent_id) {
                $application->agent_id = $request->agent_id;

                //Notification for agent and customer
                $notification = new Notification();
                $notification->sender_id = Auth::user()->id;
                $notification->receiver_id = $request->agent_id;
                $notification->status = 'unread';
                $notification->message = 'You have been assigned as Agent for loan application #' . $reference_id;
                $notification->save();
            }
            
            // Save other details if provided
            if ($request->has('biro')) {
                $application->biro = $request->biro;
            }
            
            if ($request->has('banca')) {
                $application->banca = $request->banca;
            }
            
            if ($request->has('rates')) {
                $application->rates = $request->rates;
            }
            
            if ($request->has('tenure_applied')) {
                $application->tenure_applied = $request->tenure_applied;
            }
            
            if ($request->has('date_received')) {
                $application->date_received = $request->date_received;
            } else {
                $application->date_received = now();
            }
            
            if ($request->has('document_checklist') && is_array($request->document_checklist)) {
                $application->document_checklist = json_encode($request->document_checklist);
            }
            
            if ($request->has('product_id')) {
                $application->product_id = $request->product_id;
            }

            if ($request->has('for_admin')) {
                $application->admin_id = $request->for_admin;
                $notification = new Notification();
                $notification->sender_id = Auth::user()->id;
                $notification->receiver_id = $request->for_admin;
                $notification->status = 'unread';
                $notification->reference_id = $reference_id;
                $notification->message = 'You have been assigned as Admin for loan application #' . $reference_id;
                $notification->save();
            }
            
            $application->status = 'New';
            $application->save();
            
            // If there's a workflow remark provided
            if ($request->has('workflow_remarks') && !empty($request->workflow_remarks)) {
                // Create a workflow remark
                $workflowRemark = new WorkflowRemarks();
                $workflowRemark->application_id = $application->id;
                $workflowRemark->status = 'New';
                $workflowRemark->remarks = $request->workflow_remarks;
                $workflowRemark->user_id = Auth::user()->id;
                $workflowRemark->save();
            }

            //Notification for agent and customer

            $notification = new Notification();
            $notification->sender_id = Auth::user()->id;
            $notification->receiver_id = $user->id;
            $notification->status = 'unread';
            $notification->reference_id = $reference_id;
            $notification->message = 'Your new loan application has been created! Reference ID: ' . $reference_id;
            $notification->save();

            // Store the amount applied from the request
            return redirect()->route('loan-modules.applications', $module->slug)
                            ->with('flash', [
                                'success' => 'Loan application submitted successfully',
                                'referenceId' => $reference_id
                            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Loan application error: ' . $e->getMessage());
            
            // Return to the form with the input preserved and show the error
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while processing your application. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($moduleSlug, string $referenceId)
    {
        $error = null;
        $application = LoanApplications::where('reference_id', $referenceId)->first();
        $module = LoanModules::where('slug', $moduleSlug)->first();
        $moduleId = $module->id;
        $user = User::where('id', $application->customer_id)->first() ?? null;
        $employments = Employments::where('user_id', $user->id)->first() ?? null;
        $salaries = Salaries::where('customer_id', $user->id)->first() ?? null;
        $employmentAddresses = $employments ? Addresses::where('employment_id', $employments->id)->first(): null;
        $addresses = Addresses::where('user_id', $user->id)->first() ?? null;
        $redemption = Redemptions::where('customer_id', $user->id)->first() ?? null;
        $allModules = LoanModules::all();
        $user_address = ($addresses->address_line_1 ?? '') . 
            (isset($addresses->address_line_2) ? ', ' . $addresses->address_line_2 : '') . 
            (isset($addresses->city) ? ', ' . $addresses->city : '') . 
            (isset($addresses->state) ? ', ' . $addresses->state : '') . 
            (isset($addresses->zip) ? ', ' . $addresses->zip : '') . 
            (isset($addresses->country) ? ', ' . $addresses->country : '');
        $employment_address = ($employmentAddresses->address_line_1 ?? '') . 
            (isset($employmentAddresses->address_line_2) ? ', ' . $employmentAddresses->address_line_2 : '') . 
            (isset($employmentAddresses->city) ? ', ' . $employmentAddresses->city : '') . 
            (isset($employmentAddresses->state) ? ', ' . $employmentAddresses->state : '') . 
            (isset($employmentAddresses->zip) ? ', ' . $employmentAddresses->zip : '') . 
            (isset($employmentAddresses->country) ? ', ' . $employmentAddresses->country : '');
            
        //Workflow remarks
        $workflow_remarks = WorkflowRemarks::where('application_id', $application->id)->get();
        // Fetch all users with role 'Agent' to populate the agent selection dropdown


        // Retrieve agents who have permission for the current module
        $agents = User::where(function ($query) {
                $query->where('role', 'agent')
                    ->orWhere('role', 'sub agent');
            })
            ->where(function ($query) use ($moduleId) {
                $query->whereJsonContains('module_permissions', (int)$moduleId)
                      ->orWhereJsonContains('module_permissions', "$moduleId");
            })
            ->select('id', 'name', 'email', 'phone_num', 'role')
            ->get();

        $products = Products::where('module_id', $moduleId)->get();
        $admins = User::where('role', 'admin')->get();
        
        // Attach user name and role to each workflow remark
        $workflow_remarks = $workflow_remarks->map(function ($remark) {
            $user = User::find($remark->user_id);
            $remark->user_name = $user ? $user->name : null;
            $remark->user_role = $user ? $user->role : null;
            return $remark;
        });
        
        // Always return an Inertia response
        return Inertia::render('LoanApplications/Show', [
            'moduleId' => (int)$moduleId,
            'allModules' => $allModules,
            'module' => [
                'id' => $module->id,
                'slug' => $module->slug,
                'title' => $module->name ?? '',
                'description' => $module->description ?? '',
                'banner' => $module->logo ? asset($module->logo) : asset('images/loan-modules/default.png'),
                'interestRate' => $this->calculateInterestRate($module),
                'minAmount' => $module->products()->min('minimum_loan') ?? 1000,
                'maxAmount' => $module->products()->max('maximum_loan') ?? 50000,
                'tenure' => $this->calculateTenure($module),
                'status' => 'Active',
            ],
            'user' => $user,
            'employments' => $employments,
            'redemption' => $redemption,
            'salaries' => $salaries,
            'employmentAddresses' => $employmentAddresses,
            'addresses' => $addresses,
            'user_address' => $user_address,
            'employment_address' => $employment_address,
            'agents' => $agents,
            'products' => $products,
            'error' => $error,
            'application' => $application,
            'workflow_remarks' => $workflow_remarks,
            'flash' => session()->get('flash') ?? null,
            'success' => session()->get('success') ?? null,
            'admins' => $admins,
            'moduleSlug' => $module->slug,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    public function updateWorkflow($moduleId, $applicationId, $workflowId, Request $request)
    {
        $workflow = WorkflowRemarks::findOrFail($workflowId);
        $workflow->remarks = $request->remarks;
        $workflow->status = $request->status;
        $workflow->user_id = Auth::user()->id;
        $workflow->save();

        return redirect()->back()->with('success', 'Workflow updated successfully')->with('fragment', 'workflow-remarks-timeline');
    }

    public function deleteWorkflow($moduleId, $applicationId, $workflowId)
    {
        try {
            $workflow = WorkflowRemarks::findOrFail($workflowId);
            $workflow->delete();

            $application = LoanApplications::findOrFail($applicationId);
            $latestWorkflow = WorkflowRemarks::where('application_id', $applicationId)
                ->orderBy('created_at', 'desc')
                ->first();
            $application->status = $latestWorkflow ? $latestWorkflow->status : 'Pending';
            $application->save();
            
            return redirect()->back()->with('success', 'Workflow remark deleted successfully')->with('fragment', 'workflow-remarks-timeline');
            
        } catch (\Exception $e) {
            // Log the error
            Log::error('Workflow deletion error: ' . $e->getMessage());
            
            // Redirect with error message
            return redirect()->back()->with('error', 'An error occurred while deleting the workflow remark');
        }
    }

    public function addWorkflow($moduleId, $applicationId, Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'remarks' => 'required|string',
                'status' => 'required|string',
            ]);
            
            // Create a new workflow remark
            $workflow = new WorkflowRemarks();
            $workflow->application_id = $applicationId;
            $workflow->remarks = $request->remarks;
            $workflow->status = $request->status;
            $workflow->user_id = Auth::user()->id;
            $workflow->save();

            $application = LoanApplications::findOrFail($applicationId);

            if($application->status !== $request->status){
                $notification = new Notification();
                $notification->sender_id = Auth::user()->id;
                $notification->receiver_id = $application->customer_id;
                $notification->status = 'unread';
                $notification->reference_id = $application->reference_id;
                $notification->message = 'Your loan application status has been updated to ' . $request->status;
                $notification->save();
            }
            // Update the application status
            $application->status = $request->status;
            $application->save();
            
            return redirect()->back()->with('success', 'Workflow remark added successfully')->with('fragment', 'workflow-remarks-timeline');
            
        } catch (\Exception $e) {
            // Log the error
            Log::error('Workflow addition error: ' . $e->getMessage());
            
            // Redirect with error message
            return redirect()->back()->with('error', 'An error occurred while adding the workflow remark');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Auto-save specific fields of a loan application
     */
    public function saveApplication($moduleId, $applicationId, Request $request)
    {
        try {
            // Find the application
            $application = LoanApplications::findOrFail($applicationId);
            
            // Validate the request
            $validator = $request->validate([
                'agent_id' => 'nullable|exists:users,id',
                'document_checklist' => 'nullable|array',
                'rates' => 'nullable|numeric',
                'biro' => 'nullable|string',
                'banca' => 'nullable|string',
                'tenure_applied' => 'nullable|string',
                'date_received' => 'nullable|date',
                'amount_applied' => 'nullable|numeric',
                'amount_approved' => 'nullable|numeric',
                'amount_disbursed' => 'nullable|numeric',
                'tenure_approved' => 'nullable|string',
                'date_approved' => 'nullable|date',
                'date_disbursed' => 'nullable|date',
                'date_rejected' => 'nullable|date',
                'product_id' => 'nullable|exists:products,id',
                'fields' => 'nullable|array',
                'for_admin' => 'nullable|exists:users,id',
            ]);
            
            // Get all validated fields that are present in the request
            $fieldsToUpdate = $request->only([
                'rates', 'biro', 'banca', 'tenure_applied', 'date_received',
                'amount_applied', 'amount_approved', 'amount_disbursed',
                'tenure_approved', 'date_approved', 'date_disbursed', 'date_rejected', 'product_id'
            ]);
            
            // If fields array is provided, process multiple fields at once
            if ($request->has('fields') && is_array($request->fields)) {
                foreach ($request->fields as $field => $value) {
                    if (in_array($field, [
                        'rates', 'biro', 'banca', 'tenure_applied', 'date_received',
                        'amount_applied', 'amount_approved', 'amount_disbursed',
                        'tenure_approved', 'date_approved', 'date_disbursed', 'date_rejected', 'product_id'
                    ])) {
                        $fieldsToUpdate[$field] = $value;
                    }
                }
            }
            
            // Update the application with all available fields at once
            $application->fill($fieldsToUpdate);
            
            // Handle special cases that need additional processing
            if ($request->has('document_checklist')) {
                $application->document_checklist = json_encode($request->document_checklist);
            }
            
            // Handle agent assignment with notification
            if ($request->has('agent_id') && $request->agent_id !== $application->agent_id) {
                $application->agent_id = $request->agent_id;

                $notification = new Notification();
                $notification->sender_id = Auth::user()->id;
                $notification->receiver_id = $request->agent_id;
                $notification->status = 'unread';
                $notification->reference_id = $application->reference_id;
                $notification->message = 'You have been assigned as Agent for loan application #' . $application->reference_id;
                $notification->save();
                
            }

            if ($request->has('for_admin') && $request->for_admin !== $application->admin_id) {
                $application->admin_id = $request->for_admin;
                
                $notification = new Notification();
                $notification->sender_id = Auth::user()->id;
                $notification->receiver_id = $request->for_admin;
                $notification->status = 'unread';
                $notification->reference_id = $application->reference_id;
                $notification->message = 'You have been assigned as Admin for loan application #' . $application->reference_id;
                $notification->save();
            }
            
            $application->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Changes auto-saved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Auto-save error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to auto-save changes',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($applicationId)
    {
        try {
            // Check if user is admin
            if (!Auth::check() || Auth::user()->role !== 'admin') {
                return redirect()->back()->with('error', 'You do not have permission to delete applications.');
            }
            
            // Find the application
            $application = LoanApplications::findOrFail($applicationId);
            
            // Save moduleId for redirect
            $module = LoanModules::findOrFail($application->module_id);
            
            // Delete related workflow remarks
            $application->workflowRemarks()->delete();

            // Delete related notifications
            $notifications = Notification::where('reference_id', $application->reference_id)->get();
            foreach ($notifications as $notification) {
                $notification->delete();
            }
            
            // Delete the application
            $application->delete();
            
            // Redirect with success message
            return redirect()->route('loan-modules.applications', $module->slug)
                           ->with('success', 'Application has been successfully deleted.');
                           
        } catch (\Exception $e) {
            // Log the error
            Log::error('Application deletion error: ' . $e->getMessage());
            
            // Redirect with error message
            return redirect()->back()
                           ->with('error', 'An error occurred while deleting the application.');
        }
    }

    public function destroySecond( $applicationId)
    {
        try {
            // Check if user is admin
            if (!Auth::check() || Auth::user()->role !== 'admin') {
                return redirect()->back()->with('error', 'You do not have permission to delete applications.');
            }
            
            // Find the application
            $application = LoanApplications::findOrFail($applicationId);

            
            // Delete related workflow remarks
            $workflow_remarks = WorkflowRemarks::where('application_id', $application->id)->get();
            foreach ($workflow_remarks as $workflow_remark) {
                $workflow_remark->delete();
            }
            
            // Delete the application
            $application->delete();
            
            // Redirect with success message
            return redirect()->route('loan-applications.list')
                           ->with('success', 'Application has been successfully deleted.');
                           
        } catch (\Exception $e) {
            // Log the error
            Log::error('Application deletion error: ' . $e->getMessage());
            
            // Redirect with error message
            return redirect()->back()
                           ->with('error', 'An error occurred while deleting the application.');
        }
    }


    /**
     * Calculate interest rate for a module
     */
    private function calculateInterestRate($module)
    {
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
        $text = "";
        if($module->products()->min('tenure') == $module->products()->max('tenure')){
            $text = ($module->products()->min('tenure') ?? 0) . ' Years';
        }else{
            $text = ($module->products()->min('tenure') ?? 0) . ' Years - ' . ($module->products()->max('tenure') ?? 0) . ' Years';
        }
        return $text;
    }

    /**
     * Auto-save module selection
     */
    public function autoSaveModule($moduleSlug, $applicationId, Request $request)
    {
        try {
            // Find the application
            $application = LoanApplications::findOrFail($applicationId);
            
            // Validate the request
            $request->validate([
                'module_id' => 'required|exists:loan_modules,id',
            ]);
            
            // Update the module_id field
            $application->module_id = $request->module_id;
            $application->rates = null;
            $application->product_id = null;
            $application->tenure_applied = null;
            $application->save();
            
            Log::info($application);
            
            // Get the new module slug for redirection
            $newModule = LoanModules::findOrFail($request->module_id);
            $newModuleSlug = $newModule->slug;
            
            return response()->json([
                'success' => true,
                'message' => 'Module selection auto-saved and changed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Module selection auto-save error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to auto-save module selection',
            ], 500);
        }
    }   

    public function postModule($moduleSlug, $referenceId, Request $request)
    {
        try {
            // Find the application
            $application = LoanApplications::where('reference_id', $referenceId)->first();
            $moduleSlug = LoanModules::where('id', $request->module_id)->first()->slug;

            // Validate the request
            $request->validate([
                'module_id' => 'required|exists:loan_modules,id',
            ]);

            // Update the module_id field
            $application->module_id = $request->module_id;
            $application->rates = null;
            $application->product_id = null;
            $application->tenure_applied = null;

            $application->save();


            return response()->json([
                'success' => true,
                'message' => 'Module changed successfully',
                'moduleSlug' => $moduleSlug,
            ]);
        } catch (\Exception $e) {
            Log::error('Post module error', [
                'error' => $e->getMessage(),
                'reference_id' => $referenceId,
                'user_id' => Auth::user()->id,
                'timestamp' => now()->toDateTimeString(),
                'request_data' => $request->all(),
            ]);

            return redirect()->back()->with('error', 'An error occurred while posting the module');
        }
    }
    public function list()
    {
        $applications = LoanApplications::all();

        
        $isAdmin = Auth::user()->role === 'admin';

        $permissions = [
            'create' => $isAdmin,
            'edit' => $isAdmin,
            'delete' => $isAdmin,
        ];

        $flash = [
            'success' => session('success'),
            'error' => session('error'),
        ];

        if(Auth::user()->role === 'admin'){
            // Get all applications for this module
            Log::info('Admin');
            $applications = LoanApplications::all()
                ->map(function ($application) {
                    return [
                        'id' => $application->id ?? 0,
                        'customer_id' => $application->customer_id ?? 0,
                        'product_id' => $application->product_id ?? 0,
                        'agent_id' => $application->agent_id ?? 0,
                        'module_id' => $application->module_id ?? 0,
                        'biro' => $application->biro ?? '',
                        'banca' => $application->banca ?? '',
                        'rates' => $application->rates ?? 0,
                        'module_id' => $application->module_id ?? null,
                        'moduleSlug' => $application->module->slug ?? null,
                        'document_checklist' => $application->document_checklist ? json_decode($application->document_checklist) : [],
                        'date_received' => $application->date_received ?? null,
                        'date_rejected' => $application->date_rejected ?? null,
                        'date_approved' => $application->date_approved ?? null,
                        'date_disbursed' => $application->date_disbursed ?? null,
                        'date_submitted' => $application->date_submitted ?? null,
                        'tenure_applied' => $application->tenure_applied ?? 0,
                        'tenure_approved' => $application->tenure_approved ?? 0,
                        'amount_applied' => $application->amount_applied ?? 0,
                        'amount_approved' => $application->amount_approved ?? 0,
                        'amount_disbursed' => $application->amount_disbursed ?? 0,
                        'created_at' => $application->created_at ? $application->created_at->format('d M Y') : '',
                        'updated_at' => $application->updated_at ? $application->updated_at->format('d M Y') : '',
                        'reference_id' => $application->reference_id ?? '',
                        'customer_name' => $application->customer->name ?? '',
                        'status' => $application->status ?? '', 
                    ];
                });
            }else if(Auth::user()->role === 'agent'){
                Log::info('Agent');
                $subAgents = User::where('master_agent', Auth::user()->id)->get();
                $subAgentsApplications = LoanApplications::whereIn('agent_id', $subAgents->pluck('id'))->get();
                $agentApplications = LoanApplications::where('agent_id', Auth::user()->id)->get();
                $applications = $agentApplications->merge($subAgentsApplications)
                ->map(function ($application) {
                    return [
                        'id' => $application->id ?? 0,
                        'agent_name' => $application->agent->name ?? '',
                        'customer_id' => $application->customer_id ?? 0,
                        'product_id' => $application->product_id ?? 0,
                        'agent_id' => $application->agent_id ?? 0,
                        'module_id' => $application->module_id ?? 0,
                        'biro' => $application->biro ?? '',
                        'banca' => $application->banca ?? '',
                        'rates' => $application->rates ?? 0,
                        'module_id' => $application->module_id ?? null,
                        'moduleSlug' => $application->module->slug ?? null,
                        'document_checklist' => $application->document_checklist ? json_decode($application->document_checklist) : [],
                        'date_received' => $application->date_received ?? null,
                        'date_rejected' => $application->date_rejected ?? null,
                        'date_approved' => $application->date_approved ?? null,
                        'date_disbursed' => $application->date_disbursed ?? null,
                        'date_submitted' => $application->date_submitted ?? null,
                        'tenure_applied' => $application->tenure_applied ?? 0,
                        'tenure_approved' => $application->tenure_approved ?? 0,
                        'amount_applied' => $application->amount_applied ?? 0,
                        'amount_approved' => $application->amount_approved ?? 0,
                        'amount_disbursed' => $application->amount_disbursed ?? 0,
                        'created_at' => $application->created_at ? $application->created_at->format('d M Y') : '',
                        'updated_at' => $application->updated_at ? $application->updated_at->format('d M Y') : '',
                        'reference_id' => $application->reference_id ?? '',
                        'customer_name' => $application->customer->name ?? '',
                        'status' => $application->status ?? '', 
                    ];
                });
                
            }else if(Auth::user()->role === 'sub agent'){
                Log::info('Sub Agent');
                $applications = LoanApplications::where('agent_id', Auth::user()->id)
                ->get()
                ->map(function ($application) {
                    return [
                        'id' => $application->id ?? 0,
                        'customer_id' => $application->customer_id ?? 0,
                        'product_id' => $application->product_id ?? 0,
                        'agent_id' => $application->agent_id ?? 0,
                        'module_id' => $application->module_id ?? 0,
                        'biro' => $application->biro ?? '',
                        'banca' => $application->banca ?? '',
                        'rates' => $application->rates ?? 0,
                        'module_id' => $application->module_id ?? null,
                        'moduleSlug' => $application->module->slug ?? null,
                        'document_checklist' => $application->document_checklist ? json_decode($application->document_checklist) : [],
                        'date_received' => $application->date_received ?? null,
                        'date_rejected' => $application->date_rejected ?? null,
                        'date_approved' => $application->date_approved ?? null,
                        'date_disbursed' => $application->date_disbursed ?? null,
                        'date_submitted' => $application->date_submitted ?? null,
                        'tenure_applied' => $application->tenure_applied ?? 0,
                        'tenure_approved' => $application->tenure_approved ?? 0,
                        'amount_applied' => $application->amount_applied ?? 0,
                        'amount_approved' => $application->amount_approved ?? 0,
                        'amount_disbursed' => $application->amount_disbursed ?? 0,
                        'created_at' => $application->created_at ? $application->created_at->format('d M Y') : '',
                        'updated_at' => $application->updated_at ? $application->updated_at->format('d M Y') : '',
                        'reference_id' => $application->reference_id ?? '',
                        'customer_name' => $application->customer->name ?? '',
                        'status' => $application->status ?? '', 
                    ];
                });
                        
            }

        return Inertia::render('LoanApplications/List', [
            'applications' => $applications,
            'subAgentsApplications' => $subAgentsApplications ?? [],
            'agentApplications' => $agentApplications ?? [],
            'isAdmin' => $isAdmin,
            'permissions' => $permissions,
            'flash' => $flash
        ]);
    }
}
