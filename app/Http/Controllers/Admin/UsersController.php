<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    public function index(){
        $users = User::whereHas('roles', function($query) {
            $query->where('name', 'user');
        })->get();
        
        return view('admin.users.index', ['users' => $users]);
    }
}
