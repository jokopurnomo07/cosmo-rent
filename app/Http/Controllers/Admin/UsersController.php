<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    public function index(){
        $users = User::with('roles')->whereHas('roles', function($query) {
            $query->where('name', 'user');
        })->paginate(10);        
    
        
        $notifications = Notification::where('is_read', false)->latest()->paginate(10);
        return view('admin.users.index', ['users' => $users, 'notifications' => $notifications]);
    }
}
