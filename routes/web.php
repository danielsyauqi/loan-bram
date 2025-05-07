<?php

use Inertia\Inertia;
use App\Models\LoanModules;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoanProductsController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\LoanApplicationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ModulesManagementController;
use App\Http\Controllers\LoanModulesList as LoanModulesController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ShortcutController;
use App\Http\Controllers\Auth\EmailPreVerificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\SubAgentController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('loan-modules', [LoanModulesController::class, 'index'])->middleware(['auth', 'verified'])->name('loan-modules');

Route::get('loan-modules/{moduleSlug}/applications', [LoanApplicationController::class, 'index'])->middleware(['auth', 'verified'])->name('loan-modules.applications');
Route::get('loan-modules/{moduleSlug}/applications/create', [LoanApplicationController::class, 'create'])->middleware(['auth', 'verified'])->name('loan-modules.applications.create');
Route::post('loan-modules/{moduleSlug}/applications/create', [LoanApplicationController::class, 'create'])->middleware(['auth', 'verified']);
Route::post('/loan-applications', [LoanApplicationController::class, 'store'])->middleware(['auth', 'verified'])->name('loan-applications.store');
Route::get('/loan-applications/list', [LoanApplicationController::class, 'list'])->middleware(['auth', 'verified'])->name('loan-applications.list');
Route::delete('/loan-applications/{moduleSlug}/{applicationId}', [LoanApplicationController::class, 'destroy'])->middleware(['auth', 'verified'])->name('loan-applications.destroy');
Route::delete('/loan-applications/{moduleSlug}/{applicationId}', [LoanApplicationController::class, 'destroySecond'])->middleware(['auth', 'verified'])->name('loan-applications.destroySecond');
Route::get('loan-modules/{moduleSlug}/applications/{referenceId}', [LoanApplicationController::class, 'show'])->middleware(['auth', 'verified'])->name('loan-modules.applications.show');
Route::get('loan-modules/{moduleSlug}/applications/{applicationId}/edit', [LoanApplicationController::class, 'edit'])->middleware(['auth', 'verified'])->name('loan-modules.applications.edit');
Route::post('loan-modules/{moduleSlug}/applications/{applicationId}/updateWorkflow/{workflowId}', [LoanApplicationController::class, 'updateWorkflow'])->middleware(['auth', 'verified'])->name('loan-modules.applications.updateWorkflow');
Route::delete('loan-modules/{moduleSlug}/applications/{applicationId}/deleteWorkflow/{workflowId}', [LoanApplicationController::class, 'deleteWorkflow'])->middleware(['auth', 'verified'])->name('loan-modules.applications.deleteWorkflow');
Route::post('loan-modules/{moduleSlug}/applications/{applicationId}/addWorkflow', [LoanApplicationController::class, 'addWorkflow'])->middleware(['auth', 'verified'])->name('loan-modules.applications.addWorkflow');
Route::post('loan-modules/{moduleSlug}/applications/{applicationId}/save', [LoanApplicationController::class, 'saveApplication'])->middleware(['auth', 'verified'])->name('loan-modules.applications.saveApplication');
Route::post('loan-modules/{moduleSlug}/applications/{referenceId}/module/post', [LoanApplicationController::class, 'postModule'])->middleware(['auth', 'verified'])->name('loan-modules.applications.module.post');
Route::get('new-application', [LoanApplicationController::class, 'newApplication'])->middleware(['auth', 'verified'])->name('new-application');

