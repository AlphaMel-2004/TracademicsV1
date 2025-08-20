@extends('layouts.app')

@section('title', $assignment->subject->code . ' - Requirements')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-4 mb-4">
        <a href="{{ route('subjects.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="bi bi-arrow-left mr-2"></i>Back to Subjects
        </a>
    </div>
    
    <h1 class="text-3xl font-bold text-gray-800">{{ $assignment->subject->code }}</h1>
    <p class="text-gray-600 mt-2">{{ $assignment->subject->title }} - {{ $assignment->term->name }} {{ $assignment->term->year }}</p>
</div>

@if(session('status'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
        <div class="flex items-center">
            <i class="bi bi-check-circle text-green-500 mr-2"></i>
            <span class="text-green-700">{{ session('status') }}</span>
        </div>
    </div>
@endif

<!-- Requirements Table -->
<div class="bg-white rounded-lg shadow overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Compliance Requirements</h3>
        <p class="text-sm text-gray-600 mt-1">Submit Google Drive links for each required document</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="uniform-table">
            <thead>
                <tr>
                    <th>Requirement Name</th>
                    <th>Description</th>
                    <th>Due Date</th>
                    <th>Submission Link</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documentTypes as $docType)
                    @php
                        $existingDoc = $assignment->complianceDocuments->where('document_type_id', $docType->id)->first();
                        $status = $existingDoc ? $existingDoc->status : 'Pending';
                        $driveLink = $existingDoc ? $existingDoc->drive_link : '';
                        $submittedDate = $existingDoc ? $existingDoc->updated_at : null;
                    @endphp
                    <tr>
                        <td class="font-medium">{{ $docType->name }}</td>
                        <td class="text-gray-600">{{ $docType->description ?? 'No description available' }}</td>
                        <td>
                            <span class="text-gray-600">End of Term</span>
                        </td>
                        <td>
                            @if($driveLink)
                                <a href="{{ $driveLink }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                    <i class="bi bi-link-45deg mr-1"></i>View Document
                                </a>
                            @else
                                <span class="text-gray-400">Not submitted</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge {{ $status === 'Compiled' ? 'status-complete' : 'status-pending' }}">
                                {{ $status }}
                            </span>
                        </td>
                        <td>
                            <button onclick="openSubmissionModal('{{ $docType->id }}', '{{ $docType->name }}', '{{ $driveLink }}')" 
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                {{ $driveLink ? 'Update' : 'Submit' }}
                            </button>
                        </td>
                    </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-500">
                        <i class="bi bi-file-earmark-text text-4xl text-gray-300 mb-2 block"></i>
                        <p>No compliance requirements found for this subject.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Compliance Summary -->
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Compliance Summary</h3>
    
    @php
        $totalRequirements = $documentTypes->count();
        $completedRequirements = $assignment->complianceDocuments->where('status', 'Compiled')->count();
        $percentage = $totalRequirements > 0 ? round(($completedRequirements / $totalRequirements) * 100, 1) : 0;
    @endphp
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
        <div class="text-center">
            <div class="text-2xl font-bold text-blue-600">{{ $totalRequirements }}</div>
            <div class="text-sm text-gray-600">Total Requirements</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-green-600">{{ $completedRequirements }}</div>
            <div class="text-sm text-gray-600">Completed</div>
        </div>
        <div class="text-center">
            <div class="text-2xl font-bold text-purple-600">{{ $percentage }}%</div>
            <div class="text-sm text-gray-600">Completion Rate</div>
        </div>
    </div>
    
    <div class="w-full bg-gray-200 rounded-full h-3">
        <div class="bg-purple-500 h-3 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
    </div>
</div>

<!-- Document Submission Modal -->
<div id="submissionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Submit Document</h3>
                <button onclick="closeSubmissionModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="bi bi-x text-xl"></i>
                </button>
            </div>
            
            <form method="POST" action="{{ route('subjects.submit', $assignment->id) }}" class="space-y-4">
                @csrf
                <input type="hidden" id="modal_document_type_id" name="document_type_id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                    <input type="text" id="modal_document_name" class="form-input bg-gray-50" readonly>
                </div>
                
                <div>
                    <label for="modal_drive_link" class="block text-sm font-medium text-gray-700 mb-2">Google Drive Link</label>
                    <input type="url" id="modal_drive_link" name="drive_link" required
                           class="form-input" placeholder="https://drive.google.com/...">
                </div>
                
                <div>
                    <label for="modal_self_evaluation" class="block text-sm font-medium text-gray-700 mb-2">Self Evaluation (Optional)</label>
                    <textarea id="modal_self_evaluation" name="self_evaluation" rows="3"
                              class="form-input" placeholder="Brief self-evaluation or notes..."></textarea>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeSubmissionModal()" class="btn-secondary">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-upload mr-2"></i>Submit Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openSubmissionModal(documentTypeId, documentName, existingLink) {
    document.getElementById('modal_document_type_id').value = documentTypeId;
    document.getElementById('modal_document_name').value = documentName;
    document.getElementById('modal_drive_link').value = existingLink;
    document.getElementById('submissionModal').classList.remove('hidden');
}

function closeSubmissionModal() {
    document.getElementById('submissionModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('submissionModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSubmissionModal();
    }
});
</script>
@endsection
