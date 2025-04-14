<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use App\Models\LoanModules;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\LoanApplications;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Show the notifications page
     */
    public function index(Request $request)
    {
        // Check if this is an API request or a page request
        if ($request->wantsJson() || $request->is('api/*')) {
            return $this->getNotificationsJson($request);
        }
        
        // For AJAX requests that are Inertia requests, include the notifications data
        if ($request->ajax() && $request->header('X-Inertia')) {
            return Inertia::render('System/Notifications', [
                'notifications' => $this->getNotificationsJson($request)->getData()->notifications
            ]);
        }
        
        // For regular page requests, return the Inertia view

        $auth = Auth::check();

        if($auth) {
            return Inertia::render('System/Notifications');
        } else {
            return redirect()->route('dashboard');
        }

    }
    
    /**
     * Get notifications as JSON for API requests
     */
    private function getNotificationsJson(Request $request)
    {
        // If user is not authenticated, return sample data for development
        if (!Auth::check()) {
            return $this->getSampleNotifications();
        }
        
        $user = Auth::user();
        
        $notifications = Notification::where('receiver_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'message' => $notification->message,
                    'date' => $this->formatDate($notification->created_at),
                    'status' => $notification->status,
                    'created_at' => $notification->created_at,
                    'reference_id' => $notification->reference_id
                ];
            });
        
        return response()->json([
            'notifications' => $notifications
        ])->header('X-Inertia-Partial-Data', true);
    }
    
    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => true,
                'message' => 'Development mode: Notification would be marked as read'
            ])->header('X-Inertia-Partial-Data', true);
        }
        
        $user = Auth::user();
        
        $notification = Notification::where('id', $id)
            ->where('receiver_id', $user->id)
            ->first();
            
        if ($notification) {
            $notification->read_at = Carbon::now();
            $notification->status = 'read';
            $notification->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ])->header('X-Inertia-Partial-Data', true);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Notification not found'
        ], 404)->header('X-Inertia-Partial-Data', true);
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => true,
                'message' => 'Development mode: All notifications would be marked as read'
            ])->header('X-Inertia-Partial-Data', true);
        }
        
        $user = Auth::user();
        
        Notification::where('receiver_id', $user->id)
            ->update(['read_at' => Carbon::now(), 'status' => 'read']);
            
        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ])->header('X-Inertia-Partial-Data', true);
    }
    
    /**
     * Delete a notification
     */
    public function destroy(Request $request, $id)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => true,
                'message' => 'Development mode: Notification would be deleted'
            ])->header('X-Inertia-Partial-Data', true);
        }
        
        $user = Auth::user();
        
        $notification = Notification::where('id', $id)
            ->where('receiver_id', $user->id)
            ->first();
            
        if ($notification) {
            $notification->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted'
            ])->header('X-Inertia-Partial-Data', true);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Notification not found'
        ], 404)->header('X-Inertia-Partial-Data', true);
    }
    
    /**
     * Delete all notifications
     */
    public function destroyAll(Request $request)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => true,
                'message' => 'Development mode: All notifications would be deleted'
            ])->header('X-Inertia-Partial-Data', true);
        }
        
        $user = Auth::user();
        
        Notification::where('receiver_id', $user->id)->delete();
            
        return response()->json([
            'success' => true,
            'message' => 'All notifications deleted'
        ])->header('X-Inertia-Partial-Data', true);
    }
    
    /**
     * Format the date to a human-readable format
     */
    private function formatDate($date)
    {
        $carbon = Carbon::parse($date);
        $now = Carbon::now();
        
        if ($carbon->isToday()) {
            return 'Today at ' . $carbon->format('g:i A');
        } elseif ($carbon->isYesterday()) {
            return 'Yesterday at ' . $carbon->format('g:i A');
        } elseif ($carbon->isCurrentWeek()) {
            return $carbon->format('l') . ' at ' . $carbon->format('g:i A');
        } elseif ($carbon->diffInDays($now) < 14) {
            return $carbon->diffInDays($now) . ' days ago';
        } elseif ($carbon->diffInDays($now) < 30) {
            $weeks = floor($carbon->diffInDays($now) / 7);
            return $weeks . ' ' . ($weeks == 1 ? 'week' : 'weeks') . ' ago';
        } elseif ($carbon->diffInDays($now) < 365) {
            $months = floor($carbon->diffInDays($now) / 30);
            return $months . ' ' . ($months == 1 ? 'month' : 'months') . ' ago';
        } else {
            $years = floor($carbon->diffInDays($now) / 365);
            return $years . ' ' . ($years == 1 ? 'year' : 'years') . ' ago';
        }
    }
    
    /**
     * Get sample notifications for development
     */
    private function getSampleNotifications()
    {
        $now = Carbon::now();
        $notifications = [
            [
                'id' => 1,
                'message' => 'You have a new message from administrator',
                'created_at' => $now->copy()->subHour()->toDateTimeString(),
                'status' => 'unread',
                'reference_id' => '1234567890'
            ],
            [
                'id' => 2,
                'message' => 'Your loan application has been processed',
                'created_at' => $now->copy()->subHours(3)->toDateTimeString(),
                'status' => 'unread',
                'reference_id' => '1234567890'
            ],
            [
                'id' => 3,
                'message' => 'Welcome to the loan application system',
                'created_at' => $now->copy()->subDay()->toDateTimeString(),
                'status' => 'read',
                'reference_id' => '1234567890'
            ],
            [
                'id' => 4,
                'message' => 'Your profile has been updated successfully',
                'created_at' => $now->copy()->subDays(2)->toDateTimeString(),
                'status' => 'read',
                'reference_id' => '1234567890'
            ],
            [
                'id' => 5,
                'message' => 'System maintenance scheduled for next weekend',
                'created_at' => $now->copy()->subWeek()->toDateTimeString(),
                'status' => 'unread',
                'reference_id' => '1234567890'
            ],
        ];
        
        // Format dates for display
        foreach ($notifications as &$notification) {
            $notification['date'] = $this->formatDate($notification['created_at']);
        }
        
        return response()->json([
            'notifications' => $notifications
        ])->header('X-Inertia-Partial-Data', true);
    }

    public function goToReferenceId($referenceId,$notificationId)
    {
        $application = LoanApplications::where('reference_id', $referenceId)->first();

        $module = LoanModules::where('id', $application->module_id)->first();

        $notification = Notification::where('id', $notificationId)->first();

        $user = User::where('id', $notification->receiver_id)->first();

        if($application){

            if($user->role === 'customer' || $user->role === 'agent' || $user->role === 'subagent'){
                return redirect()->route('customer.application.show', [$referenceId]);
            }else{

                if($application->module_id){
                    return redirect()->route('loan-modules.applications.show', [$module->slug, $referenceId]);
                }else{
                    return redirect()->route('choose-module', ['referenceId' => $referenceId]);
                }

               
            }
        }else{
            if($user->role === 'customer' || $user->role === 'agent' || $user->role === 'subagent'){
                return redirect()->route('customer.application.show', [$referenceId])->with('error', 'Application not found');
            }else{
                return redirect()->route('new-application-shortcut')->with('error', 'Application not found');
            }
        }
    }
} 