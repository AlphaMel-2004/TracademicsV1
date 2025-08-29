@extends('layouts.app')

@section('title', 'My Compliance')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">My Compliance</h1>
    <p class="text-gray-600 mt-2">Select a subject to view and manage compliance requirements</p>
</div>

@if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
        <div class="flex items-center">
            <i class="bi bi-check-circle text-green-500 mr-3"></i>
            <span class="text-green-700">{{ session('success') }}</span>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
        <div class="flex items-center">
            <i class="bi bi-exclamation-circle text-red-500 mr-3"></i>
            <span class="text-red-700">{{ session('error') }}</span>
        </div>
    </div>
@endif

@if($assignments->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($assignments as $assignment)
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow border border-gray-200">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                {{ $assignment->subject_code }}
                            </h3>
                            <p class="text-gray-600 text-sm mb-3">
                                {{ $assignment->subject_description }}
                            </p>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="bi bi-calendar mr-2"></i>
                                {{ $currentSemester->name }} {{ $currentSemester->year }}
                            </div>
                        </div>
                    </div>

                    @php
                        // Get actual compliance data for this assignment
                        $complianceInfo = $complianceData[$assignment->id] ?? ['total' => 16, 'completed' => 0, 'percentage' => 0];
                        $totalRequirements = $complianceInfo['total'];
                        $completedRequirements = $complianceInfo['completed'];
                        $compliancePercentage = $complianceInfo['percentage'];
                    @endphp

                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Compliance Progress</span>
                            <span class="text-sm text-gray-600">{{ $completedRequirements }}/{{ $totalRequirements }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ $compliancePercentage }}%"></div>
                        </div>
                        <div class="text-right mt-1">
                            <span class="text-sm font-medium 
                                {{ $compliancePercentage >= 80 ? 'text-green-600' : ($compliancePercentage >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $compliancePercentage }}%
                            </span>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <a href="{{ route('compliance.subject', $assignment->subject_code) }}" 
                           class="btn-primary">
                            <i class="bi bi-folder-check mr-2"></i>
                            View Requirements
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex items-start">
            <i class="bi bi-info-circle text-blue-500 text-xl mr-3 mt-0.5"></i>
            <div>
                <h4 class="font-medium text-blue-900 mb-2">Compliance Information</h4>
                <div class="text-blue-700 text-sm space-y-1">
                    <p>• <strong>Faculty-wide requirements:</strong> Information Sheet, TOR/Diploma, Certificates of Trainings, Faculty Load (submit once per faculty user)</p>
                    <p>• <strong>Semester-specific requirements:</strong> All other documents must be submitted for each subject every semester</p>
                    <p>• Syllabus will show as subject-specific (e.g., "Syllabus (IT 1)") for each subject</p>
                    <p>• You can submit multiple links for each requirement if needed</p>
                    <p>• Click on any subject card above to manage your compliance documents</p>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
        <div class="flex items-center">
            <i class="bi bi-exclamation-triangle text-yellow-500 text-xl mr-3"></i>
            <div>
                <h4 class="font-medium text-yellow-900 mb-1">No Subject Assignments</h4>
                <p class="text-yellow-700 text-sm">
                    You don't have any subject assignments for the current semester. 
                    Please contact your Program Head if you believe this is an error.
                </p>
            </div>
        </div>
    </div>
@endif
@endsection


