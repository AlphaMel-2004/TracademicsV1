@extends('layouts.app')

@section('title', 'Select Semester')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Select Semester</h1>
    <p class="text-gray-600 mt-2">Choose the semester you want to work with</p>
</div>

<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        @if($currentSemester)
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-start">
                    <i class="bi bi-info-circle text-blue-500 text-xl mr-3 mt-0.5"></i>
                    <div>
                        <h4 class="font-medium text-blue-900 mb-1">Current Semester</h4>
                        <p class="text-blue-700 text-sm">
                            You are currently working in <strong>{{ $currentSemester->name }} {{ $currentSemester->year }}</strong>
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if($semesters->isEmpty())
            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-start">
                    <i class="bi bi-exclamation-triangle text-yellow-500 text-xl mr-3 mt-0.5"></i>
                    <div>
                        <h4 class="font-medium text-yellow-900 mb-1">No Active Semesters</h4>
                        <p class="text-yellow-700 text-sm">
                            There are currently no active semesters available. Please contact the MIS administrator to activate semesters.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-start">
                <a href="{{ route('dashboard') }}" class="btn-secondary">
                    <i class="bi bi-arrow-left mr-2"></i>Return to Dashboard
                </a>
            </div>
        @elseif($semesters->count() == 1 && $currentSemester && $semesters->first()->id == $currentSemester->id)
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-start">
                    <i class="bi bi-check-circle text-green-500 text-xl mr-3 mt-0.5"></i>
                    <div>
                        <h4 class="font-medium text-green-900 mb-1">Only Current Semester Available</h4>
                        <p class="text-green-700 text-sm">
                            The current semester <strong>{{ $currentSemester->name }} {{ $currentSemester->year }}</strong> is the only active semester available. 
                            No other semesters are currently open for selection.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-start">
                <a href="{{ route('dashboard') }}" class="btn-secondary">
                    <i class="bi bi-arrow-left mr-2"></i>Return to Dashboard
                </a>
            </div>
        @else
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Available Semesters</h3>
                <p class="text-sm text-gray-600">
                    Select a semester from the list below. You can switch between active semesters as needed.
                </p>
            </div>

            <form method="POST" action="{{ route('semester.set') }}">
                @csrf
                <div class="space-y-3 mb-6">
                    @foreach($semesters as $semester)
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors
                                    {{ $currentSemester && $semester->id == $currentSemester->id ? 'bg-blue-50 border-blue-300' : '' }}">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="semester_id" value="{{ $semester->id }}" 
                                       class="sr-only" required
                                       {{ $currentSemester && $semester->id == $currentSemester->id ? 'checked' : '' }}>
                                <div class="flex-shrink-0 mr-4">
                                    <div class="w-4 h-4 border-2 border-gray-300 rounded-full flex items-center justify-center
                                                radio-button {{ $currentSemester && $semester->id == $currentSemester->id ? 'border-blue-500' : '' }}">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full hidden radio-dot"></div>
                                    </div>
                                </div>
                                <div class="flex-grow">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-medium text-gray-900">{{ $semester->name }} {{ $semester->year }}</h4>
                                            <p class="text-sm text-gray-600">
                                                Academic Year {{ $semester->year }}
                                                @if($semester->is_active)
                                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full ml-2">
                                                        <i class="bi bi-check-circle mr-1"></i>Active
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                        @if($currentSemester && $semester->id == $currentSemester->id)
                                            <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-blue-100 text-blue-800 rounded-full">
                                                <i class="bi bi-person-check mr-1"></i>Current
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </label>
                        </div>
                    @endforeach
                </div>
                
                <div class="flex justify-between">
                    <a href="{{ route('dashboard') }}" class="btn-secondary">
                        <i class="bi bi-arrow-left mr-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check-circle mr-2"></i>Switch Semester
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>

<style>
/* Custom radio button styling */
.radio-button {
    transition: all 0.2s ease;
}

input[type="radio"]:checked + .flex-shrink-0 .radio-button {
    border-color: #3b82f6;
}

input[type="radio"]:checked + .flex-shrink-0 .radio-button .radio-dot {
    display: block;
}

input[type="radio"]:checked ~ .flex-grow {
    color: #1e40af;
}

label:hover .radio-button {
    border-color: #93c5fd;
}
</style>
@endsection