Route::get('loan-modules/{moduleSlug}/products/{productId}/apply', function ($moduleSlug, $productId) {
    // Fetch the loan module from the database
    $loanModule = LoanModules::where('slug', $moduleSlug)->first();

    
    // Map the module data to the format expected by the component
    $module = [
        'id' => $loanModule->id,
        'title' => $loanModule->name,
        'description' => $loanModule->description ?? 'No description available',
        'banner' => $loanModule->logo ? asset($loanModule->logo) : asset('images/loan-modules/default.png'),
        'status' => $loanModule->status ?? 'Active'
    ];

    // Get the specific product
    $product = $loanModule->products()->findOrFail($productId);
    
    // Default requirements and features if not available in the database
    $defaultRequirements = [
            'Valid ID and proof of residence',
            'Latest 3 months bank statements'
    ];
    
    $defaultFeatures = [
            'Flexible repayment terms',
        'Quick approval process'
    ];
    
    // Map the product to the format expected by the component
    $mappedProduct = [
        'id' => $product->id,
        'moduleId' => $loanModule->id,
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

    return Inertia::render('CreateLoanApplication', [
        'moduleId' => $loanModule->id,
        'module' => $module,
        'productId' => $productId,
        'product' => $mappedProduct
    ]);
})->middleware(['auth', 'verified'])->name('loan-modules.products.apply');

Route::get('loan-modules/{id}/products', [LoanProductsController::class, 'index'])->middleware(['auth', 'verified'])->name('loan-modules.products');

// Modules Management Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    Route::get('/modules', [ModulesManagementController::class, 'index'])->name('modules.management.index');
    Route::get('/modules/create', [ModulesManagementController::class, 'create'])->name('modules.management.create');
    Route::post('/modules', [ModulesManagementController::class, 'store'])->name('modules.management.store');
    Route::get('/modules/{moduleSlug}/edit', [ModulesManagementController::class, 'edit'])->name('modules.management.edit');
    Route::put('/modules/{moduleSlug}', [ModulesManagementController::class, 'update'])->name('modules.management.update');
    Route::delete('/modules/{moduleSlug}', [ModulesManagementController::class, 'destroy'])->name('modules.management.destroy');
    
    // Products Management Routes
    Route::get('/modules/{moduleSlug}/products', [ProductsController::class, 'index'])->name('products.index');
    Route::get('/modules/{moduleSlug}/products/create', [ProductsController::class, 'create'])->name('products.create');
    Route::post('/modules/{moduleSlug}/products', [ProductsController::class, 'store'])->name('products.store');
    Route::get('/modules/{moduleSlug}/products/{productSlug}/edit', [ProductsController::class, 'edit'])->name('products.edit');
    Route::put('/modules/{moduleSlug}/products/{productId}', [ProductsController::class, 'update'])->name('products.update');
    Route::delete('/modules/{moduleSlug}/products/{productId}', [ProductsController::class, 'destroy'])->name('products.destroy');
    
    // User Management Routes
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.management.index');
    Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.management.create');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.management.store');
    Route::get('/users/{username}', [UserManagementController::class, 'show'])->name('users.management.show');
    Route::get('/users/{username}/edit', [UserManagementController::class, 'edit'])->name('users.management.edit');
    Route::put('/users/{userId}', [UserManagementController::class, 'update'])->name('users.management.update');
    Route::delete('/users/{userId}', [UserManagementController::class, 'destroy'])->name('users.management.destroy');
});

// Customer Dashboard route
Route::get('/customer-dashboard', [CustomerController::class, 'index'])
    ->middleware(['auth'])
    ->name('customer.dashboard');
