@extends('layouts.app')

@section('title', 'Semester Management - MIS')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Semester Management</h1>
            <p class="text-gray-600 mt-1">Manage academic semesters and sessions</p>
        </div>
        <button type="button" onclick="showCreateSemesterModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow-lg transition-colors">
            <i class="bi bi-plus-circle mr-2"></i>Create Semester
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="bi bi-calendar3 text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $semesters->count() }}</h3>
                    <p class="text-gray-600">Total Semesters</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="bi bi-check-circle text-green-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $semesters->where('is_active', true)->count() }}</h3>
                    <p class="text-gray-600">Active Semesters</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="bi bi-calendar-year text-purple-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ date('Y') }}</h3>
                    <p class="text-gray-600">Current Year</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-orange-100 rounded-full">
                    <i class="bi bi-pause-circle text-orange-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $semesters->where('is_active', false)->count() }}</h3>
                    <p class="text-gray-600">Inactive Semesters</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            <div class="flex items-center">
                <i class="bi bi-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <div class="flex items-center">
                <i class="bi bi-exclamation-triangle mr-2"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Semesters Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900">Semesters List</h2>
                <div class="flex items-center space-x-2">
                    <input type="text" id="searchSemesters" placeholder="Search semesters..." 
                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <button class="px-3 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            @if($semesters->count() > 0)
                <table class="w-full" id="semestersTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Academic Year</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($semesters as $semester)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">#{{ $semester->id }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="p-2 bg-blue-100 rounded-full mr-3">
                                            <i class="bi bi-calendar3 text-blue-600"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ $semester->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $semester->year }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($semester->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $semester->created_at ? $semester->created_at->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <button type="button" 
                                                onclick="editSemester({{ $semester->id }}, '{{ $semester->name }}', '{{ $semester->year }}', {{ $semester->is_active ? 'true' : 'false' }})"
                                                class="text-blue-600 hover:text-blue-900 transition-colors">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" 
                                                onclick="toggleSemesterStatus({{ $semester->id }}, {{ $semester->is_active ? 'false' : 'true' }})"
                                                class="text-{{ $semester->is_active ? 'yellow' : 'green' }}-600 hover:text-{{ $semester->is_active ? 'yellow' : 'green' }}-900 transition-colors">
                                            <i class="bi bi-{{ $semester->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                        <button type="button" 
                                                onclick="deleteSemester({{ $semester->id }}, '{{ $semester->name }}')"
                                                class="text-red-600 hover:text-red-900 transition-colors">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-12">
                    <i class="bi bi-calendar3 text-gray-400 text-6xl"></i>
                    <h3 class="text-xl font-medium text-gray-900 mt-4">No Semesters Found</h3>
                    <p class="text-gray-500 mt-2">Start by creating your first semester.</p>
                    <button type="button" onclick="showCreateSemesterModal()" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors">
                        <i class="bi bi-plus-circle mr-2"></i>Create First Semester
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Semester Modal -->
<div id="createSemesterModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Create New Semester</h3>
                <button type="button" onclick="hideCreateSemesterModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form action="{{ route('mis.semesters.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Semester Name <span class="text-red-500">*</span>
                    </label>
                    <select id="name" name="name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Semester</option>
                        <option value="1st Semester">1st Semester</option>
                        <option value="2nd Semester">2nd Semester</option>
                        <option value="Summer">Summer</option>
                    </select>
                </div>
                
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-1">
                        Academic Year <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="year" name="year" required
                           placeholder="e.g., 2024-2025" value="{{ date('Y') }}-{{ date('Y') + 1 }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Set as Active Semester</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="hideCreateSemesterModal()" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Create Semester
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Semester Modal -->
<div id="editSemesterModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Edit Semester</h3>
                <button type="button" onclick="hideEditSemesterModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form id="editSemesterForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Semester Name <span class="text-red-500">*</span>
                    </label>
                    <select id="edit_name" name="name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="1st Semester">1st Semester</option>
                        <option value="2nd Semester">2nd Semester</option>
                        <option value="Summer">Summer</option>
                    </select>
                </div>
                
                <div>
                    <label for="edit_year" class="block text-sm font-medium text-gray-700 mb-1">
                        Academic Year <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="edit_year" name="year" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" id="edit_is_active" name="is_active" value="1"
                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Set as Active Semester</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="hideEditSemesterModal()" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Update Semester
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Modal functions
function showCreateSemesterModal() {
    document.getElementById('createSemesterModal').classList.remove('hidden');
}

function hideCreateSemesterModal() {
    document.getElementById('createSemesterModal').classList.add('hidden');
}

function showEditSemesterModal() {
    document.getElementById('editSemesterModal').classList.remove('hidden');
}

function hideEditSemesterModal() {
    document.getElementById('editSemesterModal').classList.add('hidden');
}

function editSemester(id, name, year, isActive) {
    document.getElementById('editSemesterForm').action = `/mis/semesters/${id}`;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_year').value = year;
    document.getElementById('edit_is_active').checked = isActive;
    showEditSemesterModal();
}

function toggleSemesterStatus(id, newStatus) {
    if (confirm('Are you sure you want to change the semester status?')) {
        // Create and submit a form for status toggle
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/mis/semesters/${id}/toggle-status`;
        form.innerHTML = `
            @csrf
            @method('PATCH')
            <input type="hidden" name="is_active" value="${newStatus}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteSemester(id, name) {
    if (confirm(`Are you sure you want to delete the semester "${name}"? This action cannot be undone.`)) {
        // Create and submit a form for DELETE request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/mis/semesters/${id}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchSemesters');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const tableRows = document.querySelectorAll('#semestersTable tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
});

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target === document.getElementById('createSemesterModal')) {
        hideCreateSemesterModal();
    }
    if (e.target === document.getElementById('editSemesterModal')) {
        hideEditSemesterModal();
    }
});
</script>
@endsection
