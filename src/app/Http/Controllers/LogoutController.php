<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogoutController extends Controller
{
    public function logout(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            $role = 'admin';
            Auth::guard('admin')->logout();
        } elseif (Auth::check()) {
            $role = 'user';
            Auth::logout();
        } else {
            $role = null;
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();


        if ($role === 'admin') {
            return redirect('/admin/login');
        }

        return redirect('/login');
    }
}
