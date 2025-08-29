<div class="curriculum-details">
    <div class="mb-4">
        <h4 class="text-md font-semibold text-gray-800">{{ $curriculum->course->course_name }}</h4>
        <p class="text-sm text-gray-600">{{ $curriculum->name }}</p>
        <p class="text-xs text-gray-500 mt-1">Total Subjects: {{ $curriculum->subjects->count() }}</p>
    </div>

    @if($subjectsByYear->count() > 0)
        @foreach($subjectsByYear->sortKeys() as $year => $yearSubjects)
            <div class="mb-6">
                <h5 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b border-gray-200">
                    Year {{ $year }}
                </h5>
                
                @php
                    $semesterGroups = $yearSubjects->groupBy('semester');
                @endphp
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach(['1st', '2nd'] as $sem)
                        @if($semesterGroups->has($sem))
                            <div class="border border-gray-200 rounded-lg p-3">
                                <h6 class="text-xs font-medium text-gray-600 mb-2 bg-gray-50 px-2 py-1 rounded">
                                    {{ $sem }} Semester ({{ $semesterGroups[$sem]->count() }} subjects)
                                </h6>
                                <div class="space-y-2">
                                    @foreach($semesterGroups[$sem] as $subject)
                                        <div class="flex justify-between items-start text-xs">
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-800">{{ $subject->subject_code }}</div>
                                                <div class="text-gray-600 text-xs">{{ $subject->subject_title }}</div>
                                            </div>
                                            @if($subject->units)
                                                <span class="text-gray-500 text-xs ml-2">{{ $subject->units }} units</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    @else
        <div class="text-center py-8">
            <i class="bi bi-book text-gray-300 text-3xl mb-2"></i>
            <p class="text-gray-500">No subjects found in this curriculum</p>
        </div>
    @endif
</div>
