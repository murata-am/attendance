<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class StaffController extends Controller
{
    public function index(){
        $staff = User::where('role', 'user')
            ->select('id', 'name', 'email')
            ->get();

        return view('admin/staff/list', compact('staff'));
    }
}
