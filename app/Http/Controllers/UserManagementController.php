<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Salaries;
use App\Models\Addresses;
use App\Models\LoanModules;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\LoanApplications;
use App\Models\WorkflowRemarks;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the users.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $query = User::orderBy('updated_at', 'desc')->where('role', '!=', 'superuser');
        
        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('phone_num', 'like', "%{$search}%");
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
        
        $users = $query->paginate(10)->withQueryString();
        
        return Inertia::render('Admin/UserManagement/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'status']),
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $loanModules = LoanModules::select('id', 'name', 'description', 'logo', 'status')->get();
        
        return Inertia::render('Admin/UserManagement/Create', [
            'loanModules' => $loanModules
        ]);
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Log the request data before validation
        Log::info('Arrived');
        
        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone_num' => 'required|string|max:20',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'role' => 'required|string|in:admin,agent,customer,sub agent',
            'status' => 'required|string|in:active,inactive',
            'ic_num' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip' => 'required|string|max:20',
            'country' => 'nullable|string|max:255',
            'user_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'module_permissions' => 'nullable|array',
            'module_permissions.*' => 'exists:loan_modules,id',
        ]);

        // Log the validated data
        Log::info($validated);
        // Handle photo upload
        $userPhotoPath = null;
        if ($request->hasFile('user_photo')) {
            $userPhotoPath = $request->file('user_photo')->store('images/users', 'public');
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_num' => $request->phone_num,
            'bank_name' => $request->bank_name,
            'bank_account' => $request->bank_account,
            'role' => $request->role,
            'status' => $request->status,
            'ic_num' => $request->ic_num,
            'user_photo' => $userPhotoPath,
            'module_permissions' => $request->module_permissions ? json_encode($request->module_permissions) : null,
        ]);

        if($request->status !== 'not active'){
            $user->email_verified_at = now();
            $user->save();
        }

        Log::info($user);

        // Create address
        $user->address()->create([
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip,
            'country' => 'Malaysia',
        ]);


        return redirect()->route('users.management.index')
            ->with('success', 'User created successfully.');
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
        
        return Inertia::render('Admin/UserManagement/Show', [
            'user' => $user,
            'loanModules' => $loanModules
        ]);
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  int  $id
     * @return \Inertia\Response
     */
    public function edit($username)
    {
        $user = User::with('address')->where('username', $username)->firstOrFail();
        $loanModules = LoanModules::select('id', 'name', 'description', 'logo', 'status')->get();
        
        // Convert module_permissions from JSON string to array
        if ($user->module_permissions) {
            $user->module_permissions = json_decode($user->module_permissions);
        }
        
        return Inertia::render('Admin/UserManagement/Edit', [
            'user' => $user,
            'loanModules' => $loanModules
        ]);
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $userId)
    {
        $user = User::where('id', $userId)->firstOrFail();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'phone_num' => 'required|string|max:20',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'role' => 'required|string|in:admin,agent,customer,sub agent',
            'status' => 'required|string|in:active,inactive',
            'ic_num' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip' => 'required|string|max:20',
            'user_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'module_permissions' => 'nullable|array',
            'module_permissions.*' => 'exists:loan_modules,id',
        ]);

        // Update user
        $userData = [
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'email' => $request->email,
            'phone_num' => $request->phone_num,
            'bank_name' => $request->bank_name,
            'bank_account' => $request->bank_account,
            'role' => $request->role,
            'status' => $request->status,
            'ic_num' => $request->ic_num,
            'module_permissions' => $request->module_permissions ? json_encode($request->module_permissions) : null,
        ];

        if($request->status === 'not active'){
            $userData['email_verified_at'] = null;
        }

        // Handle photo upload
        if ($request->hasFile('user_photo')) {
            // Delete old photo if exists
            if ($user->user_photo) {
                $oldPhotoPath = storage_path('app/public/' . $user->user_photo);
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }
            
            // Store new photo
            $userPhotoPath = $request->file('user_photo')->store('images/users', 'public');
            $userData['user_photo'] = $userPhotoPath;
        }
        
        // Only update password if provided
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }
        
        $user->update($userData);

        // Update or create address
        $user->address()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'address_line_1' => $request->address_line_1,
                'address_line_2' => $request->address_line_2,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
            ]
        );

        return redirect()->route('users.management.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($userId)
    {
        $user = User::where('id', $userId)->firstOrFail();
        if($user->role === 'agent') {
            LoanApplications::where('agent_id', $user->id)->update(['agent_id' => null]);
        }elseif($user->role === 'customer') {
            // First get all loan applications for this customer
            $loanApplications = LoanApplications::where('customer_id', $user->id)->get();
            
            // Delete workflow remarks for each loan application
            foreach ($loanApplications as $application) {
                WorkflowRemarks::where('application_id', $application->id)->delete();
            }
            
            // Then delete the loan applications
            LoanApplications::where('customer_id', $user->id)->delete();
        }elseif($user->role === 'sub agent') {
            LoanApplications::where('agent_id', $user->id)->update(['agent_id' => null]);
        }
        
        // Delete user photo if exists
        if ($user->user_photo) {
            $oldPhotoPath = storage_path('app/public/' . $user->user_photo);
            if (file_exists($oldPhotoPath)) {
                unlink($oldPhotoPath);
            }
        }

        //Delete user salary
        if (Salaries::where('customer_id', $user->id)->exists()) {
            Salaries::where('customer_id', $user->id)->delete();
            Log::info('Salary deleted');
        }

        // Delete address first (if exists)
        if ($user->address) {
            $user->address->delete();
        }

        //Delete user employment
        if ($user->employment) {
            Addresses::where('employment_id', $user->employment->id)->delete();
            $user->employment->delete();
        }


        //Delete notification
        Notification::where('receiver_id', $user->id)->orWhere('sender_id', $user->id)->delete();
        
        // Delete user
        $user->delete();

        return redirect()->route('users.management.index')
            ->with('success', 'User deleted successfully.');
    }

    public function showProfile()
    {
        $user = Auth::user();

        return Inertia::render('User/Profile', [
            'user' => $user
        ]);
    }
    
    /**
     * Check if a username is available (not already taken).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUsername(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:3',
        ]);
        
        $username = $request->username;
        $currentUserId = Auth::id();
        
        // Check if username exists, excluding the current user
        $exists = User::where('username', $username)
            ->when($currentUserId, function ($query, $currentUserId) {
                return $query->where('id', '!=', $currentUserId);
            })
            ->exists();
        
        return response()->json([
            'available' => !$exists,
        ]);
    }
    
    /**
     * Check if an email is available (not already taken).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        
        $email = $request->email;
        
        // Check if email exists, excluding the current user
        $exists = User::where('email', $email)
            ->exists();
        
        return response()->json([
            'available' => !$exists,
        ]);
    }

    /**
     * Check if the provided current password is correct for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);
        
        $user = Auth::user();
        $isCorrect = Hash::check($request->password, $user->password);
        
        return response()->json([
            'available' => $isCorrect,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = Auth::user();
        $isCorrect = Hash::check($request->current_password, $user->password);
        
        if (!$isCorrect) {
            return redirect()->back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }
    
        try {
            User::where('id', $user->id)->update(['password' => Hash::make($request->password)]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update password.' . $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    public function updateProfile(Request $request)
    {

        try {
            $user = User::findOrFail(Auth::id());
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
            ]);
            Log::info($request->hasFile('user_photo'));
            Log::info($request->user_photo);
            if ($request->hasFile('user_photo')) {

                $oldProfilePhotoPath = storage_path('app/public/' . $user->user_photo);
                if (file_exists($oldProfilePhotoPath) && $user->user_photo) {
                    unlink($oldProfilePhotoPath);
                }

                $userPhotoPath = $request->file('user_photo')->store('images/users', 'public');
                $user->user_photo = $userPhotoPath;
                $user->save();
                Log::info($userPhotoPath);
                Log::info($user->user_photo);
            }

            if ($request->user_photo === null) {
                $oldProfilePhotoPath = storage_path('app/public/' . $user->user_photo);
                if (file_exists($oldProfilePhotoPath) && $user->user_photo) {
                    unlink($oldProfilePhotoPath);
                }
                $user->user_photo = null;
                $user->save();
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update profile.' . $e->getMessage()]);
        }
    
        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    public function checkICNumber(Request $request)
    {
        $request->validate([
            'ic_num' => 'required|string|max:20',
        ]);

        $icNum = $request->ic_num;
        
        // Check if ic number exists
        $exists = User::where('ic_num', $icNum)
            ->exists();
        
        return response()->json([
            'available' => !$exists,
        ]);
    }

    public function comparePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();
        $isCorrect = password_verify($request->password, $user->password);
        Log::info($isCorrect);
        
        return response()->json([
            'available' => $isCorrect,
        ]);
        
        
    }

    public function checkICNumberEdit(Request $request, $username)
    {
        $request->validate([
            'ic_num' => 'required|string|max:20',
        ]);

        $icNum = $request->ic_num;
        
        // Get the current user by username
        $currentUser = User::where('username', $username)->first();
        
        // If the IC number is the same as the current user's, it's available
        if ($currentUser && $currentUser->ic_num === $icNum) {
            return response()->json([
                'available' => true,
            ]);
        }
        
        // Check if IC number exists for any other user
        $exists = User::where('ic_num', $icNum)
            ->exists();

        return response()->json([
            'available' => !$exists,
        ]);
    }

    public function checkEmailEdit(Request $request, $username)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;
        
        // Get the current user by username
        $currentUser = User::where('username', $username)->first();
        
        // If the email is the same as the current user's, it's available
        if ($currentUser && $currentUser->email === $email) {
            return response()->json([
                'available' => true,
            ]);
        }
        
        // Check if email exists for any other user
        $exists = User::where('email', $email)
            ->exists();

        return response()->json([
            'available' => !$exists,
        ]);
            
    }

    public function checkUsernameEdit(Request $request, $username)
    {
        $request->validate([
            'username' => 'required|string|max:255',
        ]);

        
        // Get the current user by username
        $currentUser = User::where('username', $username)->first();
        
        // If the username is the same as the current user's, it's available
        if ($currentUser && $currentUser->username === $request->username) {
            Log::info('Username is the same as the current user');
            Log::info($currentUser->username);
            Log::info($request->username);
            
            return response()->json([
                'available' => true,
            ]);
        }
        
        // Check if username exists for any other user
        $exists = User::where('username', $request->username)
            ->exists();

        return response()->json([
            'available' => !$exists,
        ]);
    }
}   



