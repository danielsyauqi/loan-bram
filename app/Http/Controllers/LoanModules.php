<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\LoanModules as LoanModulesModel;

class LoanModulesList extends Controller
{
    /**
     * Get all loan modules with transformed data
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getTransformedModules()
    {
        // Get the authenticated user
        $user = request()->user();
        
        // Get modules based on user permissions
        $query = LoanModulesModel::query();
        
        // If user is not an admin, filter by their module permissions
        //if ($user && $user->role !== 'admin') {
            // Get module permissions from user model (collection of module ids)
            $modulePermissions = $user->module_permissions ? json_decode($user->module_permissions, true) : [];
            
            // Only show modules the user has permission to access
            if (!empty($modulePermissions)) {
                $query->whereIn('id', $modulePermissions);
            } else {
                // If user has no permissions, return empty collection
                return [
                    'modules' => collect(),
                    'user' => $user,
                ];
            }
        //}
        return $query->get()->map(function ($module) {
            // Get products or empty array if none exist
            $products = $module->products()->get();
            
            // Count total applications across all products
            $totalApplications = 0;
            foreach ($products as $product) {
                $totalApplications += $product->loanApplications()->count();
            }
            Log::info("Module slug: " . $module->slug);
            
            return [
                'id' => $module->id,
                'slug' => $module->slug,
                'title' => $module->name,
                'description' => $module->description ?? 'No description available',
                'banner' => $module->logo ? asset($module->logo) : asset('images/loan-modules/default.png'),
                'status' => $module->status ?? 'Active',
                'dateCreated' => $module->created_at ? $module->created_at->format('d F Y') : 'None',
                'productCount' => $products->count(),
                'totalApplications' => $totalApplications,
                'rating' => '4.5', // Default value
                'topProducts' => $products->take(3)->pluck('name')->toArray()
            ];
        });
    }

    /**
     * Handle the API request for loan modules
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        $modules = $this->getTransformedModules();
        
        return response()->json($modules);
    }

    /**
     * Handle the web request for loan modules
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $modules = $this->getTransformedModules();
        
        return Inertia::render('LoanModulesList', [
            'modules' => $modules
        ]);
    }
}
