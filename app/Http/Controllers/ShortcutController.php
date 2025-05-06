<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\LoanModules;
use Illuminate\Support\Str;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\LoanApplications;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ShortcutController extends Controller
{
    /**
     * Display the new application shortcut page.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('NewApplicationShortcut');
    }

    /**
     * Get all active loan modules.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLoanModules()
    {
        $modules = LoanModules::where('status', 'active')
            ->select([
                'id', 
                'name',
                'description',
                'logo as banner',
                'status'
            ])
            ->get()
            ->map(function ($module) {
                // Generate a slug from the name if it doesn't exist
                $slug = $module->slug ?? Str::slug($module->name);
                
                // Add default values for fields not in database
                return array_merge($module->toArray(), [
                    'title' => $module->name, // Use name as title
                    'interestRate' => $this->calculateInterestRate($module),
                    'minAmount' => $module->products()->min('minimum_loan') ?? 1000, // Default min amount
                    'maxAmount' => $module->products()->max('maximum_loan') ?? 50000, // Default max amount
                    'tenure' => $this->calculateTenure($module), // Default tenure
                    'slug' => $slug,
                ]);
            });

        return response()->json([
            'modules' => $modules,
            'success' => true
        ]);
    }

    public function assignAgent($referenceId, $agentId){
     
        try {
            // Update the loan application with the sub-agent ID
            LoanApplications::where('reference_id', $referenceId)->update(['agent_id' => $agentId]);

            //Send Notification to the agent
            $notification = new Notification();
            $notification->sender_id = Auth::user()->id;
            $notification->receiver_id = $agentId;
            $notification->message = 'You have been assigned a new loan application with reference number ' . $referenceId;
            $notification->status = 'unread';
            $notification->reference_id = $referenceId;
            $notification->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Sub-agent assigned successfully!'
            ]);
          
        } catch (\Exception $e) {
            Log::error('Failed to assign sub-agent', [
                'reference_id' => $referenceId,
                'agent_id' => $agentId,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Failed to assign sub-agent: ' . $e->getMessage());
        }
    }

    public function chooseModule($referenceId)
    {
       // Get the authenticated user
       $user = request()->user();
        
       // Get modules based on user permissions
       $query = LoanModules::query();
       

           // Get module permissions from user model (collection of module ids)
           $modulePermissions = $user->module_permissions ? json_decode($user->module_permissions, true) : [];
           
           // Only show modules the user has permission to access
           if (!empty($modulePermissions)) {
               $query->whereIn('id', $modulePermissions);
           } else {
               // If user has no permissions, return empty modules with user data
               return [
                   'modules' => collect(),
                   'user' => $user,
                   'message' => 'Please contact your administrator to get access to the loan modules'
               ];
           }

       
       $transformedModules = $query->get()->map(function ($module) {
           // Get products or empty array if none exist
           $products = $module->products()->get();
           
           // Count total applications across all products
           $totalApplications = 0;
           foreach ($products as $product) {
               $totalApplications += $product->loanApplications()->count();
           }
           
           return [
               'id' => $module->id,
               'slug' => $module->slug,
               'title' => $module->name,
               'description' => $module->description ?? 'No description available',
               'banner' => $module->logo ? asset($module->logo) : asset('images/loan-modules/default.png'),
               'status' => $module->status ?? 'Active',
               'dateCreated' => $module->created_at ? $module->created_at->format('d F Y') : 'None',
               'productCount' => $products->count(),
               'tenure' => $this->calculateTenure($module),
               'interestRate' => $this->calculateInterestRate($module),
               'minAmount' => $module->products()->min('minimum_loan') ?? 1000,
               'maxAmount' => $module->products()->max('maximum_loan') ?? 50000,
           ];
       });
            $application = LoanApplications::where('reference_id', $referenceId)->first();

        return Inertia::render('ChooseModuleShortcut', [
            'modules' => $transformedModules,
            'application' => $application
        ]);
    }

    public function selectModule($referenceId, $moduleId)
    {
        try {
            $application = LoanApplications::where('reference_id', $referenceId)->first();
            $application->module_id = $moduleId;
            $application->save();

            $module = LoanModules::where('id', $moduleId)->first();

            return redirect()->route('loan-modules.applications.show', [$module->slug, $referenceId]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to select module');
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

   

} 