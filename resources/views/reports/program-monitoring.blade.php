@extends('layouts.master')

@section('title','Program Monitoring')

@section('content')
<div class="bg-white p-6 rounded shadow">
	<h2 class="text-xl font-semibold mb-4">Program Monitoring</h2>
	<p>Program-specific monitoring and reports for Program Heads. This view will show faculty assignments, compliance status, and other program-related data.</p>
	
	<div class="mt-6">
		<h3 class="text-lg font-medium mb-3">Program Overview</h3>
		<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
			<div class="bg-blue-50 p-4 rounded-lg">
				<h4 class="font-medium text-blue-800">Faculty Assignments</h4>
				<p class="text-2xl font-bold text-blue-600">0</p>
				<p class="text-sm text-blue-600">Active assignments</p>
			</div>
			<div class="bg-green-50 p-4 rounded-lg">
				<h4 class="font-medium text-green-800">Compliance Status</h4>
				<p class="text-2xl font-bold text-green-600">0%</p>
				<p class="text-sm text-green-600">Documents submitted</p>
			</div>
			<div class="bg-yellow-50 p-4 rounded-lg">
				<h4 class="font-medium text-yellow-800">Pending Items</h4>
				<p class="text-2xl font-bold text-yellow-600">0</p>
				<p class="text-sm text-yellow-600">Requires attention</p>
			</div>
		</div>
	</div>
</div>
@endsection
