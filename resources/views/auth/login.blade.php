@extends('layouts.guest')

@section('title','Login')
@section('meta_title','Login - Tracademics')
@section('meta_description','Sign in to access Tracademics. Only @brokenshire.edu.ph emails are allowed.')
@section('meta_robots','noindex,nofollow')
@section('canonical', url()->current())

@section('content')
<div class="login-card">
    <div class="login-left">
        <div class="mb-8">
            <div class="flex items-center gap-2 mb-6">
                <img src="{{ asset('favicon.ico') }}" class="h-12 w-12" alt="Logo">
                <span class="text-3xl font-bold">Tracademics</span>
            </div>
            <p class="text-white text-opacity-90 text-lg mb-4">Academic Compliance Monitoring System</p>
            <p class="text-white text-opacity-75">Streamlining and organizing faculty requirements and document submissions.</p>
        </div>
    </div>
    
    <div class="login-right">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Welcome Back</h2>
            <p class="text-gray-600">Please sign in to your account</p>
        </div>
        
        <div class="text-center mb-8">
            <div class="inline-flex justify-center">
                <div class="p-3 rounded-full bg-blue-50 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <div class="input-container">
                <span class="input-icon">
                    <i class="bi bi-person"></i>
                </span>
                <input 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    class="login-input" 
                    placeholder="user@brokenshire.edu.ph" 
                    required 
                />
            </div>
            
            <div class="input-container">
                <span class="input-icon">
                    <i class="bi bi-lock"></i>
                </span>
                <input 
                    type="password" 
                    name="password" 
                    class="login-input" 
                    placeholder="Password"
                    required 
                />
            </div>
            
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <input id="remember" type="checkbox" name="remember" class="h-4 w-4 text-blue-600 border-gray-300 rounded" />
                    <label for="remember" class="ml-2 text-sm text-gray-700">Remember me</label>
                </div>
                <div>
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-800">Forgot Password?</a>
                </div>
            </div>
            
            <button class="login-button" type="submit">
                <i class="bi bi-box-arrow-in-right mr-2"></i>LOGIN
            </button>
            
            @if($errors->any())
                <div class="mt-4 p-3 bg-red-100 text-red-700 rounded-md">
                    <p class="flex items-center"><i class="bi bi-exclamation-triangle mr-2"></i> {{ $errors->first() }}</p>
                </div>
            @endif
            
            <div class="mt-6 text-center">
                <p class="mb-4 text-sm text-gray-500">Or continue with</p>
                <a href="{{ route('google.redirect') }}" class="inline-flex items-center justify-center w-full py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.24 10.285V14.4h6.806c-.275 1.765-2.056 5.174-6.806 5.174-4.095 0-7.439-3.389-7.439-7.574s3.345-7.574 7.439-7.574c2.33 0 3.891.989 4.785 1.849l3.254-3.138C18.189 1.186 15.479 0 12.24 0c-6.635 0-12 5.365-12 12s5.365 12 12 12c6.926 0 11.52-4.869 11.52-11.726 0-.788-.085-1.39-.189-1.989H12.24z" fill="#4285F4"/>
                        <path d="M12.24 10.285V14.4h6.806c-.275 1.765-2.056 5.174-6.806 5.174-4.095 0-7.439-3.389-7.439-7.574s3.345-7.574 7.439-7.574c2.33 0 3.891.989 4.785 1.849l3.254-3.138C18.189 1.186 15.479 0 12.24 0c-6.635 0-12 5.365-12 12s5.365 12 12 12c6.926 0 11.52-4.869 11.52-11.726 0-.788-.085-1.39-.189-1.989H12.24z" fill="#34A853" transform="translate(0 8) scale(1 .5)"/>
                        <path d="M12.24 10.285V14.4h6.806c-.275 1.765-2.056 5.174-6.806 5.174-4.095 0-7.439-3.389-7.439-7.574s3.345-7.574 7.439-7.574c2.33 0 3.891.989 4.785 1.849l3.254-3.138C18.189 1.186 15.479 0 12.24 0c-6.635 0-12 5.365-12 12s5.365 12 12 12c6.926 0 11.52-4.869 11.52-11.726 0-.788-.085-1.39-.189-1.989H12.24z" fill="#FBBC05" transform="translate(5) scale(.5)"/>
                        <path d="M12.24 10.285V14.4h6.806c-.275 1.765-2.056 5.174-6.806 5.174-4.095 0-7.439-3.389-7.439-7.574s3.345-7.574 7.439-7.574c2.33 0 3.891.989 4.785 1.849l3.254-3.138C18.189 1.186 15.479 0 12.24 0c-6.635 0-12 5.365-12 12s5.365 12 12 12c6.926 0 11.52-4.869 11.52-11.726 0-.788-.085-1.39-.189-1.989H12.24z" fill="#EA4335" transform="translate(15) scale(.5)"/>
                    </svg>
                    Sign in with Google
                </a>
            </div>
            
            <div class="mt-6 text-center text-sm text-gray-500">
                <p>Only emails ending with @brokenshire.edu.ph are allowed.</p>
            </div>
        </form>
    </div>
</div>
@endsection


