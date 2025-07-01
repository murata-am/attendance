<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Fortify;
use Illuminate\Validation\ValidationException;
use phpDocumentor\Reflection\Types\Boolean;


class AuthenticatedSessionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            Fortify::username() => 'required|string',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($request->only(Fortify::username(), 'password'), $request->Boolean('remember'))) {
            throw ValidationException::withMessages([
                Fortify::username() => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended('/attendance');
    }
}
