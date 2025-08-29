@extends('layouts.app')

@section('title', 'Compliance - ' . $subjectCode)

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center mb-2">
                <a href="{{ route('compliances.index') }}" class="text-blue-600 hover:text-blue-800 mr-3">
                    <i class="bi bi-arrow-left text-lg"></i>
                </a>
                <h1 class="text-3xl font-bold text-gray-800">{{ $subjectCode }} Compliance</h1>
            </div>
            <p class="text-gray-600">
                {{ $subjectDescription }} | {{ $currentSemester->name }} {{ $currentSemester->year }}
            </p>
        </div>
        <div class="progress-indicator">
            @php
                $totalDocuments = count($facultyWideRequirements) + count($semesterSpecificRequirements);
                $completedDocuments = 0;
                // Count completed documents (this would be calculated properly in the controller)
                foreach($facultyWideRequirements as $req) {
                    if(isset($complianceData[$req->id]) && count($complianceData[$req->id]['links']) > 0) {
                        $completedDocuments++;
                    }
                }
                foreach($semesterSpecificRequirements as $req) {
                    if(isset($complianceData[$req->id]) && count($complianceData[$req->id]['links']) > 0) {
                        $completedDocuments++;
                    }
                }
                $percentage = $totalDocuments > 0 ? round(($completedDocuments / $totalDocuments) * 100, 1) : 0;
            @endphp
            <div class="progress-text">
                Progress: {{ $completedDocuments }}/{{ $totalDocuments }} ({{ $percentage }}%)
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar" style="width: {{ $percentage }}%"></div>
            </div>
        </div>
    </div>
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

