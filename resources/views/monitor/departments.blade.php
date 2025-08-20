@extends('layouts.app')

@section('title', 'Monitor Departments - VPAA')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Monitor Departments</h1>
    <p class="text-gray-600 mt-2">Click on a department to view its programs and faculty compliance</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($departments as $department)
    <div class="department-card" onclick="window.location.href='{{ route('monitor.programs', $department->id) }}'">
        <div class="flex items-center justify-between mb-4">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="bi bi-building text-2xl"></i>
            </div>
            <div class="text-right">
                <span class="text-sm text-gray-500">{{ $department->programs_count }} Programs</span>
            </div>
        </div>
        
        <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ $department->name }}</h3>
        
        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Faculty Members:</span>
                <span class="font-medium text-gray-800">{{ $department->users_count }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Total Programs:</span>
                <span class="font-medium text-gray-800">{{ $department->programs_count }}</span>
            </div>
        </div>
        
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex items-center text-blue-600 text-sm font-medium">
                <span>View Programs</span>
                <i class="bi bi-arrow-right ml-2"></i>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($departments->isEmpty())
<div class="text-center py-12">
    <i class="bi bi-building text-6xl text-gray-300 mb-4"></i>
    <h3 class="text-lg font-medium text-gray-900 mb-2">No Departments Found</h3>
    <p class="text-gray-500">There are no departments configured in the system.</p>
</div>
@endif
@endsection
