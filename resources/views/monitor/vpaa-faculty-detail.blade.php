@extends('layouts.app')

@section('title', 'Faculty Details - ' . $faculty->name)

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-4 mb-4">
        <a href="{{ route('monitor.faculty-compliance', $program->id) }}" class="text-blue-600 hover:text-blue-800">
            <i class="bi bi-arrow-left mr-2"></i>Back to Faculty List
        </a>
    </div>
    
    <div class="flex items-center gap-4">
        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
            <i class="bi bi-person text-3xl"></i>
        </div>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $faculty->name }}</h1>
            <p class="text-gray-600">{{ $program->name }} - {{ $program->department->name }}</p>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="filter-section">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Assignments</h3>
    <form method="GET" action="{{ route('monitor.vpaa-faculty-detail', [$program->id, $faculty->id]) }}" class="filter-grid">
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
        
        <div class="flex items-end">
            <button type="submit" class="btn-primary mr-2">
                <i class="bi bi-search mr-2"></i>Apply Filters
            </button>
            <a href="{{ route('monitor.vpaa-faculty-detail', [$program->id, $faculty->id]) }}" class="btn-secondary">
                <i class="bi bi-x-circle mr-2"></i>Clear
            </a>
        </div>
    </form>
</div>

<!-- Summary Statistics -->
@php
    $totalDocuments = 0;
    $compliedDocuments = 0;
    foreach($assignments as $assignment) {
        $totalDocuments += $assignment->complianceDocuments->count();
        $compliedDocuments += $assignment->complianceDocuments->where('status', 'Complied')->count();
    }
    $complianceRate = $totalDocuments > 0 ? round(($compliedDocuments / $totalDocuments) * 100, 1) : 0;
@endphp

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <div class="stat-icon bg-blue-100 text-blue-600">
            <i class="bi bi-journal-text"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $assignments->count() }}</div>
            <div class="stat-label">Total Assignments</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-purple-100 text-purple-600">
            <i class="bi bi-files"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $totalDocuments }}</div>
            <div class="stat-label">Total Documents</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-green-100 text-green-600">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $compliedDocuments }}</div>
            <div class="stat-label">Complied</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon bg-red-100 text-red-600">
            <i class="bi bi-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $totalDocuments - $compliedDocuments }}</div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
</div>

<!-- Assignments List -->
<div class="space-y-6">
    @forelse($assignments as $assignment)
        <div class="assignment-card">
            <div class="assignment-header">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-blue-100 text-blue-600">
                        <i class="bi bi-book"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">
                            {{ $assignment->subject_code }} - {{ $assignment->subject_description }}
                        </h3>
                        @if($assignment->semester)
                            <p class="text-sm text-gray-600">{{ $assignment->semester->name }} {{ $assignment->semester->year }}</p>
                        @endif
                    </div>
                </div>
                <div class="assignment-meta">
                    @php
                        $assignmentDocs = $assignment->complianceDocuments->count();
                        $assignmentComplied = $assignment->complianceDocuments->where('status', 'Complied')->count();
                        $assignmentRate = $assignmentDocs > 0 ? round(($assignmentComplied / $assignmentDocs) * 100, 1) : 0;
                    @endphp
                    <span class="text-sm text-gray-600">{{ $assignmentComplied }}/{{ $assignmentDocs }} Complied</span>
                    <div class="progress-bar-small">
                        <div class="progress-fill" style="width: {{ $assignmentRate }}%"></div>
                    </div>
                </div>
            </div>
            
            <div class="compliance-documents">
                @forelse($assignment->complianceDocuments as $document)
                    <div class="document-item">
                        <div class="document-info">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-file-earmark-text text-gray-500"></i>
                                <span class="document-name">{{ $document->documentType->name }}</span>
                            </div>
                            <span class="status-badge {{ $document->status === 'Complied' ? 'status-complete' : 'status-pending' }}">
                                {{ $document->status }}
                            </span>
                        </div>
                        <div class="document-meta">
                            @if($document->updated_at)
                                <span class="text-xs text-gray-500">{{ $document->updated_at->format('M d, Y g:i A') }}</span>
                            @endif
                            @if($document->links && $document->links->count() > 0)
                                <div class="document-links">
                                    @foreach($document->links as $link)
                                        <a href="{{ $link->drive_link }}" target="_blank" class="document-link">
                                            <i class="bi bi-link-45deg mr-1"></i>View Document
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-gray-500">
                        <i class="bi bi-inbox text-2xl mb-2 block"></i>
                        <p class="text-sm">No compliance documents for this assignment</p>
                    </div>
                @endforelse
            </div>
        </div>
    @empty
        <div class="text-center py-12">
            <i class="bi bi-journal-x text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Assignments Found</h3>
            <p class="text-gray-500">No assignments found matching your criteria.</p>
        </div>
    @endforelse
</div>

<div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
    <div class="flex items-start">
        <i class="bi bi-info-circle text-blue-500 text-xl mr-3 mt-0.5"></i>
        <div>
            <h4 class="font-medium text-blue-900 mb-1">VPAA View</h4>
            <p class="text-blue-700 text-sm">
                As a VPAA, you can view all assignments and compliance documents across all semesters for this faculty member.
                This comprehensive view allows you to track compliance trends and identify areas that may need attention.
            </p>
        </div>
    </div>
</div>
@endsection
