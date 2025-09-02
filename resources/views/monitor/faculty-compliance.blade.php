@extends('layouts.app')

@section('title', 'Faculty Compliance - ' . $program->name)

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-4 mb-4">
        <a href="{{ route('monitor.programs', $program->department->id) }}" class="text-blue-600 hover:text-blue-800">
            <i class="bi bi-arrow-left mr-2"></i>Back to Programs
        </a>
    </div>
    
    <h1 class="text-3xl font-bold text-gray-800">Faculty Compliance</h1>
    <p class="text-gray-600 mt-2">{{ $program->name }} - {{ $program->department->name }}</p>
</div>

<!-- Filters Section -->
<div class="filter-section">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Filters</h3>
    <form method="GET" action="{{ route('monitor.faculty-compliance', $program->id) }}" class="filter-grid">
        <div>
            <label for="faculty_name" class="block text-sm font-medium text-gray-700 mb-2">Faculty Name</label>
            <input type="text" id="faculty_name" name="faculty_name" value="{{ request('faculty_name') }}" 
                   class="form-input" placeholder="Search by faculty name">
        </div>
        
        <div class="flex items-end">
            <button type="submit" class="btn-primary mr-2">
                <i class="bi bi-search mr-2"></i>Apply Filters
            </button>
            <a href="{{ route('monitor.faculty-compliance', $program->id) }}" class="btn-secondary">
                <i class="bi bi-x-circle mr-2"></i>Clear
            </a>
        </div>
    </form>
</div>

<!-- Faculty Cards Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($faculty as $member)
        @php
            $totalDocuments = 0;
            $compliedDocuments = 0;
            $totalAssignments = $member->facultyAssignments->count();
            
            foreach($member->facultyAssignments as $assignment) {
                $totalDocuments += $assignment->complianceDocuments->count();
                $compliedDocuments += $assignment->complianceDocuments->where('status', 'Complied')->count();
            }
            
            $complianceRate = $totalDocuments > 0 ? round(($compliedDocuments / $totalDocuments) * 100, 1) : 0;
        @endphp
        
        <div class="faculty-card cursor-pointer" onclick="window.location.href='{{ route('monitor.vpaa-faculty-detail', [$program->id, $member->id]) }}'">
            <!-- Blue Header Section -->
            <div class="relative bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <!-- Faculty Avatar -->
                        <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4">
                            @if($member->avatar)
                                <img src="{{ $member->avatar }}" alt="{{ $member->name }}" class="w-14 h-14 rounded-full object-cover">
                            @else
                                <i class="bi bi-person-fill text-2xl"></i>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">{{ $member->name }}</h3>
                            <p class="text-blue-100 text-sm">{{ $member->email }}</p>
                            <p class="text-blue-100 text-xs">Full-time</p>
                        </div>
                    </div>
                    
                    <!-- Compliance Rate Badge -->
                    <div class="text-center">
                        <div class="text-2xl font-bold">{{ $complianceRate }}%</div>
                        <div class="text-xs text-blue-100">Compliance</div>
                    </div>
                </div>
            </div>
            
            <!-- White Body Section -->
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-800">{{ $totalAssignments }}</div>
                        <div class="text-sm text-gray-600">Assignments</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-800">{{ $totalDocuments }}</div>
                        <div class="text-sm text-gray-600">Documents</div>
                    </div>
                </div>
                
                <!-- Progress Bar -->
                <div class="mb-4">
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>Compliance Progress</span>
                        <span>{{ $compliedDocuments }}/{{ $totalDocuments }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-gradient-to-r from-green-400 to-green-600 h-2 rounded-full transition-all duration-300" 
                             style="width: {{ $complianceRate }}%"></div>
                    </div>
                </div>
                
                <!-- Status Badge -->
                <div class="flex justify-center">
                    @if($complianceRate >= 80)
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                            <i class="bi bi-check-circle mr-1"></i>Excellent
                        </span>
                    @elseif($complianceRate >= 60)
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-medium rounded-full">
                            <i class="bi bi-exclamation-triangle mr-1"></i>Good
                        </span>
                    @else
                        <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">
                            <i class="bi bi-x-circle mr-1"></i>Needs Attention
                        </span>
                    @endif
                </div>
            </div>
            
            <!-- Click indicator -->
            <div class="px-6 pb-4">
                <div class="text-center text-blue-600 text-sm font-medium">
                    <i class="bi bi-arrow-right mr-1"></i>Click to view details
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-12">
            <i class="bi bi-people text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Faculty Members Found</h3>
            <p class="text-gray-500">There are no faculty members in this program matching your criteria.</p>
        </div>
    @endforelse
</div>

<div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
    <div class="flex items-start">
        <i class="bi bi-info-circle text-blue-500 text-xl mr-3 mt-0.5"></i>
        <div>
            <h4 class="font-medium text-blue-900 mb-1">Compliance Overview</h4>
            <p class="text-blue-700 text-sm">
                Click on any faculty card to view detailed compliance information for {{ $program->name }} program. 
                The progress bars show the overall compliance rate for each faculty member.
            </p>
        </div>
    </div>
</div>
@endsection
