@extends('layouts.app')

@section('title', 'Curriculum Management')

@section('styles')
<style>
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Curriculum of Subjects</h1>
            <p class="text-gray-600 mt-1">Manage and auto-load curriculum subjects for programs</p>
        </div>
        <button onclick="showAutoLoadModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow-lg transition-colors">
            <i class="bi bi-download mr-2"></i>Auto-Load Subjects
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="bi bi-graduation-cap text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $totalCourses }}</h3>
                    <p class="text-gray-600">Total Courses</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="bi bi-book text-green-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $curriculumSubjectsCount }}</h3>
                    <p class="text-gray-600">Unique Subjects</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="bi bi-list-ul text-purple-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $totalCurriculums }}</h3>
                    <p class="text-gray-600">Curriculums</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-orange-100 rounded-full">
                    <i class="bi bi-building text-orange-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $departments->count() }}</h3>
                    <p class="text-gray-600">Departments</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Courses and Curriculums -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($courses as $course)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-white">{{ $course->course_code }}</h3>
                            <p class="text-blue-100 text-sm">{{ $course->course_name }}</p>
                        </div>
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <i class="bi bi-graduation-cap text-white text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    @forelse($course->curriculums as $curriculum)
                        <div class="mb-4 last:mb-0">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-medium text-gray-900">{{ $curriculum->name }}</h4>
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                    {{ $curriculum->subjects->count() }} subjects
                                </span>
                            </div>
                            
                            <!-- Year Level Breakdown -->
                            <div class="space-y-2">
                                @for($year = 1; $year <= 4; $year++)
                                    @php
                                        $yearSubjects = $curriculum->subjects->where('year_level', $year);
                                        $firstSem = $yearSubjects->where('semester', '1st')->count();
                                        $secondSem = $yearSubjects->where('semester', '2nd')->count();
                                    @endphp
                                    @if($yearSubjects->count() > 0)
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-600">Year {{ $year }}</span>
                                            <div class="flex gap-2">
                                                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs">
                                                    1st: {{ $firstSem }}
                                                </span>
                                                <span class="bg-orange-100 text-orange-700 px-2 py-0.5 rounded text-xs">
                                                    2nd: {{ $secondSem }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                @endfor
                            </div>
                            
                            <div class="mt-3 flex gap-2">
                                <button onclick="viewCurriculum({{ $curriculum->id }}, '{{ $course->course_name }}')" 
                                        class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-700 py-2 px-3 rounded-lg text-sm transition-colors">
                                    <i class="bi bi-eye mr-1"></i>View Details
                                </button>
                                <button onclick="autoLoadForCourse({{ $course->id }}, '{{ $course->course_name }}')" 
                                        class="flex-1 bg-green-50 hover:bg-green-100 text-green-700 py-2 px-3 rounded-lg text-sm transition-colors">
                                    <i class="bi bi-arrow-repeat mr-1"></i>Reload
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6">
                            <i class="bi bi-book text-gray-400 text-3xl mb-3"></i>
                            <p class="text-gray-500 mb-3">No curriculum loaded</p>
                            <button onclick="autoLoadForCourse({{ $course->id }}, '{{ $course->course_name }}')" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg text-sm transition-colors">
                                <i class="bi bi-download mr-2"></i>Load Curriculum
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <div class="text-gray-500">
                    <i class="bi bi-graduation-cap text-4xl mb-4"></i>
                    <p class="text-lg">No courses found</p>
                    <p class="text-sm">Please create courses first before managing curriculum</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Auto-Load Modal -->
<div id="autoLoadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 text-center">Auto-Load Curriculum</h3>
            <form action="{{ route('mis.curriculum.load', 'PLACEHOLDER') }}" method="POST" id="autoLoadForm" class="mt-4">
                @csrf
                <div class="mb-4">
                    <label for="course_id" class="block text-sm font-medium text-gray-700 mb-2">Select Course</label>
                    <select id="course_id" name="course_id" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Choose a course</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->course_code }} - {{ $course->course_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="curriculum_name" class="block text-sm font-medium text-gray-700 mb-2">Curriculum Name</label>
                    <input type="text" id="curriculum_name" name="curriculum_name" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., 2024 Curriculum" value="2024 Curriculum">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition-colors">
                        <i class="bi bi-download mr-2"></i>Load Subjects
                    </button>
                    <button type="button" onclick="hideAutoLoadModal()" 
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-lg transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Curriculum Details Modal -->
<div id="curriculumModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-[800px] shadow-lg rounded-xl bg-white">
        <div class="mt-3">
            <h3 id="curriculumTitle" class="text-lg font-medium text-gray-900 text-center mb-4"></h3>
            <div id="curriculumContent" class="max-h-96 overflow-y-auto">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="flex justify-center mt-4">
                <button onclick="hideCurriculumModal()" 
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-6 rounded-lg transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showAutoLoadModal() {
    document.getElementById('autoLoadModal').classList.remove('hidden');
}

function hideAutoLoadModal() {
    document.getElementById('autoLoadModal').classList.add('hidden');
}

function autoLoadForCourse(courseId, courseName) {
    document.getElementById('course_id').value = courseId;
    document.getElementById('curriculum_name').value = `2024 ${courseName} Curriculum`;
    document.getElementById('autoLoadForm').action = `/mis/curriculum/${courseId}/load`;
    showAutoLoadModal();
}

function viewCurriculum(curriculumId, courseName) {
    document.getElementById('curriculumTitle').textContent = `${courseName} - Curriculum Details`;
    document.getElementById('curriculumContent').innerHTML = '<div class="text-center py-4"><i class="bi bi-arrow-clockwise spin text-blue-500 text-2xl"></i><br>Loading curriculum details...</div>';
    document.getElementById('curriculumModal').classList.remove('hidden');
    
    // Make AJAX call to load curriculum details
    fetch(`/curriculum/${curriculumId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('curriculumContent').innerHTML = data.html;
            } else {
                document.getElementById('curriculumContent').innerHTML = `
                    <div class="text-center py-8">
                        <i class="bi bi-exclamation-triangle text-red-500 text-4xl mb-4"></i>
                        <p class="text-gray-600">Error loading curriculum details</p>
                        <p class="text-sm text-gray-500">${data.message || 'Please try again later'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('curriculumContent').innerHTML = `
                <div class="text-center py-8">
                    <i class="bi bi-exclamation-triangle text-red-500 text-4xl mb-4"></i>
                    <p class="text-gray-600">Failed to load curriculum details</p>
                    <p class="text-sm text-gray-500">Please check your connection and try again</p>
                </div>
            `;
        });
}

function hideCurriculumModal() {
    document.getElementById('curriculumModal').classList.add('hidden');
}
</script>
@endsection
