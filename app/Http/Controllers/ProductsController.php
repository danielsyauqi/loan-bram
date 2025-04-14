<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Products;
use App\Models\LoanModules;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductsController extends Controller
{
    /**
     * Display a listing of the products for a specific module.
     *
     * @param  int  $moduleId
     * @return \Inertia\Response
     */
    public function index($moduleSlug)
    {
        // Check if the module exists
        $module = LoanModules::where('slug', $moduleSlug)->first();
        
        // Get all products for this module
        $products = $module->products()->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'slug' => $product->slug,
                'name' => $product->name,
                'minimum_loan' => $product->minimum_loan,
                'maximum_loan' => $product->maximum_loan,
                'rate' => $product->rate ? json_decode($product->rate) : [],
                'tenure' => $product->tenure,
                'created_at' => $product->created_at->format('d M Y'),
            ];
        });

        return Inertia::render('Admin/ProductsManagement/Index', [
            'module' => [
                'id' => $module->id,
                'name' => $module->name,
                'description' => $module->description,
                'slug' => $module->slug,
                'logo' => $module->logo ? asset($module->logo) : asset('images/loan-modules/default.png'),
            ],
            'products' => $products,
            'flash' => [
                'success' => session('success'),
                'productName' => session('productName'),
            ]
        ]);
    }

    /**
     * Show the form for creating a new product.
     *
     * @param  int  $moduleId
     * @return \Inertia\Response
     */
    public function create($moduleSlug)
    {
        // Check if the module exists
        $module = LoanModules::where('slug', $moduleSlug)->first();

        return Inertia::render('Admin/ProductsManagement/Create', [
            'module' => [
                'id' => $module->id,
                'slug' => $module->slug,
                'name' => $module->name,
                'description' => $module->description,
                'slug' => $module->slug,
                'logo' => $module->logo ? asset($module->logo) : asset('images/loan-modules/default.png'),
            ]
        ]);
    }

    /**
     * Store a newly created product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $moduleId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $moduleSlug)
    {
        // Check if the module exists
        $module = LoanModules::where('slug', $moduleSlug)->first();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'minimum_loan' => 'required|numeric|min:0',
            'maximum_loan' => 'required|numeric|gt:minimum_loan',
            'rate' => 'required|array',
            'rate.*' => 'numeric',
            'tenure' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Filter out empty rates
        $rates = array_filter($request->rate, function($value) {
            return $value !== null && $value !== '';
        });

        // Create a slug from the product name
        $slug = Str::slug($request->name);
        
        // Check if a product with this slug already exists in this module
        $slugExists = Products::where('module_id', $module->id)
            ->where('slug', $slug)
            ->exists();
            
        if ($slugExists) {
            // Append a unique identifier if slug already exists
            $slug = $slug . '-' . uniqid();
        }

        Log::info('Slug: ' . $slug);

        // Create the product
        Products::create([
            'module_id' => $module->id,
            'slug' => $slug,
            'name' => $request->name,
            'minimum_loan' => $request->minimum_loan,
            'maximum_loan' => $request->maximum_loan,
            'rate' => json_encode($rates),
            'tenure' => $request->tenure,
        ]);

        return redirect()->route('products.index', ['moduleSlug' => $moduleSlug])
            ->with('success', 'Product created successfully.')
            ->with('productName', $request->name);
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param  int  $moduleId
     * @param  int  $id
     * @return \Inertia\Response
     */
    public function edit($moduleSlug, $productSlug)
    {
        // Check if the module exists
        $module = LoanModules::where('slug', $moduleSlug)->first();
        
        // Check if the product exists and belongs to the module
        $product = Products::where('module_id', $module->id)->where('slug', $productSlug)->first();

        return Inertia::render('Admin/ProductsManagement/Edit', [
            'module' => [
                'id' => $module->id,
                'name' => $module->name,
                'description' => $module->description,
                'logo' => $module->logo ? asset($module->logo) : asset('images/loan-modules/default.png'),
                'slug' => $module->slug,
            ],
            'product' => [
                'id' => $product->id,
                'slug' => $product->slug,
                'name' => $product->name,
                'minimum_loan' => $product->minimum_loan,
                'maximum_loan' => $product->maximum_loan,
                'rate' => $product->rate ? json_decode($product->rate) : [],
                'tenure' => $product->tenure,
            ]
        ]);
    }

    /**
     * Update the specified product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $moduleId
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $moduleSlug, $id)
    {
        // Check if the module exists
        $module = LoanModules::where('slug', $moduleSlug)->first();
        
        // Check if the product exists and belongs to the module
        $product = Products::where('module_id', $module->id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'minimum_loan' => 'required|numeric|min:0',
            'maximum_loan' => 'required|numeric|gt:minimum_loan',
            'rate' => 'required|array',
            'rate.*' => 'numeric',
            'tenure' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Filter out empty rates
        $rates = array_filter($request->rate, function($value) {
            return $value !== null && $value !== '';
        });

        // Update the product
        $product->update([
            'name' => $request->name,
            'minimum_loan' => $request->minimum_loan,
            'maximum_loan' => $request->maximum_loan,
            'rate' => json_encode($rates),
            'tenure' => $request->tenure,
        ]);

        return redirect()->route('products.index', ['moduleSlug' => $moduleSlug])
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product from storage.
     *
     * @param  int  $moduleId
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($moduleSlug, $id)
    {
        try {
            // Check if the module exists
            $module = LoanModules::where('slug', $moduleSlug)->first();

        // Update all loan applications associated with this product to have product_id = null
        // This ensures we don't have orphaned references if we need to delete the product
        $product = Products::where('module_id', $module->id)->findOrFail($id);
        $product->loanApplications()->update(['product_id' => null]);
        

        // Check if there are any loan applications associated with this product
        if ($product->loanApplications()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete product with associated loan applications.');
        }

        $product->delete();

            return redirect()->route('products.index', ['moduleSlug' => $moduleSlug])
                ->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete product.');
        }
    }
} 