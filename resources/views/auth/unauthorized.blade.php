@extends('layouts.master')

@section('title','Unauthorized')

@section('content')
<div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
	<h2 class="text-xl font-semibold text-red-600 mb-2">Unauthorized</h2>
	<p class="mb-4">Only @brokenshire.edu.ph accounts are allowed.</p>
	<a href="{{ route('google.redirect') }}" class="btn-gradient inline-block">Try again</a>
</div>
@endsection


