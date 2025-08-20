@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="p-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">
        @if($data['type'] === 'vpaa')
            Institution Dashboard
        @elseif($data['type'] === 'dean')
            {{ $user->department->name }} Dashboard
        @elseif($data['type'] === 'program_head')
            {{ $user->program->name }} Dashboard
        @else
            My Dashboard
        @endif
    </h1>

    @if($data['type'] === 'vpaa')
        <!-- VPAA Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="bi bi-people-fill text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Faculty</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $data['overall_stats']['total_faculty'] }}</p>
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
                        <p class="text-2xl font-semibold text-gray-900">{{ $data['overall_stats']['total_assignments'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="bi bi-check-circle-fill text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Complied Documents</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $data['overall_stats']['total_complied'] }}</p>
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
                        <p class="text-2xl font-semibold text-gray-900">
                            {{ $data['overall_stats']['total_required'] > 0 ? round(($data['overall_stats']['total_complied'] / $data['overall_stats']['total_required']) * 100, 1) : 0 }}%
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compliance Monitoring Chart with Filters -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Compliance Monitoring Chart</h3>
                <p class="text-sm text-gray-600 mt-1">Overall institution compliance status</p>
                
                <!-- Filters for VPAA -->
                <div class="mt-4 flex gap-4">
                    <div>
                        <label for="department_filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Department</label>
                        <select id="department_filter" class="form-input w-48" onchange="filterCompliance()">
                            <option value="">All Departments</option>
                            @foreach($data['departments'] as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="program_filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Program</label>
                        <select id="program_filter" class="form-input w-48" onchange="filterCompliance()">
                            <option value="">All Programs</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600">{{ $data['compliance_chart']['total'] }}</div>
                        <div class="text-sm text-gray-600">Total Requirements</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600">{{ $data['compliance_chart']['compiled'] }}</div>
                        <div class="text-sm text-gray-600">Completed</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-purple-600">{{ $data['compliance_chart']['percentage'] }}%</div>
                        <div class="text-sm text-gray-600">Completion Rate</div>
                    </div>
                </div>
                
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div class="bg-gradient-to-r from-green-500 to-blue-500 h-4 rounded-full transition-all duration-300" 
                         style="width: {{ $data['compliance_chart']['percentage'] }}%"></div>
                </div>
                
                <div class="mt-4 flex justify-between text-sm text-gray-600">
                    <span>0%</span>
                    <span>50%</span>
                    <span>100%</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Department Overview</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-green-600">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Faculty Count</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Assignments</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data['departments'] as $dept)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $dept->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $dept->faculty_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $dept->assignment_count }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @elseif($data['type'] === 'dean')
        <!-- Dean Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="bi bi-people-fill text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Department Faculty</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $data['dept_stats']['faculty_count'] }}</p>
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
                        <p class="text-2xl font-semibold text-gray-900">{{ $data['dept_stats']['assignment_count'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="bi bi-percent text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Compliance Rate</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $data['dept_stats']['compliance_rate'] }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compliance Chart for Department -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Department Compliance Chart</h3>
                <p class="text-sm text-gray-600 mt-1">Compliance status for programs under {{ $user->department->name }}</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600">{{ $data['compliance_chart']['total'] }}</div>
                        <div class="text-sm text-gray-600">Total Requirements</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600">{{ $data['compliance_chart']['compiled'] }}</div>
                        <div class="text-sm text-gray-600">Completed</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-purple-600">{{ $data['compliance_chart']['percentage'] }}%</div>
                        <div class="text-sm text-gray-600">Completion Rate</div>
                    </div>
                </div>
                
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div class="bg-gradient-to-r from-green-500 to-blue-500 h-4 rounded-full transition-all duration-300" 
                         style="width: {{ $data['compliance_chart']['percentage'] }}%"></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Programs in {{ $user->department->name }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-green-600">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Program</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Faculty Count</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Assignments</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data['programs'] as $prog)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $prog->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $prog->faculty_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $prog->assignment_count }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @elseif($data['type'] === 'program_head')
        <!-- Program Head Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="bi bi-people-fill text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Program Faculty</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $data['prog_stats']['faculty_count'] }}</p>
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
                        <p class="text-2xl font-semibold text-gray-900">{{ $data['prog_stats']['assignment_count'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="bi bi-percent text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Compliance Rate</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $data['prog_stats']['compliance_rate'] }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compliance Chart for Program -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Program Compliance Chart</h3>
                <p class="text-sm text-gray-600 mt-1">Compliance status for faculty under {{ $user->program->name }}</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600">{{ $data['compliance_chart']['total'] }}</div>
                        <div class="text-sm text-gray-600">Total Requirements</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600">{{ $data['compliance_chart']['compiled'] }}</div>
                        <div class="text-sm text-gray-600">Completed</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-purple-600">{{ $data['compliance_chart']['percentage'] }}%</div>
                        <div class="text-sm text-gray-600">Completion Rate</div>
                    </div>
                </div>
                
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div class="bg-gradient-to-r from-green-500 to-blue-500 h-4 rounded-full transition-all duration-300" 
                         style="width: {{ $data['compliance_chart']['percentage'] }}%"></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Faculty in {{ $user->program->name }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-green-600">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Assignments</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data['faculty'] as $faculty)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $faculty->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $faculty->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $faculty->facultyAssignments->count() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @else
        <!-- Faculty Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="bi bi-book-fill text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">My Assignments</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $data['assignments']->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="bi bi-percent text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">My Compliance Rate</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $data['compliance_rate'] }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Compliance Chart -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">My Compliance Status</h3>
                <p class="text-sm text-gray-600 mt-1">Track your document submission progress</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600">{{ $data['compliance_chart']['total'] }}</div>
                        <div class="text-sm text-gray-600">Total Requirements</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600">{{ $data['compliance_chart']['compiled'] }}</div>
                        <div class="text-sm text-gray-600">Completed</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-purple-600">{{ $data['compliance_chart']['percentage'] }}%</div>
                        <div class="text-sm text-gray-600">Completion Rate</div>
                    </div>
                </div>
                
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div class="bg-gradient-to-r from-green-500 to-blue-500 h-4 rounded-full transition-all duration-300" 
                         style="width: {{ $data['compliance_chart']['percentage'] }}%"></div>
                </div>
            </div>
        </div>

        @if($data['assignments']->count() > 0)
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">My Subject Assignments</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-green-600">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Term</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Compliance Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data['assignments'] as $assignment)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $assignment->subject->code }} - {{ $assignment->subject->title }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $assignment->term->name }} {{ $assignment->term->year }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @php
                                    $compiled = $assignment->complianceDocuments->where('status', 'Compiled')->count();
                                    $total = $assignment->complianceDocuments->count();
                                    $percentage = $total > 0 ? round(($compiled / $total) * 100, 1) : 0;
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $percentage >= 80 ? 'bg-green-100 text-green-800' : ($percentage >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $percentage }}% ({{ $compiled }}/{{ $total }})
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center">
                <i class="bi bi-inbox text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Assignments Yet</h3>
                <p class="text-gray-500">You haven't been assigned to any subjects yet. Contact your Program Head for assignments.</p>
            </div>
        </div>
        @endif
    @endif
</div>

@if($data['type'] === 'vpaa')
<script>
// Populate program filter based on department selection
document.getElementById('department_filter').addEventListener('change', function() {
    const departmentId = this.value;
    const programFilter = document.getElementById('program_filter');
    
    // Clear program filter
    programFilter.innerHTML = '<option value="">All Programs</option>';
    
    if (departmentId) {
        // Fetch programs for selected department
        fetch(`/api/departments/${departmentId}/programs`)
            .then(response => response.json())
            .then(programs => {
                programs.forEach(program => {
                    const option = document.createElement('option');
                    option.value = program.id;
                    option.textContent = program.name;
                    programFilter.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching programs:', error));
    }
});

function filterCompliance() {
    const departmentId = document.getElementById('department_filter').value;
    const programId = document.getElementById('program_filter').value;
    
    // Here you would implement the actual filtering logic
    // For now, this is a placeholder for the filter functionality
    console.log('Filtering by:', { departmentId, programId });
}
</script>
@endif
@endsection



