<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class AdminLoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)
            ->where('role', 'admin')
            ->first();

        if ($user && Hash::check($request->password, $user->password)) {
            Auth::guard('admin')->login($user);
            $request->session()->regenerate();

            return redirect('/admin/attendance/list');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }

}
