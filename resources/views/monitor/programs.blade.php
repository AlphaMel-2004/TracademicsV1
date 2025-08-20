@extends('layouts.app')

@section('title', 'Programs in ' . $department->name)

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-4 mb-4">
        <a href="{{ route('monitor.departments') }}" class="text-blue-600 hover:text-blue-800">
            <i class="bi bi-arrow-left mr-2"></i>Back to Departments
        </a>
    </div>
    
    <h1 class="text-3xl font-bold text-gray-800">Programs in {{ $department->name }}</h1>
    <p class="text-gray-600 mt-2">Click on a program to view faculty compliance details</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($programs as $program)
    <div class="program-card" onclick="window.location.href='{{ route('monitor.faculty-compliance', $program->id) }}'">
        <div class="flex items-center justify-between mb-4">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="bi bi-mortarboard text-2xl"></i>
            </div>
            <div class="text-right">
                <span class="text-sm text-gray-500">{{ $program->users_count }} Faculty</span>
            </div>
        </div>
        
        <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ $program->name }}</h3>
        
        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Faculty Members:</span>
                <span class="font-medium text-gray-800">{{ $program->users_count }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Total Assignments:</span>
                <span class="font-medium text-gray-800">{{ $program->faculty_assignments_count }}</span>
            </div>
        </div>
        
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex items-center text-green-600 text-sm font-medium">
                <span>View Faculty</span>
                <i class="bi bi-arrow-right ml-2"></i>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($programs->isEmpty())
<div class="text-center py-12">
    <i class="bi bi-mortarboard text-6xl text-gray-300 mb-4"></i>
    <h3 class="text-lg font-medium text-gray-900 mb-2">No Programs Found</h3>
    <p class="text-gray-500">There are no programs in this department.</p>
</div>
@endif
@endsection
