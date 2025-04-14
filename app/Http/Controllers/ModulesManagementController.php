<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Admins;
use App\Models\Products;
use App\Models\LoanModules;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\LoanApplications;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ModulesManagementController extends Controller
{
    /**
     * Display a listing of the modules.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $modules = LoanModules::with('products')->get()->map(function ($module) {
            return [
                'id' => $module->id,
                'slug' => $module->slug,
                'name' => $module->name,
                'description' => $module->description,
                'logo' => $module->logo ? asset($module->logo) : asset('images/loan-modules/default.png'),
                'status' => $module->status,
                'created_at' => $module->created_at->format('d M Y'),
                'products_count' => $module->products->count(),
            ];
        });

        return Inertia::render('Admin/ModulesManagement/Index', [
            'modules' => $modules,
            'flash' => [
                'success' => session('success'),
                'moduleName' => session('moduleName'),
            ]
        ]);
    }

    /**
     * Show the form for creating a new module.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Admin/ModulesManagement/Create');
    }

    /**
     * Store a newly created module in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('images/loan-modules', 'public');
        }

        // Create a slug from the module name
        $slug = Str::slug($request->name);
        
        // Check if a module with this slug already exists in this module
        $slugExists = LoanModules::where('slug', $slug)
            ->exists();
            
        if ($slugExists) {
            // Append a unique identifier if slug already exists
            $slug = $slug . '-' . uniqid();
        }

        LoanModules::create([
            'name' => $request->name,
            'description' => $request->description,
            'logo' => $logoPath ? 'storage/' . $logoPath : null,
            'status' => $request->status,
            'slug' => $slug,
        ]);

        return redirect()->route('modules.management.index')->with('success', 'Module created successfully.')
            ->with('moduleName', $request->name);
    }

    /**
     * Show the form for editing the specified module.
     *
     * @param  int  $id
     * @return \Inertia\Response
     */
    public function edit($moduleSlug)
    {
        $module = LoanModules::where('slug', $moduleSlug)->first();
        Log::info($module);

        return Inertia::render('Admin/ModulesManagement/Edit', [
            'module' => [
                'id' => $module->id,
                'slug' => $module->slug,
                'name' => $module->name,
                'description' => $module->description,
                'logo' => $module->logo ? asset($module->logo) : null,
                'status' => $module->status,
            ]
        ]);
    }

    /**
     * Update the specified module in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $module = LoanModules::findOrFail($id);

        $logoPath = $module->logo;
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($module->logo && Storage::disk('public')->exists(str_replace('storage/', '', $module->logo))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $module->logo));
            }
            
            $logoPath = 'storage/' . $request->file('logo')->store('images/loan-modules', 'public');
        }

        $module->update([
            'name' => $request->name,
            'description' => $request->description,
            'logo' => $logoPath,
            'status' => $request->status,
        ]);

        return redirect()->route('modules.management.index')->with('success', 'Module updated successfully.');
    }

    /**
     * Remove the specified module from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $module = LoanModules::findOrFail($id);

        // Delete logo if exists
        if ($module->logo && Storage::disk('public')->exists(str_replace('storage/', '', $module->logo))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $module->logo));
        }

        Products::where('module_id', $id)->delete();
        LoanApplications::where('module_id', $id)->delete();

        // Get all users with this module in their permissions
        $users = User::where('module_permissions', 'like', '%' . $id . '%')->get();
        
        // For each user, remove this module ID from their permissions
        foreach ($users as $user) {
            $permissions = json_decode($user->module_permissions, true) ?: [];
            $permissions = array_filter($permissions, function($moduleId) use ($id) {
                return $moduleId != $id;
            });
            $user->module_permissions = !empty($permissions) ? json_encode(array_values($permissions)) : null;
            $user->save();
        }

        $module->delete();

        return redirect()->route('modules.management.index')->with('success', 'Module deleted successfully.');
    }
} 