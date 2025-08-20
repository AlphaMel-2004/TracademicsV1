@extends('layouts.app')

@section('title', 'Faculty Compliance - ' . $program->name)

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-4 mb-4">
        <a href="{{ route('monitor.faculty') }}" class="text-blue-600 hover:text-blue-800">
            <i class="bi bi-arrow-left mr-2"></i>Back to Programs
        </a>
    </div>
    
    <h1 class="text-3xl font-bold text-gray-800">Faculty Compliance</h1>
    <p class="text-gray-600 mt-2">{{ $program->name }} - {{ $user->department->name }}</p>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Faculty Members and Compliance Status</h3>
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
                                No compliance documents found for this assignment
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
                        <p>No faculty members found in this program.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
    <div class="flex items-start">
        <i class="bi bi-info-circle text-blue-500 text-xl mr-3 mt-0.5"></i>
        <div>
            <h4 class="font-medium text-blue-900 mb-1">Compliance Overview</h4>
            <p class="text-blue-700 text-sm">
                This table shows the compliance status of all faculty members in the {{ $program->name }} program. 
                Click on document links to view submitted materials.
            </p>
        </div>
    </div>
</div>
@endsection
