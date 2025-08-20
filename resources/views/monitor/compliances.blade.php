@extends('layouts.app')

@section('title', 'Monitor Compliances - Program Head')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Monitor Compliances</h1>
    <p class="text-gray-600 mt-2">Track compliance status of faculty under your supervision</p>
</div>

<!-- Filters Section -->
<div class="filter-section">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Filters</h3>
    <form method="GET" action="{{ route('monitor.compliances') }}" class="filter-grid">
        <div>
            <label for="faculty_name" class="block text-sm font-medium text-gray-700 mb-2">Faculty Name</label>
            <input type="text" id="faculty_name" name="faculty_name" value="{{ request('faculty_name') }}" 
                   class="form-input" placeholder="Search by faculty name">
        </div>
        
        <div>
            <label for="compliance_status" class="block text-sm font-medium text-gray-700 mb-2">Compliance Status</label>
            <select id="compliance_status" name="compliance_status" class="form-input">
                <option value="">All Statuses</option>
                <option value="Complete" {{ request('compliance_status') === 'Complete' ? 'selected' : '' }}>Complete</option>
                <option value="Incomplete" {{ request('compliance_status') === 'Incomplete' ? 'selected' : '' }}>Incomplete</option>
                <option value="Pending" {{ request('compliance_status') === 'Pending' ? 'selected' : '' }}>Pending</option>
            </select>
        </div>
        
        <div class="flex items-end">
            <button type="submit" class="btn-primary mr-2">
                <i class="bi bi-search mr-2"></i>Apply Filters
            </button>
            <a href="{{ route('monitor.compliances') }}" class="btn-secondary">
                <i class="bi bi-x-circle mr-2"></i>Clear
            </a>
        </div>
    </form>
</div>

<!-- Compliance Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Faculty Compliance Status</h3>
        <p class="text-sm text-gray-600 mt-1">Showing {{ $faculty->count() }} faculty members</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="uniform-table">
            <thead>
                <tr>
                    <th>Faculty Name</th>
                    <th>Subject</th>
                    <th>Compliance Item</th>
                    <th>Status</th>
                    <th>Date Submitted</th>
                    <th>Drive Link</th>
                </tr>
            </thead>
            <tbody>
                @forelse($faculty as $member)
                    @forelse($member->facultyAssignments as $assignment)
                        @forelse($assignment->complianceDocuments as $document)
                        <tr>
                            <td class="font-medium">{{ $member->name }}</td>
                            <td>{{ $assignment->subject->code }} - {{ $assignment->subject->title }}</td>
                            <td>{{ $document->documentType->name }}</td>
                            <td>
                                <span class="status-badge {{ $document->status === 'Compiled' ? 'status-complete' : 'status-pending' }}">
                                    {{ $document->status }}
                                </span>
                            </td>
                            <td>
                                {{ $document->updated_at ? $document->updated_at->format('M d, Y') : 'Not submitted' }}
                            </td>
                            <td>
                                @if($document->drive_link)
                                    <a href="{{ $document->drive_link }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                        <i class="bi bi-link-45deg mr-1"></i>View Document
                                    </a>
                                @else
                                    <span class="text-gray-400">No link</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td class="font-medium">{{ $member->name }}</td>
                            <td>{{ $assignment->subject->code }} - {{ $assignment->subject->title }}</td>
                            <td colspan="4" class="text-center text-gray-500">
                                No compliance documents found
                            </td>
                        </tr>
                        @endforelse
                    @empty
                    <tr>
                        <td class="font-medium">{{ $member->name }}</td>
                        <td colspan="5" class="text-center text-gray-500">
                            No assignments found
                        </td>
                    </tr>
                    @endforelse
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-500">
                        <i class="bi bi-people text-4xl text-gray-300 mb-2 block"></i>
                        <p>No faculty members found in your program.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination - Show only when records exceed 10 rows -->
@if($faculty->count() > 10)
<div class="pagination">
    @if($faculty->currentPage() > 1)
        <a href="{{ $faculty->previousPageUrl() }}" class="pagination-link">
            <i class="bi bi-chevron-left mr-1"></i>Previous
        </a>
    @endif
    
    @for($i = 1; $i <= $faculty->lastPage(); $i++)
        <a href="{{ $faculty->url($i) }}" 
           class="pagination-link {{ $i === $faculty->currentPage() ? 'active' : '' }}">
            {{ $i }}
        </a>
    @endfor
    
    @if($faculty->currentPage() < $faculty->lastPage())
        <a href="{{ $faculty->nextPageUrl() }}" class="pagination-link">
            Next<i class="bi bi-chevron-right ml-1"></i>
        </a>
    @endif
</div>
@endif

<div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
    <div class="flex items-start">
        <i class="bi bi-info-circle text-blue-500 text-xl mr-3 mt-0.5"></i>
        <div>
            <h4 class="font-medium text-blue-900 mb-1">Compliance Monitoring</h4>
            <p class="text-blue-700 text-sm">
                Use the filters above to search for specific faculty members or compliance statuses. 
                The table shows all compliance documents for faculty under your supervision.
                @if($faculty->count() > 10)
                    <br><strong>Pagination is enabled</strong> when records exceed 10 rows for better performance.
                @endif
            </p>
        </div>
    </div>
</div>
@endsection
