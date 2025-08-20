@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Reports</h1>
    <p class="text-gray-600 mt-2">
        @if($data['type'] === 'vpaa')
            Institution-wide compliance and performance reports
        @else
            Department-level compliance reports for {{ $data['department']->name }}
        @endif
    </p>
</div>

@if($data['type'] === 'vpaa')
<!-- VPAA Reports -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="bi bi-people-fill text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Faculty</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $data['total_faculty'] }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="bi bi-book-fill text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Assignments</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $data['total_assignments'] }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <i class="bi bi-percent text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Overall Compliance</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $data['compliance_rate'] }}%</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Export Options</h3>
    <div class="flex gap-4">
        <a href="{{ route('reports.export.excel') }}" class="btn-primary">
            <i class="bi bi-file-earmark-excel mr-2"></i>Export to Excel
        </a>
        <a href="{{ route('reports.export.pdf') }}" class="btn-primary">
            <i class="bi bi-file-earmark-pdf mr-2"></i>Export to PDF
        </a>
    </div>
</div>

@else
<!-- Dean Reports -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="bi bi-people-fill text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Department Faculty</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $data['total_faculty'] }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <i class="bi bi-percent text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Department Compliance</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $data['compliance_rate'] }}%</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Department Reports</h3>
    <div class="flex gap-4">
        <a href="{{ route('reports.export.pdf') }}" class="btn-primary">
            <i class="bi bi-file-earmark-pdf mr-2"></i>Generate PDF Report
        </a>
    </div>
    <p class="text-sm text-gray-600 mt-2">PDF reports will include timestamp in filename for tracking.</p>
</div>
@endif

<div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
    <div class="flex items-start">
        <i class="bi bi-info-circle text-blue-500 text-xl mr-3 mt-0.5"></i>
        <div>
            <h4 class="font-medium text-blue-900 mb-1">Report Information</h4>
            <p class="text-blue-700 text-sm">
                @if($data['type'] === 'vpaa')
                    These reports provide institution-wide insights into faculty compliance, assignments, and overall performance metrics.
                @else
                    These reports show department-specific compliance data and can be exported for administrative review.
                @endif
            </p>
        </div>
    </div>
</div>
@endsection


