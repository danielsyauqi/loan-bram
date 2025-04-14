<?php

namespace App\Http\Controllers;

use App\Models\LoanModules;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoanProductsController extends Controller
{
    /**
     * Display the loan products for a specific module.
     *
     * @param  int  $id
     * @return \Inertia\Response
     */
    public function index($id)
    {
        // Fetch the loan module from the database
        $loanModule = LoanModules::findOrFail($id);
        
        // Check if user has permission to access this module
        $user = request()->user();
        $userModulePermissions = $user->module_permissions ? json_decode($user->module_permissions, true) : [];
        
        // If user is not an admin and doesn't have permission for this module, abort
        if (!in_array($loanModule->id, $userModulePermissions)) {
            abort(403, 'You do not have permission to access this loan module.');
        }
        
        // Map the module data to the format expected by the component
        $module = [
            'id' => $loanModule->id,
            'title' => $loanModule->name,
            'description' => $loanModule->description ?? 'No description available',
            'banner' => $loanModule->logo ? asset($loanModule->logo) : asset('images/loan-modules/default.png'),
            'status' => $loanModule->status ?? 'Active'
        ];
        
        // Fetch the products for this module
        $products = $loanModule->products()->get();
        
        // Map the products to the format expected by the component
        $mappedProducts = $products->map(function ($product) use ($id) {
            // Default requirements and features if not available in the database
            $defaultRequirements = [
                'Valid ID and proof of residence',
                'Latest 3 months bank statements'
            ];
            
            $defaultFeatures = [
                'Flexible repayment terms',
                'Quick approval process'
            ];
            
            return [
                'id' => $product->id,
                'moduleId' => $id,
                'title' => $product->name,
                'description' => $product->description ?? 'No description available',
                'interestRate' => $product->rate ?? 'N/A',
                'minAmount' => (int)$product->minimum_loan ?? 0,
                'maxAmount' => (int)$product->maximum_loan ?? 0,
                'processingTime' => $product->tenure ?? 'N/A',
                'requirements' => isset($product->requirements) ? json_decode($product->requirements) : $defaultRequirements,
                'features' => isset($product->features) ? json_decode($product->features) : $defaultFeatures,
                'status' => $product->status ?? 'Active'
            ];
        });

        return Inertia::render('LoanProducts', [
            'moduleId' => $id,
            'module' => $module,
            'products' => $mappedProducts
        ]);
    }
} 