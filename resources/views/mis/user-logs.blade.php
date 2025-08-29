@extends('layouts.app')

@section('title', 'User Activity Logs')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">User Activity Logs</h1>
            <p class="text-gray-600 mt-1">Monitor all system user activities and actions</p>
        </div>
        <div class="flex gap-3">
            <button onclick="toggleFilters()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="bi bi-funnel mr-2"></i>Filters
            </button>
            <button onclick="exportLogs()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="bi bi-download mr-2"></i>Export
            </button>
        </div>
    </div>

    <!-- Filter Panel -->
    <div id="filterPanel" class="bg-white rounded-xl shadow-lg p-6 hidden">
        <form method="GET" action="{{ route('mis.user-logs') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">User</label>
                <select id="user_id" name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="action" class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                <select id="action" name="action" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Actions</option>
                    <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                    <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Logout</option>
                    <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Create</option>
                    <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Update</option>
                    <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Delete</option>
                </select>
            </div>
            
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="md:col-span-4 flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                    <i class="bi bi-search mr-2"></i>Apply Filters
                </button>
                <a href="{{ route('mis.user-logs') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg transition-colors">
                    <i class="bi bi-arrow-clockwise mr-2"></i>Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="bi bi-activity text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $logs->total() }}</h3>
                    <p class="text-gray-600">Total Activities</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="bi bi-box-arrow-in-right text-green-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $logs->where('action', 'login')->count() }}</h3>
                    <p class="text-gray-600">Logins Today</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="bi bi-plus-circle text-purple-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $logs->where('action', 'create')->count() }}</h3>
                    <p class="text-gray-600">Records Created</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 bg-orange-100 rounded-full">
                    <i class="bi bi-pencil text-orange-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $logs->where('action', 'update')->count() }}</h3>
                    <p class="text-gray-600">Records Updated</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Logs Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Activity Timeline</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="p-2 bg-blue-100 rounded-full mr-3">
                                        <i class="bi bi-person text-blue-600"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $log->user->name ?? 'Unknown User' }}</div>
                                        <div class="text-sm text-gray-500">{{ $log->user->email ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($log->action == 'login') bg-green-100 text-green-800
                                    @elseif($log->action == 'logout') bg-red-100 text-red-800
                                    @elseif($log->action == 'create') bg-blue-100 text-blue-800
                                    @elseif($log->action == 'update') bg-yellow-100 text-yellow-800
                                    @elseif($log->action == 'delete') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    <i class="bi 
                                        @if($log->action == 'login') bi-box-arrow-in-right
                                        @elseif($log->action == 'logout') bi-box-arrow-right
                                        @elseif($log->action == 'create') bi-plus-circle
                                        @elseif($log->action == 'update') bi-pencil
                                        @elseif($log->action == 'delete') bi-trash
                                        @else bi-activity
                                        @endif mr-1"></i>
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs truncate">{{ $log->description }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="font-mono">{{ $log->ip_address ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>{{ $log->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $log->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($log->metadata)
                                    <button onclick="showDetails('{{ addslashes(json_encode($log->metadata)) }}')" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                @else
                                    <span class="text-gray-400">No details</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="bi bi-activity text-4xl mb-4"></i>
                                    <p class="text-lg">No activity logs found</p>
                                    <p class="text-sm">User activities will appear here</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-xl bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 text-center mb-4">Activity Details</h3>
            <pre id="detailsContent" class="bg-gray-100 p-4 rounded-lg text-sm overflow-auto max-h-96"></pre>
            <div class="flex justify-center mt-4">
                <button onclick="hideDetails()" 
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-6 rounded-lg transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFilters() {
    const panel = document.getElementById('filterPanel');
    panel.classList.toggle('hidden');
}

function exportLogs() {
    // For now, just show an alert. You can implement CSV/Excel export later
    alert('Export functionality will be implemented soon.');
}

function showDetails(metadata) {
    try {
        const data = JSON.parse(metadata);
        document.getElementById('detailsContent').textContent = JSON.stringify(data, null, 2);
        document.getElementById('detailsModal').classList.remove('hidden');
    } catch (e) {
        alert('Error parsing activity details');
    }
}

function hideDetails() {
    document.getElementById('detailsModal').classList.add('hidden');
}
</script>
@endsection