Route::get('/show-application/{referenceNumber}', [CustomerController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('customer.application.show');
Route::get('/customer-applications-new', [CustomerController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('customer.applications.new');
Route::post('/customer-applications-new', [CustomerController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('customer.applications.store');

// New Customer Applications routes
Route::get('/customer-applications', [CustomerController::class, 'applications'])
    ->middleware(['auth', 'verified'])
    ->name('customer.applications.index');
Route::delete('/customer-applications/{referenceNumber}', [CustomerController::class, 'deleteRequest'])
    ->middleware(['auth', 'verified'])
    ->name('customer.application.deleteRequest');

Route::get('/identity-form', [CustomerController::class, 'identityForm'])
    ->middleware(['auth', 'verified'])
    ->name('customer.identityForm');
Route::post('/identity-form', [CustomerController::class, 'IdentityFormStore'])
    ->middleware(['auth', 'verified'])
    ->name('customer.identityForm.store');

// User Profile route
Route::get('/user-profile', [UserManagementController::class, 'showProfile'])
    ->middleware(['auth', 'verified'])
    ->name('user.profile');

Route::post('/user-profile', [UserManagementController::class, 'updateProfile'])
    ->middleware(['auth', 'verified'])
    ->name('user.profile.update');

Route::post('/user-password', [UserManagementController::class, 'updatePassword'])
    ->middleware(['auth', 'verified'])
    ->name('user.password.update');

    // User validation routes
Route::post('/check-username', [UserManagementController::class, 'checkUsername'])->name('api.check-username');
Route::post('/check-email', [UserManagementController::class, 'checkEmail'])->name('api.check-email');
Route::post('/check-password', [UserManagementController::class, 'checkPassword'])->name('api.check-password');
Route::post('/check-ic-number', [UserManagementController::class, 'checkICNumber'])->name('api.check-ic-number');
Route::post('/compare-password', [UserManagementController::class, 'comparePassword'])->name('api.compare-password');

Route::post('/check-ic-number-edit/{username}', [UserManagementController::class, 'checkICNumberEdit'])->name('api.check-ic-number-edit');
Route::post('/check-email-edit/{username}', [UserManagementController::class, 'checkEmailEdit'])->name('api.check-email-edit');
Route::post('/check-username-edit/{username}', [UserManagementController::class, 'checkUsernameEdit'])->name('api.check-username-edit');



//User validation routes User Management
Route::post('/check-username-management', [UserManagementController::class, 'checkUsernameManagement'])->name('api.check-username-management');
Route::post('/check-email-management', [UserManagementController::class, 'checkEmailManagement'])->name('api.check-email-management');
Route::post('/check-password-management', [UserManagementController::class, 'checkPasswordManagement'])->name('api.check-password-management');
Route::post('/check-ic-number-management', [UserManagementController::class, 'checkICNumberManagement'])->name('api.check-ic-number-management');

// Notifications routes
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

// Add notification API routes to web.php
Route::prefix('api')->group(function () {
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/mark-as-read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'destroy']);
    Route::delete('/notifications', [App\Http\Controllers\NotificationController::class, 'destroyAll']);
});

//Notification routes
Route::get('/notifications/{referenceId}/{notificationId}', [NotificationController::class, 'goToReferenceId'])->name('notifications.goToReferenceId');

// New Application Shortcut Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/new-application', [App\Http\Controllers\ShortcutController::class, 'index'])->name('new-application');
});

// API Routes that should be accessible when authenticated
Route::middleware(['auth'])->prefix('api')->group(function () {
    // ... existing API routes ...
    
    // Loan Modules API
    Route::get('/loan-modules', [App\Http\Controllers\ShortcutController::class, 'getLoanModules']);
});

Route::get('/choose-module/{referenceId}', [App\Http\Controllers\ShortcutController::class, 'chooseModule'])->name('choose-module');
Route::get('/choose-module/{referenceId}/{moduleId}', [App\Http\Controllers\ShortcutController::class, 'selectModule'])->name('choose-module.select');

Route::post('/assign-agent/{referenceId}/{agentId}', [App\Http\Controllers\ShortcutController::class, 'assignAgent'])->middleware(['auth', 'verified'])->name('assign-agent');

// Admin Dashboard route
Route::get('/dashboard', [DashboardController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');
Route::post('/admin/dashboard/notifications', [DashboardController::class, 'adminMarkAllNotifications'])->middleware(['auth', 'verified'])->name('admin.markAllNotifications');

// Step 1: Send verification email
Route::post('/register/email-preverify', [EmailPreVerificationController::class, 'sendVerification'])->name('email.preverify.send');

// Step 1b: After clicking email link, verify and set session
Route::get('/register/email-verify/{code}', [EmailPreVerificationController::class, 'verify'])->name('email.preverify');

// Step 1c: Get verified email from session (for Vue onMounted)
Route::get('/register/verified-email', function () {
    return response()->json(['verified_email' => session('verified_email')]);
})->name('register.verified-email');

// Cancel email verification and clear session
Route::post('/register/email-preverify/cancel', [EmailPreVerificationController::class, 'cancel'])->name('email.preverify.cancel');
// Get current verified email from session
Route::get('/register/email-preverify/current', [EmailPreVerificationController::class, 'currentVerifiedEmail'])->name('email.preverify.current');

// Get email by preverify_code cookie
Route::get('/register/email-by-code', [EmailPreVerificationController::class, 'getEmailByCode'])->name('email.by-code');

// Route to show the registration page after email verification
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');

Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.finished');

Route::get('/registration-success', [RegisteredUserController::class, 'registrationSuccess'])->name('registration.success');

Route::post('/register/email-preverify/verify', [EmailPreVerificationController::class, 'verifyByCode'])->name('email.preverify.verify');

Route::get('/invalid-verification-link', [EmailPreVerificationController::class, 'invalidVerificationLink'])->name('email.preverify.invalid');

Route::get('/sub-agents', [SubAgentController::class, 'index'])->name('sub-agents.index');
Route::post('/sub-agents/add', [SubAgentController::class, 'add'])->name('sub-agents.add');
Route::post('/sub-agents/remove', [SubAgentController::class, 'remove'])->name('sub-agents.remove');
Route::get('/sub-agents/{username}', [SubAgentController::class, 'show'])->name('sub-agents.show');
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

