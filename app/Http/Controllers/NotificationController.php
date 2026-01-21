<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class NotificationController extends Controller
{
    /**
     * Mark a single notification as read and redirect to its target.
     */
    public function markAsRead($id)
    {
        // 1. Find the notification belonging to the current user
        $notification = Auth::user()->notifications()->findOrFail($id);
        
        // 2. Mark it as read (removes it from the unread count)
        $notification->markAsRead();

        // 3. Redirect to the link stored in the notification data
        // If no link is present, default back to the dashboard
        return Redirect::to($notification->data['link'] ?? route('dashboard'));
    }
    
    /**
     * Mark all notifications as read at once.
     */
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back();
    }
}