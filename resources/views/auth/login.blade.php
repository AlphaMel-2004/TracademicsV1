@extends('layouts.guest')

@section('title','Login')
@section('meta_title','Login - Tracademics')
@section('meta_description','Sign in to access Tracademics. Only @brokenshire.edu.ph emails are allowed.')
@section('meta_robots','noindex,nofollow')
@section('canonical', url()->current())

@section('content')
<div class="max-w-md mx-auto bg-white p-6 rounded shadow">
	<h2 class="text-xl font-semibold mb-4">Sign in</h2>
	<p class="mb-4">Only emails ending with @brokenshire.edu.ph are allowed.</p>
	<form method="POST" action="{{ route('login.submit') }}" class="space-y-3">
		@csrf
		<div>
			<label class="block text-sm">Email</label>
			<input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded p-2" placeholder="user@brokenshire.edu.ph" required />
		</div>
		<div>
			<label class="block text-sm">Password</label>
			<input type="password" name="password" class="w-full border rounded p-2" required />
		</div>
		<div class="flex items-center gap-2">
			<input id="remember" type="checkbox" name="remember" class="border" />
			<label for="remember" class="text-sm">Remember me</label>
		</div>
		<button class="btn-gradient" type="submit"><i class="bi bi-box-arrow-in-right mr-1"></i>Login</button>
		@if($errors->any())
			<div class="text-red-600 text-sm">{{ $errors->first() }}</div>
		@endif
	</form>
</div>
@endsection


