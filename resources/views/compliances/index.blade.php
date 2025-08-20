@extends('layouts.app')

@section('title', 'My Compliance')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">My Compliance</h1>
    <p class="text-gray-600 mt-2">
        @if($assignments->count() > 0)
            {{ $assignments->first()->subject->code }} - {{ $assignments->first()->subject->title }} | 
            {{ $assignments->first()->term->name }} ({{ $assignments->first()->term->year }})
        @else
            No assignments found
        @endif
    </p>
    
    @if($assignments->count() > 0)
    <div class="mt-4 text-right">
        @php
            $totalRequirements = $documentTypes->count() * $assignments->count();
            $totalCompiled = 0;
            foreach($assignments as $assignment) {
                $totalCompiled += $assignment->complianceDocuments->where('status', 'Compiled')->count();
            }
            $percentage = $totalRequirements > 0 ? round(($totalCompiled / $totalRequirements) * 100, 1) : 0;
        @endphp
        <span class="text-lg font-semibold text-gray-700">
            Compiled: {{ $totalCompiled }}/{{ $totalRequirements }} ({{ $percentage }}%)
        </span>
    </div>
    @endif
</div>

@if(session('status'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
        <div class="flex items-center">
            <i class="bi bi-check-circle text-green-500 mr-2"></i>
            <span class="text-green-700">{{ session('status') }}</span>
        </div>
    </div>
@endif

@if($assignments->count() > 0)
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Compliance Requirements</h3>
        <p class="text-sm text-gray-600 mt-1">Submit Google Drive links for each required document</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="uniform-table">
            <thead>
                <tr>
                    <th>DOCUMENT</th>
                    <th>SELF-EVALUATION</th>
                    <th>STATUS</th>
                    <th>DRIVE LINK</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documentTypes as $docType)
                    @foreach($assignments as $assignment)
                        @php
                            $existingDoc = $assignment->complianceDocuments->where('document_type_id', $docType->id)->first();
                            $status = $existingDoc ? $existingDoc->status : 'Pending';
                            $driveLink = $existingDoc ? $existingDoc->drive_link : '';
                            $selfEvaluation = $existingDoc ? $existingDoc->self_evaluation : '';
                        @endphp
                        <tr>
                            <td class="font-medium">{{ $docType->name }}</td>
                            <td>
                                <input type="text" 
                                       name="self_evaluation_{{ $assignment->id }}_{{ $docType->id }}" 
                                       value="{{ $selfEvaluation }}"
                                       class="form-input w-full" 
                                       placeholder="e.g., Submitted via GDrive">
                            </td>
                            <td>
                                <select name="status_{{ $assignment->id }}_{{ $docType->id }}" class="form-input">
                                    <option value="Pending" {{ $status === 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Compiled" {{ $status === 'Compiled' ? 'selected' : '' }}>Compiled</option>
                                    <option value="Not Applicable" {{ $status === 'Not Applicable' ? 'selected' : '' }}>Not Applicable</option>
                                </select>
                            </td>
                            <td>
                                <input type="url" 
                                       name="drive_link_{{ $assignment->id }}_{{ $docType->id }}" 
                                       value="{{ $driveLink }}"
                                       class="form-input w-full" 
                                       placeholder="https://drive.google.com/...">
                            </td>
                            <td>
                                <button onclick="saveCompliance({{ $assignment->id }}, {{ $docType->id }})" 
                                        class="btn-primary text-sm">
                                    <i class="bi bi-download mr-1"></i>Save
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Hidden form for AJAX submission -->
<form id="complianceForm" method="POST" action="{{ route('compliances.store') }}" class="hidden">
    @csrf
    <input type="hidden" id="form_assignment_id" name="assignment_id">
    <input type="hidden" id="form_document_type_id" name="document_type_id">
    <input type="hidden" id="form_drive_link" name="drive_link">
    <input type="hidden" id="form_self_evaluation" name="self_evaluation">
</form>

<script>
function saveCompliance(assignmentId, documentTypeId) {
    const driveLink = document.querySelector(`input[name="drive_link_${assignmentId}_${documentTypeId}"]`).value;
    const selfEvaluation = document.querySelector(`input[name="self_evaluation_${assignmentId}_${documentTypeId}"]`).value;
    
    if (!driveLink) {
        alert('Please enter a Google Drive link');
        return;
    }
    
    // Set form values
    document.getElementById('form_assignment_id').value = assignmentId;
    document.getElementById('form_document_type_id').value = documentTypeId;
    document.getElementById('form_drive_link').value = driveLink;
    document.getElementById('form_self_evaluation').value = selfEvaluation;
    
    // Submit form
    document.getElementById('complianceForm').submit();
}
</script>

@else
<div class="text-center py-12">
    <i class="bi bi-journal-check text-6xl text-gray-300 mb-4"></i>
    <h3 class="text-lg font-medium text-gray-900 mb-2">No Assignments Found</h3>
    <p class="text-gray-500">You haven't been assigned to any subjects yet. Contact your Program Head for assignments.</p>
</div>
@endif

<div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
    <div class="flex items-start">
        <i class="bi bi-info-circle text-blue-500 text-xl mr-3 mt-0.5"></i>
        <div>
            <h4 class="font-medium text-blue-900 mb-1">Compliance Instructions</h4>
            <p class="text-blue-700 text-sm">
                For each required document, provide a Google Drive link and optional self-evaluation notes. 
                Click "Save" to update your compliance status. All documents are due by the end of the term.
            </p>
        </div>
    </div>
</div>
@endsection


