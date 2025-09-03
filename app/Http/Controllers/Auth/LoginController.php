<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            // Debug: Log request data
            Log::info('Login attempt', [
                'has_token' => $request->has('_token'),
                'token_value' => $request->input('_token'),
                'session_token' => session()->token(),
                'email' => $request->input('email'),
                'user_agent' => $request->userAgent(),
            ]);
            
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
            Log::error('CSRF Token Mismatch', [
                'request_token' => $request->input('_token'),
                'session_token' => session()->token(),
                'session_id' => session()->getId(),
            ]);
            return redirect()->route('login')->withErrors(['email' => 'Page expired. Please try again.']);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'An error occurred. Please try again.'])->withInput();
        }
    }
}