<!-- Faculty-wide Requirements -->
@if(count($facultyWideRequirements) > 0)
<div class="mb-8">
    <div class="info-section faculty-requirements">
        <i class="bi bi-person-check"></i>
        <div>
            <h3>Faculty-wide Requirements</h3>
            <p>Submit these documents once per faculty user (applies to all subjects)</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="uniform-table">
                <thead>
                    <tr>
                        <th>DOCUMENT TYPE</th>
                        <th>SUBMITTED LINKS</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($facultyWideRequirements as $requirement)
                        @php
                            $compliance = isset($complianceData[$requirement->id]) ? $complianceData[$requirement->id] : null;
                            $links = $compliance ? $compliance['links'] : [];
                            $actualStatus = $compliance ? $compliance['compliance']->status : 'Not Complied';
                            
                            // Determine display status based on actual compliance status
                            if ($actualStatus === 'Complied') {
                                $status = 'Complied';
                                $statusClass = 'bg-green-100 text-green-800';
                            } elseif ($actualStatus === 'Not Applicable') {
                                $status = 'Not Applicable';
                                $statusClass = 'bg-gray-100 text-gray-800';
                            } else {
                                $status = 'Not Complied';
                                $statusClass = 'bg-yellow-100 text-yellow-800';
                            }
                        @endphp
                        <tr>
                            <td class="font-medium">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $requirement->name }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $requirement->description }}</div>
                                </div>
                            </td>
                            <td>
                                @if(count($links) > 0)
                                    <div class="space-y-2">
                                        @foreach($links as $link)
                                            <div class="link-item">
                                                <div class="link-content">
                                                    <a href="{{ $link->drive_link }}" target="_blank" 
                                                       class="link-url">
                                                        <i class="bi bi-link-45deg mr-1"></i>
                                                        {{ $link->description ?: 'Drive Link' }}
                                                    </a>
                                                    <div class="link-meta">
                                                        Submitted: {{ $link->submitted_at->format('M d, Y h:i A') }}
                                                    </div>
                                                </div>
                                                <button onclick="deleteLink({{ $link->id }})" 
                                                        class="action-btn btn-delete" 
                                                        title="Delete this link">
                                                    <i class="bi bi-trash text-sm"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 text-sm italic">No links submitted</span>
                                @endif
                            </td>
                            <td>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $statusClass }} status-{{ strtolower(str_replace(' ', '-', $status)) }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td>
                                <div class="action-btn-group">
                                    <button onclick="openSubmitModal({{ $requirement->id }}, '{{ $requirement->name }}')" 
                                            class="action-btn btn-add"
                                            title="Add new link">
                                        <i class="bi bi-plus mr-1"></i>Add Link
                                    </button>
                                    @if($actualStatus !== 'Not Applicable')
                                        <button onclick="markAsNotApplicable({{ $requirement->id }})" 
                                                class="action-btn btn-na"
                                                title="Mark as not applicable">
                                            <i class="bi bi-x-circle mr-1"></i>N/A
                                        </button>
                                    @else
                                        <button onclick="unmarkNotApplicable({{ $requirement->id }})" 
                                                class="action-btn btn-revert"
                                                title="Revert from not applicable">
                                            <i class="bi bi-arrow-clockwise mr-1"></i>Revert
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Semester-specific Requirements -->
@if(count($semesterSpecificRequirements) > 0)
<div class="mb-8">
    <div class="info-section semester-requirements">
        <i class="bi bi-calendar-check"></i>
        <div>
            <h3>Semester-specific Requirements</h3>
            <p>Submit these documents for {{ $subjectCode }} each semester</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="uniform-table">
                <thead>
                    <tr>
                        <th>DOCUMENT TYPE</th>
                        <th>SUBMITTED LINKS</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($semesterSpecificRequirements as $requirement)
                        @php
                            $compliance = isset($complianceData[$requirement->id]) ? $complianceData[$requirement->id] : null;
                            $links = $compliance ? $compliance['links'] : [];
                            $actualStatus = $compliance ? $compliance['compliance']->status : 'Not Complied';
                            
                            // Determine display status based on actual compliance status
                            if ($actualStatus === 'Complied') {
                                $status = 'Complied';
                                $statusClass = 'bg-green-100 text-green-800';
                            } elseif ($actualStatus === 'Not Applicable') {
                                $status = 'Not Applicable';
                                $statusClass = 'bg-gray-100 text-gray-800';
                            } else {
                                $status = 'Not Complied';
                                $statusClass = 'bg-yellow-100 text-yellow-800';
                            }
                            
                            // Special handling for Syllabus to make it subject-specific
                            $displayName = $requirement->name;
                            if ($requirement->name === 'Syllabus') {
                                $displayName = 'Syllabus (' . $subjectCode . ')';
                            }
                        @endphp
                        <tr>
                            <td class="font-medium">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $displayName }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $requirement->description }}</div>
                                </div>
                            </td>
                            <td>
                                @if(count($links) > 0)
                                    <div class="space-y-2">
                                        @foreach($links as $link)
                                            <div class="link-item">
                                                <div class="link-content">
                                                    <a href="{{ $link->drive_link }}" target="_blank" 
                                                       class="link-url">
                                                        <i class="bi bi-link-45deg mr-1"></i>
                                                        {{ $link->description ?: 'Drive Link' }}
                                                    </a>
                                                    <div class="link-meta">
                                                        Submitted: {{ $link->submitted_at->format('M d, Y h:i A') }}
                                                    </div>
                                                </div>
                                                <button onclick="deleteLink({{ $link->id }})" 
                                                        class="action-btn btn-delete"
                                                        title="Delete this link">
                                                    <i class="bi bi-trash text-sm"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 text-sm italic">No links submitted</span>
                                @endif
                            </td>
                            <td>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $statusClass }} status-{{ strtolower(str_replace(' ', '-', $status)) }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td>
                                <div class="action-btn-group">
                                    <button onclick="openSubmitModal({{ $requirement->id }}, '{{ $displayName }}')" 
                                            class="action-btn btn-add"
                                            title="Add new link">
                                        <i class="bi bi-plus mr-1"></i>Add Link
                                    </button>
                                    @if($actualStatus !== 'Not Applicable')
                                        <button onclick="markAsNotApplicable({{ $requirement->id }})" 
                                                class="action-btn btn-na"
                                                title="Mark as not applicable">
                                            <i class="bi bi-x-circle mr-1"></i>N/A
                                        </button>
                                    @else
                                        <button onclick="unmarkNotApplicable({{ $requirement->id }})" 
                                                class="action-btn btn-revert"
                                                title="Revert from not applicable">
                                            <i class="bi bi-arrow-clockwise mr-1"></i>Revert
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Submit Link Modal -->
<div id="submitModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-xl">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900" id="modalTitle">Submit Compliance Link</h3>
                <button onclick="closeSubmitModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>
            
            <form id="submitForm" method="POST" action="{{ route('compliance.link.submit') }}" class="space-y-4">
                @csrf
                <input type="hidden" id="documentTypeId" name="document_type_id">
                <input type="hidden" name="subject_code" value="{{ $subjectCode }}">
                
                <div class="space-y-4">
                    <div>
                        <label for="driveLink" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="bi bi-link-45deg mr-1 text-blue-600"></i>
                            Google Drive Link *
                        </label>
                        <input type="url" 
                               id="driveLink" 
                               name="drive_link" 
                               required
                               class="form-input w-full border-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200" 
                               placeholder="https://drive.google.com/...">
                        <p class="text-xs text-gray-500 mt-1">Make sure the link is accessible to your supervisors</p>
                    </div>
                    
                    <div>
                        <label for="linkDescription" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="bi bi-file-text mr-1 text-gray-600"></i>
                            Description (Optional)
                        </label>
                        <input type="text" 
                               id="linkDescription" 
                               name="description" 
                               class="form-input w-full border-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-200" 
                               placeholder="Brief description of the document">
                    </div>
                </div>
                
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" 
                            onclick="closeSubmitModal()" 
                            class="action-btn btn-outline">
                        <i class="bi bi-x-circle mr-1"></i>Cancel
                    </button>
                    <button type="submit" 
                            class="action-btn btn-add">
                        <i class="bi bi-plus-circle mr-1"></i>Submit Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Link Form -->
<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
function openSubmitModal(documentTypeId, documentTypeName) {
    document.getElementById('documentTypeId').value = documentTypeId;
    document.getElementById('modalTitle').textContent = 'Submit Link for: ' + documentTypeName;
    document.getElementById('driveLink').value = '';
    document.getElementById('linkDescription').value = '';
    document.getElementById('submitModal').classList.remove('hidden');
}

function closeSubmitModal() {
    document.getElementById('submitModal').classList.add('hidden');
}

function deleteLink(linkId) {
    if (confirm('Are you sure you want to delete this link?')) {
        const form = document.getElementById('deleteForm');
        form.action = `{{ url('compliance/link') }}/${linkId}`;
        
        // Add loading state to delete button
        const deleteButtons = document.querySelectorAll(`button[onclick="deleteLink(${linkId})"]`);
        deleteButtons.forEach(btn => {
            btn.disabled = true;
            btn.classList.add('loading');
        });
        
        form.submit();
    }
}

function markAsNotApplicable(documentTypeId) {
    if (confirm('Are you sure you want to mark this requirement as Not Applicable?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('compliance/mark-not-applicable') }}/${documentTypeId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add subject code to identify the assignment
        const subjectCodeInput = document.createElement('input');
        subjectCodeInput.type = 'hidden';
        subjectCodeInput.name = 'subject_code';
        subjectCodeInput.value = '{{ $subjectCode }}';
        form.appendChild(subjectCodeInput);
        
        // Add loading state
        const naButtons = document.querySelectorAll(`button[onclick="markAsNotApplicable(${documentTypeId})"]`);
        naButtons.forEach(btn => {
            btn.disabled = true;
            btn.classList.add('loading');
        });
        
        document.body.appendChild(form);
        form.submit();
    }
}

function unmarkNotApplicable(documentTypeId) {
    if (confirm('Are you sure you want to revert this requirement from Not Applicable?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('compliance/unmark-not-applicable') }}/${documentTypeId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add subject code to identify the assignment
        const subjectCodeInput = document.createElement('input');
        subjectCodeInput.type = 'hidden';
        subjectCodeInput.name = 'subject_code';
        subjectCodeInput.value = '{{ $subjectCode }}';
        form.appendChild(subjectCodeInput);
        
        // Add loading state
        const revertButtons = document.querySelectorAll(`button[onclick="unmarkNotApplicable(${documentTypeId})"]`);
        revertButtons.forEach(btn => {
            btn.disabled = true;
            btn.classList.add('loading');
        });
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Close modal when clicking outside
document.getElementById('submitModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSubmitModal();
    }
});
</script>
@endsection
