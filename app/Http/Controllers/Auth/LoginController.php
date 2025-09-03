<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Session\TokenMismatchException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            $email = strtolower($credentials['email']);
            if (!str_ends_with($email, '@brokenshire.edu.ph')) {
                return back()->withErrors(['email' => 'Only @brokenshire.edu.ph accounts are allowed.'])->withInput();
            }

            if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
                $request->session()->regenerate();
                return redirect()->intended(route('dashboard'));
            }

            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
            
        } catch (TokenMismatchException $e) {
            return redirect()->route('login')->withErrors(['email' => 'Page expired. Please try again.']);
        }
    }
}


