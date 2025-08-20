@extends('layouts.app')

@section('title', 'Faculty Assignments - Program Head')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Faculty Assignments</h1>
    <p class="text-gray-600 mt-2">Manage subject assignments for faculty under your supervision</p>
</div>

@if(session('status'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
        <div class="flex items-center">
            <i class="bi bi-check-circle text-green-500 mr-2"></i>
            <span class="text-green-700">{{ session('status') }}</span>
        </div>
    </div>
@endif

<!-- Assignment Form -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Subject to Faculty</h3>
    
    <form method="POST" action="{{ route('assignments.store') }}" class="space-y-4">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="faculty_id" class="block text-sm font-medium text-gray-700 mb-2">Faculty Member</label>
                <select id="faculty_id" name="faculty_id" class="form-input" required>
                    <option value="">Select Faculty Member</option>
                    @foreach($faculty as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                <select id="subject_id" name="subject_id" class="form-input" required>
                    <option value="">Select Subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->code }} - {{ $subject->title }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="term_id" class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                <select id="term_id" name="term_id" class="form-input" required>
                    <option value="">Select Term</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}">{{ $term->name }} {{ $term->year }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="flex justify-end">
            <button type="submit" class="btn-primary">
                <i class="bi bi-plus-circle mr-2"></i>Assign Subject
            </button>
        </div>
    </form>
</div>

<!-- Current Assignments -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Current Assignments</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="uniform-table">
            <thead>
                <tr>
                    <th>Faculty Name</th>
                    <th>Subject</th>
                    <th>Term</th>
                    <th>Assignment Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $assignment)
                <tr>
                    <td class="font-medium">{{ $assignment->user->name }}</td>
                    <td>{{ $assignment->subject->code }} - {{ $assignment->subject->title }}</td>
                    <td>{{ $assignment->term->name }} {{ $assignment->term->year }}</td>
                    <td>{{ $assignment->created_at->format('M d, Y') }}</td>
                    <td>
                        <span class="status-badge status-complete">Active</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-8 text-gray-500">
                        <i class="bi bi-diagram-3 text-4xl text-gray-300 mb-2 block"></i>
                        <p>No assignments found. Create your first assignment above.</p>
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
            <h4 class="font-medium text-blue-900 mb-1">Assignment Management</h4>
            <p class="text-blue-700 text-sm">
                Assign subjects to faculty members under your supervision. Each assignment will create compliance requirements 
                that faculty must complete by the end of the term.
            </p>
        </div>
    </div>
</div>
@endsection


