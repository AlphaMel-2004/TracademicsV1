@extends('layouts.app')

@section('title', 'Monitor Compliances - Program Head')

@section('content')
<div class="monitor-compliances">
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Monitor Compliances</h1>
    <p class="text-gray-600 mt-2">Click on a faculty member to view their compliance details</p>
</div>

<!-- Search Filter -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Search Faculty</h3>
    <form method="GET" action="{{ route('monitor.compliances') }}" class="flex items-center gap-4">
        <div class="flex-1">
            <label for="faculty_name" class="block text-sm font-medium text-gray-700 mb-2">Faculty Name</label>
            <input type="text" id="faculty_name" name="faculty_name" value="{{ request('faculty_name') }}" 
                   class="form-input" placeholder="Search by faculty name">
        </div>
        
        <div class="flex items-end gap-2">
            <button type="submit" class="btn-primary">
                <i class="bi bi-search mr-2"></i>Search
            </button>
            <a href="{{ route('monitor.compliances') }}" class="btn-secondary">
                <i class="bi bi-x-circle mr-2"></i>Clear
            </a>
        </div>
    </form>
</div>

<!-- Faculty Cards Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($faculty as $member)
    <div class="faculty-card bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 cursor-pointer" 
         onclick="window.location.href='{{ route('monitor.faculty-detail', $member->id) }}'">
        
        <!-- Card Header with Faculty Info -->
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
                        @if($member->faculty_type)
                            <p class="text-blue-100 text-xs">{{ $member->faculty_type }}</p>
                        @endif
                    </div>
                </div>
                
                <!-- Compliance Rate Badge -->
                <div class="text-center">
                    <div class="text-2xl font-bold">{{ $member->compliance_rate }}%</div>
                    <div class="text-xs text-blue-100">Compliance</div>
                </div>
            </div>
        </div>
        
        <!-- Card Body with Stats -->
        <div class="p-6">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-800">{{ $member->total_assignments }}</div>
                    <div class="text-sm text-gray-600">Assignments</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-800">{{ $member->total_documents }}</div>
                    <div class="text-sm text-gray-600">Documents</div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="mb-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Compliance Progress</span>
                    <span>{{ $member->complied_documents }}/{{ $member->total_documents }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-green-400 to-green-600 h-2 rounded-full transition-all duration-300" 
                         style="width: {{ $member->compliance_rate }}%"></div>
                </div>
            </div>
            
            <!-- Status Badge -->
            <div class="flex justify-center">
                @if($member->compliance_rate >= 80)
                    <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                        <i class="bi bi-check-circle mr-1"></i>Excellent
                    </span>
                @elseif($member->compliance_rate >= 60)
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
    <div class="col-span-full text-center py-16">
        <i class="bi bi-people text-6xl text-gray-300 mb-4 block"></i>
        <h3 class="text-xl font-medium text-gray-900 mb-2">No Faculty Members Found</h3>
        <p class="text-gray-600">
            @if(request('faculty_name'))
                No faculty members match your search criteria.
            @else
                No faculty members found in your program.
            @endif
        </p>
    </div>
    @endforelse
</div>

<!-- Summary Section -->
@if($faculty->count() > 0)
<div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
    <div class="flex items-start">
        <i class="bi bi-info-circle text-blue-500 text-xl mr-3 mt-0.5"></i>
        <div>
            <h4 class="font-medium text-blue-900 mb-1">Faculty Overview</h4>
            <p class="text-blue-700 text-sm">
                Showing {{ $faculty->count() }} faculty member{{ $faculty->count() !== 1 ? 's' : '' }}. 
                Click on any card to view detailed compliance information, filter by subjects, and access submitted documents.
            </p>
        </div>
    </div>
</div>
@endif

</div>
@endsection