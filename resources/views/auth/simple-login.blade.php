@extends('layouts.guest')

@section('title','Test Login')

@section('content')
<div style="max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc;">
    <h2>Simple Login Test</h2>
    
    @if($errors->any())
        <div style="color: red; margin: 10px 0;">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
    
    <form method="POST" action="{{ route('login.submit') }}">
        @csrf
        <div style="margin: 10px 0;">
            <label>Email:</label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="user@brokenshire.edu.ph" required style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin: 10px 0;">
            <label>Password:</label>
            <input type="password" name="password" required style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin: 10px 0;">
            <button type="submit" style="padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer;">
                Login
            </button>
        </div>
    </form>
    
    <div style="margin-top: 20px; font-size: 12px; color: #666;">
        <p>CSRF Token: {{ csrf_token() }}</p>
        <p>Session ID: {{ session()->getId() }}</p>
    </div>
</div>
@endsection
