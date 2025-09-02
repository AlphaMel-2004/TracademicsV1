@extends('layouts.app')

@section('title', 'Faculty Compliance - ' . $faculty->name)

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-4 mb-4">
        <a href="{{ route('monitor.compliances') }}" class="text-blue-600 hover:text-blue-800">
            <i class="bi bi-arrow-left mr-2"></i>Back to Faculty List
        </a>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center">
            <!-- Faculty Avatar -->
            <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mr-6">
                @if($faculty->avatar)
                    <img src="{{ $faculty->avatar }}" alt="{{ $faculty->name }}" class="w-18 h-18 rounded-full object-cover">
                @else
                    <i class="bi bi-person-fill text-3xl text-blue-600"></i>
                @endif
            </div>
            
            <!-- Faculty Info -->
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-800">{{ $faculty->name }}</h1>
                <p class="text-gray-600 mt-1">{{ $faculty->email }}</p>
                @if($faculty->faculty_type)
                    <p class="text-sm text-gray-500">{{ $faculty->faculty_type }} Faculty</p>
                @endif
            </div>
            
            <!-- Summary Stats -->
            <div class="grid grid-cols-2 gap-6 text-center">
                <div>
                    <div class="text-2xl font-bold text-blue-600">{{ $assignments->count() }}</div>
                    <div class="text-sm text-gray-600">Assignments</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-green-600">
                        {{ $assignments->sum(function($assignment) { return $assignment->complianceDocuments->where('status', 'Complied')->count(); }) }}
                    </div>
                    <div class="text-sm text-gray-600">Complied</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Filters</h3>
    <form method="GET" action="{{ route('monitor.faculty-detail', $faculty->id) }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
            <input type="text" id="subject" name="subject" value="{{ request('subject') }}" 
                   class="form-input" placeholder="Search by subject code or description">
        </div>
        
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Compliance Status</label>
            <select id="status" name="status" class="form-input">
                <option value="">All Statuses</option>
                <option value="Complied" {{ request('status') === 'Complied' ? 'selected' : '' }}>Complied</option>
                <option value="Not Complied" {{ request('status') === 'Not Complied' ? 'selected' : '' }}>Not Complied</option>
                <option value="Not Applicable" {{ request('status') === 'Not Applicable' ? 'selected' : '' }}>Not Applicable</option>
            </select>
        </div>
        
        <div class="flex items-end gap-2">
            <button type="submit" class="btn-primary">
                <i class="bi bi-search mr-2"></i>Apply Filters
            </button>
            <a href="{{ route('monitor.faculty-detail', $faculty->id) }}" class="btn-secondary">
                <i class="bi bi-x-circle mr-2"></i>Clear
            </a>
        </div>
    </form>
</div>

<!-- Assignments and Compliance -->
<div class="space-y-6">
    @forelse($assignments as $assignment)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">
                        @if($assignment->subject)
                            {{ $assignment->subject->code }} - {{ $assignment->subject->title }}
                        @else
                            {{ $assignment->subject_code }} - {{ $assignment->subject_description }}
                        @endif
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $assignment->complianceDocuments->count() }} compliance documents
                    </p>
                </div>
                
                <!-- Assignment Compliance Rate -->
                @php
                    $totalDocs = $assignment->complianceDocuments->count();
                    $compliedDocs = $assignment->complianceDocuments->where('status', 'Complied')->count();
                    $complianceRate = $totalDocs > 0 ? round(($compliedDocs / $totalDocs) * 100, 1) : 0;
                @endphp
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-800">{{ $complianceRate }}%</div>
                    <div class="text-sm text-gray-600">Compliance</div>
                </div>
            </div>
        </div>
        
        @if($assignment->complianceDocuments->count() > 0)
        <div class="overflow-x-auto">
            <table class="uniform-table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                        <th>Drive Links</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignment->complianceDocuments as $document)
                    <tr>
                        <td class="font-medium">{{ $document->documentType->name }}</td>
                        <td>
                            <span class="status-badge {{ $document->status === 'Complied' ? 'status-complete' : ($document->status === 'Not Applicable' ? 'status-na' : 'status-pending') }}">
                                {{ $document->status }}
                            </span>
                        </td>
                        <td>
                            {{ $document->updated_at ? $document->updated_at->format('M d, Y g:i A') : 'Not submitted' }}
                        </td>
                        <td>
                            @if($document->links && $document->links->count() > 0)
                                @foreach($document->links as $index => $link)
                                    <a href="{{ $link->drive_link }}" target="_blank" class="text-blue-600 hover:text-blue-800 block mb-1">
                                        <i class="bi bi-link-45deg mr-1"></i>{{ $link->description ?? 'Document ' . ($index + 1) }}
                                    </a>
                                @endforeach
                            @elseif($document->drive_link)
                                <a href="{{ $document->drive_link }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                    <i class="bi bi-link-45deg mr-1"></i>View Document
                                </a>
                            @else
                                <span class="text-gray-400">No link submitted</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-8 text-center text-gray-500">
            <i class="bi bi-file-text text-4xl text-gray-300 mb-2 block"></i>
            <p>No compliance documents found for this assignment.</p>
        </div>
        @endif
    </div>
    @empty
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <i class="bi bi-clipboard-x text-6xl text-gray-300 mb-4 block"></i>
        <h3 class="text-xl font-medium text-gray-900 mb-2">No Assignments Found</h3>
        <p class="text-gray-600">
            @if(request('subject'))
                No assignments match your search criteria.
            @else
                This faculty member has no assignments for the current semester.
            @endif
        </p>
    </div>
    @endforelse
</div>

<!-- Info Section -->
<div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
    <div class="flex items-start">
        <i class="bi bi-info-circle text-blue-500 text-xl mr-3 mt-0.5"></i>
        <div>
            <h4 class="font-medium text-blue-900 mb-1">Faculty Compliance Details</h4>
            <p class="text-blue-700 text-sm">
                This view shows all assignments and compliance documents for {{ $faculty->name }}. 
                Use the filters above to search for specific subjects or compliance statuses.
            </p>
        </div>
    </div>
</div>
@endsection
