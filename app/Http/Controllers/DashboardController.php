<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\LoanModules;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\LoanApplications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

class DashboardController extends Controller
{
    /**
     * Display the welcome/landing page.
     *
     * @return \Inertia\Response
     */
    public function welcome()
    {
        return Inertia::render('Welcome');
    }

    /**
     * Display the dashboard with loan modules data.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $modules = LoanModules::with(['products' => function ($query) {
            $query->orderBy('popularity', 'desc')->take(3);
        }, 'products.loanApplications'])
        ->where('status', 'Active')
        ->get()
        ->map(function ($module) {
            $productCount = $module->products->count();
    
            $topProducts = $module->products->pluck('name')->toArray();
    
            $totalApplications = $module->products->reduce(function ($carry, $product) {
                return $carry + $product->loanApplications->count();
            }, 0);
    
            return [
                'id' => $module->id ?? 0,
                'slug' => $module->slug ?? '',
                'title' => $module->name ?? '',
                'description' => $module->description ?? 'No description available',
                'banner' => $module->logo ? asset($module->logo) : asset('images/loan-modules/default.png'),
                'status' => $module->status ?? '',
                'dateCreated' => $module->created_at ? $module->created_at->format('Y') : '',
                'establishedYear' => $module->created_at ? $module->created_at->format('Y') : '',
                'productCount' => $productCount ?? 0,
                'totalApplications' => $totalApplications ?? 0,
                'rating' => $module->rating ?? 4.5,
                'topProducts' => $topProducts ?? [],
            ];
        });
        
        return Inertia::render('Dashboard', [
            'modules' => $modules ?? [],
        ]);
    }

    public function dashboard()
    {
        
        $userRole = Auth::user()->role;
        $userId = Auth::user()->id;

        if($userRole === 'admin'){
        $totalLoanApplications = LoanApplications::count() ?? 0;
        $weekLoanApplications = LoanApplications::where('created_at', '>=', now()->subWeek())->count() ?? 0;
        $weekLoanActive = LoanApplications::where(function($query) {
            $query->where('status', 'New')
            ->orWhere('status', 'Processing')
            ->orWhere('status', 'Pending')
            ->orWhere('status', 'Pending@Bank')
            ->orWhere('status', 'Pending@Agency')
            ->orWhere('status', 'Ready to Submit');
        })
        ->where('created_at', '>=', now()->subWeek())
        ->count() ?? 0;

        $totalCustomers = User::where('role', 'Customer')->count() ?? 0;
        $weekTotalCustomers = User::where('role', 'Customer')
        ->where('created_at', '>=', now()->subWeek())
        ->count() ?? 0;



        $totalLoanActive = LoanApplications::where('status', 'New')
        ->orWhere('status', 'Processing')
        ->orWhere('status', 'Pending')
        ->orWhere('status', 'Pending@Bank')
        ->orWhere('status', 'Pending@Agency')
        ->orWhere('status', 'Ready to Submit')
        ->count() ?? 0;
        $totalLoanPending = LoanApplications::where('status', 'Pending')
        ->orWhere('status', 'New')
        ->orWhere('status', 'Processing')
        ->orWhere('status', 'Pending@Bank')
        ->orWhere('status','Pending@Agency')
        ->orWhere('status','Ready to Submit')
        ->count() ?? 0;
        $totalLoanRejected = LoanApplications::where('status', 'Rejected')->count() ?? 0;
        $totalLoanApproved = LoanApplications::where('status', 'Approved')->orWhere('status', 'Disbursed')->count() ?? 0;
        $totalLoanDisbursed = LoanApplications::where('status', 'Disbursed')->sum('amount_disbursed') ?? 0;
        
        $monthlyDisbursed = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyDisbursed[$i] = LoanApplications::where('status', 'Disbursed')
                ->whereMonth('created_at', $i)
                ->whereYear('created_at', now()->year)
                ->sum('amount_disbursed') ?? 0;
        }

        $weekLoanDisbursed = LoanApplications::where('status', 'Disbursed')
        ->where('created_at', '>=', now()->subWeek())
        ->sum('amount_disbursed') ?? 0;

        $recentNotification = Notification::orderBy('created_at', 'desc')
        ->where('status', 'unread')
        ->where('receiver_id', Auth::user()->id)
        ->take(5)
        ->get()
        ->map(function ($notification) {
            return [
                'id' => $notification->id ?? 0,
                'title' => $notification->sender ? "Alert from {$notification->sender->name}" : 'Alert',
                'description' => $notification->message ?? '',
                'created_at' => $notification->created_at ?? '',
                'role' => $notification->receiver->role ?? '',
            ];
        }) ?? [];

        $recentApplications = LoanApplications::orderBy('created_at', 'desc')
        ->take(5)
        ->get()
        ->map(function ($application) {
            return [
                'id' => $application->id ?? 0,
                'customer' => $application->customer->name ?? '',
                'module' => $application->module->name ?? '',
                'product' => $application->product->name ?? '',
                'amount' => $application->amount_applied ?? 0,
                'status' => $application->status ?? '',
                'date' => $application->created_at ?? '',
            ];
        }) ?? [];

        $topModules = LoanApplications::select('module_id', DB::raw('count(*) as total'))
        ->groupBy('module_id')
        ->orderBy('total', 'desc')
        ->take(5)
        ->get()
        ->map(function ($module) {
            $loanModule = LoanModules::find($module->module_id);
            return [
                'id' => $module->module_id ?? 0,
                'name' => $loanModule->name ?? 'No Module',
                'total' => $module->total ?? 0,
                'recentApplications' => LoanApplications::where('module_id', $module->module_id)->where('created_at', '>=', now()->subWeek())->count() ?? 0,
                'image' => $loanModule->logo ?? '',
                'growth' => LoanApplications::where('module_id', $module->module_id)->where('created_at', '<=', now()->subWeek())->count() ?? 0,
                'applications' => LoanApplications::where('module_id', $module->module_id)->count() ?? 0,
            ];
        }) ?? [];

        $compareDisbursedLastYear = LoanApplications::where('status', 'Disbursed')
        ->whereYear('created_at', now()->subYear()->year)
        ->sum('amount_disbursed') ?? 0;


        if($compareDisbursedLastYear === 0) {
            $compareDisbursedLastYearPercentage = 0; // Avoid division by zero
        }else{
            $compareDisbursedLastYearPercentage = ($totalLoanDisbursed - $compareDisbursedLastYear) / $compareDisbursedLastYear * 100;

        }


        return Inertia::render('Dashboard', [
            'monthlyDisbursed' => $monthlyDisbursed ?? [],
            'topModules' => $topModules ?? [],
            'recentApplications' => $recentApplications ?? [],
            'recentNotification' => $recentNotification ?? [],
            'totalLoanApplications' => $totalLoanApplications ?? 0,
            'weekLoanApplications' => $weekLoanApplications ?? 0,
            'totalLoanActive' => $totalLoanActive ?? 0,
            'totalLoanPending' => $totalLoanPending ?? 0,
            'totalLoanRejected' => $totalLoanRejected ?? 0,
            'totalLoanApproved' => $totalLoanApproved ?? 0,
            'totalLoanDisbursed' => $totalLoanDisbursed ?? 0,
            'weekLoanDisbursed' => $weekLoanDisbursed ?? 0,
            'totalCustomers' => $totalCustomers ?? 0,
            'weekTotalCustomers' => $weekTotalCustomers ?? 0,
            'compareDisbursedLastYearPercentage' => $compareDisbursedLastYearPercentage ?? 0,
            'weekLoanActive' => $weekLoanActive ?? 0,
        ]);

        }elseif($userRole==="agent"){

        $totalLoanApplications = LoanApplications::where('agent_id', $userId)->count() ?? 0;
        $weekLoanApplications = LoanApplications::where('agent_id', $userId)
            ->where('created_at', '>=', now()->subWeek())
            ->count() ?? 0;
        
        $weekLoanActive = LoanApplications::where('agent_id', $userId)
            ->where('created_at', '>=', now()->subWeek())
            ->where(function($query) {
                $query->where('status', 'New')
                    ->orWhere('status', 'Processing')
                    ->orWhere('status', 'Pending')
                    ->orWhere('status', 'Pending@Bank')
                    ->orWhere('status', 'Pending@Agency')
                    ->orWhere('status', 'Ready to Submit');
            })
            ->count() ?? 0;

        $totalCustomers = LoanApplications::where('agent_id', $userId)
            ->distinct('customer_id')
            ->count('customer_id') ?? 0;

        $weekTotalCustomers = LoanApplications::where('agent_id', $userId)
            ->where('created_at', '>=', now()->subWeek())
            ->distinct('customer_id')
            ->count('customer_id') ?? 0;

        $totalLoanActive = LoanApplications::where('agent_id', $userId)
            ->where(function($query) {
                $query->where('status', 'New')
                    ->orWhere('status', 'Processing')
                    ->orWhere('status', 'Pending')
                    ->orWhere('status', 'Pending@Bank')
                    ->orWhere('status', 'Pending@Agency')
                    ->orWhere('status', 'Ready to Submit');
            })
            ->count() ?? 0;

        $totalLoanPending = LoanApplications::where('agent_id', $userId)
            ->where(function($query) {
                $query->where('status', 'Pending')
                    ->orWhere('status', 'Processing')
                    ->orWhere('status', 'Pending@Bank')
                    ->orWhere('status', 'Pending@Agency')
                    ->orWhere('status', 'Ready to Submit');
            })
            ->count() ?? 0;
            
        $totalLoanRejected = LoanApplications::where('agent_id', $userId)
            ->where('status', 'Rejected')
            ->count() ?? 0;
            
        $totalLoanApproved = LoanApplications::where('agent_id', $userId)
            ->where(function($query) {
                $query->where('status', 'Approved')
                    ->orWhere('status', 'Disbursed');
            })
            ->count() ?? 0;
            
        $totalLoanDisbursed = LoanApplications::where('agent_id', $userId)
            ->where('status', 'Disbursed')
            ->sum('amount_disbursed') ?? 0;
        
        $monthlyDisbursed = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyDisbursed[$i] = LoanApplications::where('agent_id', $userId)
                ->where('status', 'Disbursed')
                ->whereMonth('created_at', $i)
                ->whereYear('created_at', now()->year)
                ->sum('amount_disbursed') ?? 0;
        }

        $weekLoanDisbursed = LoanApplications::where('agent_id', $userId)
            ->where('status', 'Disbursed')
            ->where('created_at', '>=', now()->subWeek())
            ->sum('amount_disbursed') ?? 0;

        $recentNotification = Notification::orderBy('created_at', 'desc')
            ->where('status', 'unread')
            ->where('receiver_id', Auth::user()->id)
            ->take(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id ?? 0,
                    'title' => $notification->sender ? "Alert from {$notification->sender->name}" : 'Alert',
                    'description' => $notification->message ?? '',
                    'created_at' => $notification->created_at ?? '',
                    'role' => $notification->receiver->role ?? '',
                ];
            }) ?? [];

        $recentApplications = LoanApplications::where('agent_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($application) {
                return [
                    'id' => $application->id ?? 0,
                    'customer' => $application->customer->name ?? '',
                    'module' => $application->module->name ?? '',
                    'product' => $application->product->name ?? '',
                    'amount' => $application->amount_applied ?? 0,
                    'status' => $application->status ?? '',
                    'date' => $application->created_at ?? '',
                ];
            }) ?? [];

        $topModules = LoanApplications::where('agent_id', $userId)
            ->select('module_id', DB::raw('count(*) as total'))
            ->groupBy('module_id')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get()
            ->map(function ($module) {
                $loanModule = LoanModules::find($module->module_id);
                return [
                    'id' => $module->module_id ?? 0,
                    'name' => $loanModule->name ?? 'No Module',
                    'total' => $module->total ?? 0,
                    'recentApplications' => LoanApplications::where('module_id', $module->module_id)
                        ->where('created_at', '>=', now()->subWeek())
                        ->count() ?? 0,
                    'image' => $loanModule->logo ?? '',
                    'growth' => LoanApplications::where('module_id', $module->module_id)
                        ->where('created_at', '<=', now()->subWeek())
                        ->count() ?? 0,
                    'applications' => LoanApplications::where('module_id', $module->module_id)->count() ?? 0,
                ];
            }) ?? [];

        $compareDisbursedLastYear = LoanApplications::where('agent_id', $userId)
            ->where('status', 'Disbursed')
            ->whereYear('created_at', now()->subYear()->year)
            ->sum('amount_disbursed') ?? 0;


        if($compareDisbursedLastYear === 0) {
            $compareDisbursedLastYearPercentage = 0; // Avoid division by zero
        }else{
            $compareDisbursedLastYearPercentage = ($totalLoanDisbursed - $compareDisbursedLastYear) / $compareDisbursedLastYear * 100;

        }




        return Inertia::render('Dashboard', [
            'monthlyDisbursed' => $monthlyDisbursed ?? [],
            'topModules' => $topModules ?? [],
            'recentApplications' => $recentApplications ?? [],
            'recentNotification' => $recentNotification ?? [],
            'totalLoanApplications' => $totalLoanApplications ?? 0,
            'weekLoanApplications' => $weekLoanApplications ?? 0,
            'totalLoanActive' => $totalLoanActive ?? 0,
            'totalLoanPending' => $totalLoanPending ?? 0,
            'totalLoanRejected' => $totalLoanRejected ?? 0,
            'totalLoanApproved' => $totalLoanApproved ?? 0,
            'totalLoanDisbursed' => $totalLoanDisbursed ?? 0,
            'weekLoanDisbursed' => $weekLoanDisbursed ?? 0,
            'totalCustomers' => $totalCustomers ?? 0,
            'weekTotalCustomers' => $weekTotalCustomers ?? 0,
            'compareDisbursedLastYearPercentage' => $compareDisbursedLastYearPercentage ?? 0,
            'weekLoanActive' => $weekLoanActive ?? 0,
        ]);

        }
    }

    public function adminMarkAllNotifications(Request $request)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => true,
                'message' => 'Development mode: All notifications would be marked as read'
            ])->header('X-Inertia-Partial-Data', true);
        }
        
        $user = Auth::user();

        Log::info('Marking all notifications as read for user: ' . $user->id);
        
        try{


            Notification::where('receiver_id', $user->id)
                ->update(['status' => 'read',
                'read_at' => Carbon::now()]);

            Log::info('All notifications marked as read for user: ' . $user->id);
            
        return response()->json([
            'success' => true,
                'message' => 'All notifications marked as read'
            ])->header('X-Inertia-Partial-Data', true);
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read'
            ], 500)->header('X-Inertia-Partial-Data', true);
        }
    }
    


}
