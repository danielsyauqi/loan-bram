<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\LoanModules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SubAgentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::orderBy('updated_at', 'desc')->where('role', '!=', 'superuser');
        $addSubAgentQuery = User::orderBy('updated_at', 'desc')->where('role', 'customer');

        $subAgents = User::orderBy('updated_at', 'desc')
            ->where(function($query) {
                $query->where('master_agent', Auth::user()->id);
            });
        
        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $subAgents->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('phone_num', 'like', "%{$search}%");
            });
        }

        if ($request->has('addSubAgentSearch')) {
            $addSubAgentSearch = $request->addSubAgentSearch;
            $addSubAgentQuery->where(function($q) use ($addSubAgentSearch) {
                $q->where('name', 'like', "%{$addSubAgentSearch}%")
                  ->orWhere('username', 'like', "%{$addSubAgentSearch}%"); 
            });
        }
        
        // Filter by role
        if ($request->has('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        $users = $subAgents->paginate(10)->withQueryString();
        $addableUsers = $addSubAgentQuery->get();

        Log::info($addSubAgentQuery->get());
        
        return Inertia::render('SubAgents/Manage', [
            'users' => $users,
            'addableUsers' => $addableUsers,
            'filters' => $request->only(['search', 'role', 'status', 'addSubAgentSearch']),
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function add(Request $request)
    {
        try {
            $user = User::find($request->id);
            $user->role = 'sub agent';
            $user->master_agent = Auth::user()->id;
            $user->save();

            return redirect()->back()->with('success', 'Sub agent added successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add sub agent');
        }
    }


    public function remove(Request $request)
    {
        try {
            $user = User::find($request->id);
            $user->role = 'customer';
            $user->master_agent = null;
            $user->save();

            return redirect()->back()->with('success', 'Sub agent removed successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to remove sub agent');
        }
    }

        /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Inertia\Response
     */
    public function show($username)
    {
        $user = User::with('address')->where('username', $username)->firstOrFail();
        $loanModules = LoanModules::select('id', 'name', 'description', 'logo', 'status')->get();
        
        // Convert module_permissions from JSON string to array
        if ($user->module_permissions) {
            $user->module_permissions = json_decode($user->module_permissions);
        }
        
        return Inertia::render('SubAgents/Show', [
            'user' => $user,
            'loanModules' => $loanModules
        ]);
    }
} 