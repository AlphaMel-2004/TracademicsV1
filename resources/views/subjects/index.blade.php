@extends('layouts.app')

@section('title', 'My Subjects - Faculty')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">My Subjects</h1>
    <p class="text-gray-600 mt-2">Click on a subject to view requirements and submit compliance documents</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($assignments as $assignment)
    <div class="subject-card" onclick="window.location.href='{{ route('subjects.show', $assignment->id) }}'">
        <div class="flex items-center justify-between mb-4">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <i class="bi bi-book text-2xl"></i>
            </div>
            <div class="text-right">
                @php
                    $complied = $assignment->complianceDocuments->where('status', 'Complied')->count();
                    $total = $assignment->complianceDocuments->count();
                    $percentage = $total > 0 ? round(($complied / $total) * 100, 1) : 0;
                @endphp
                <span class="text-sm text-gray-500">{{ $percentage }}% Complete</span>
            </div>
        </div>
        
        <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ $assignment->subject->code }}</h3>
        <p class="text-gray-600 mb-3">{{ $assignment->subject->title }}</p>
        
        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Term:</span>
                <span class="font-medium text-gray-800">{{ $assignment->term->name }} {{ $assignment->term->year }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Compliance:</span>
                <span class="font-medium text-gray-800">{{ $complied }}/{{ $total }}</span>
            </div>
        </div>
        
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center text-purple-600 text-sm font-medium">
                    <span>View Requirements</span>
                    <i class="bi bi-arrow-right ml-2"></i>
                </div>
                <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-purple-500 rounded-full" style="width: {{ $percentage }}%"></div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($assignments->isEmpty())
<div class="text-center py-12">
    <i class="bi bi-book text-6xl text-gray-300 mb-4"></i>
    <h3 class="text-lg font-medium text-gray-900 mb-2">No Subject Assignments</h3>
    <p class="text-gray-500">You haven't been assigned to any subjects yet. Contact your Program Head for assignments.</p>
</div>
@endif

<!-- Compliance Summary -->
@if($assignments->count() > 0)
<div class="mt-8 bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Overall Compliance Summary</h3>
    
    @php
        $totalAssignments = $assignments->count();
        $totalComplianceItems = 0;
        $totalComplied = 0;

        foreach($assignments as $assignment) {
            $totalComplianceItems += $assignment->complianceDocuments->count();
            $totalComplied += $assignment->complianceDocuments->where('status', 'Complied')->count();
        }

        $overallPercentage = $totalComplianceItems > 0 ? round(($totalComplied / $totalComplianceItems) * 100, 1) : 0;
    @endphp
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="text-center">
            <div class="text-2xl font-bold text-blue-600">{{ $totalAssignments }}</div>
            <div class="text-sm text-gray-600">Subject Assignments</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-green-600">{{ $totalComplied }}/{{ $totalComplianceItems }}</div>
            <div class="text-sm text-gray-600">Documents Submitted</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-purple-600">{{ $overallPercentage }}%</div>
            <div class="text-sm text-gray-600">Overall Compliance</div>
        </div>
    </div>
    
    <div class="mt-4 w-full bg-gray-200 rounded-full h-3">
        <div class="bg-purple-500 h-3 rounded-full transition-all duration-300" style="width: {{ $overallPercentage }}%"></div>
    </div>
</div>
@endif
@endsection
