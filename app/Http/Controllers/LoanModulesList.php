<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\LoanApplications;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\LoanModules as LoanModulesModel;

class LoanModulesList extends Controller
{
    /**
     * Get all loan modules with transformed data
     *
     * @return array
     */
    private function getTransformedModules()
    {
        // Get the authenticated user
        $user = request()->user();
        
        // Get modules based on user permissions
        $query = LoanModulesModel::query();


        // If user is NOT admin or superuser, apply module_permissions filter
        if (!in_array($user->role, ['admin', 'superuser'])) {
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
        }
        
        $transformedModules = $query->get()->map(function ($module) {
            // Get products or empty array if none exist
            $products = $module->products()->get();
            
            // Count total applications across all products
            $totalApplications = LoanApplications::where('module_id', $module->id)->count();


            
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
                'topProducts' => $products->take(3)->pluck('name')->toArray(),
            ];
        });
        
        // Always return a consistent structure with both keys
        return [
            'modules' => $transformedModules,
            'user' => $user
        ];
    }

    /**
     * Handle the API request for loan modules
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        $data = $this->getTransformedModules();
        
        return response()->json($data);
    }

    /**
     * Handle the web request for loan modules
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $data = $this->getTransformedModules();
        
        return Inertia::render('LoanModulesList', [
            'modules' => $data['modules'] ?? [],
            'authUser' => $data['user'] ? [
                'id' => $data['user']->id,
                'name' => $data['user']->name,
                'email' => $data['user']->email,
                'role' => $data['user']->role,
                'module_permissions' => $data['user']->module_permissions ? json_decode($data['user']->module_permissions) : [],
                'status' => $data['user']->status,
            ] : null
        ]);
    }
}
