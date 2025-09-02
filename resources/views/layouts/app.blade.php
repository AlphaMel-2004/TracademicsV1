<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="@yield('meta_description','Tracademics streamlines academic compliance monitoring and reporting across departments and programs.')">
	<meta name="robots" content="@yield('meta_robots','index,follow')">
	<link rel="canonical" href="@yield('canonical', url()->current())">

	<meta property="og:type" content="website">
	<meta property="og:title" content="@yield('meta_title', 'Tracademics')">
	<meta property="og:description" content="@yield('meta_description','Tracademics streamlines academic compliance monitoring and reporting across departments and programs.')">
	<meta property="og:url" content="@yield('canonical', url()->current())">
	<meta property="og:site_name" content="Tracademics">
	<meta property="og:image" content="@yield('meta_image', asset('images/logo.png'))">

	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="@yield('meta_title', 'Tracademics')">
	<meta name="twitter:description" content="@yield('meta_description','Tracademics streamlines academic compliance monitoring and reporting across departments and programs.')">
	<meta name="twitter:image" content="@yield('meta_image', asset('images/logo.png'))">

	@yield('structured_data')
	@yield('extra_meta')
	<title>@yield('title', 'Tracademics')</title>
	@vite(['resources/css/app.css','resources/js/app.js'])
	<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
	<link rel="stylesheet" href="{{ asset('css/enhanced-ui.css') }}">
	<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<style>
		/* Fallback styles to ensure colors are applied */
		.sidebar-bg { background-color: #008080 !important; }
		.header-bg { background-color: #448899 !important; }
		.page-bg { background-color: #dbffef !important; }
		.nav-link-white { color: white !important; }
		.nav-link-white:hover { background-color: #006666 !important; }
	</style>
</head>
<body class="page-bg min-h-screen">
	<div class="flex">
		<aside class="w-64 sidebar-bg min-h-screen shadow-lg">
			<div class="p-4 font-bold text-lg text-white border-b border-[#006666] flex items-center gap-2">
				<img src="{{ asset('favicon.ico') }}" class="h-6 w-6" alt="Logo">
				<span>Tracademics</span>
			</div>
			<nav class="px-2 py-4">
				<ul class="space-y-2">
					<li><a href="{{ route('dashboard') }}" class="nav-link nav-link-white hover:bg-[#006666] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('dashboard') ? 'bg-[#006666]' : '' }}"><i class="bi bi-speedometer2 mr-2"></i>Dashboard</a></li>
					
					@if(auth()->user()->role && auth()->user()->role->name === 'MIS')
						<li><a href="{{ route('mis.departments') }}" class="nav-link nav-link-white hover:bg-[#006666] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('mis.departments*') ? 'bg-[#006666]' : '' }}"><i class="bi bi-building mr-2"></i>Departments</a></li>
						<li><a href="{{ route('mis.programs') }}" class="nav-link nav-link-white hover:bg-[#006666] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('mis.programs*') ? 'bg-[#006666]' : '' }}"><i class="bi bi-collection mr-2"></i>Programs</a></li>
						<li><a href="{{ route('mis.users') }}" class="nav-link nav-link-white hover:bg-[#006666] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('mis.users*') ? 'bg-[#006666]' : '' }}"><i class="bi bi-people mr-2"></i>Users</a></li>
						<li><a href="{{ route('mis.semesters') }}" class="nav-link nav-link-white hover:bg-[#006666] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('mis.semesters*') ? 'bg-[#006666]' : '' }}"><i class="bi bi-calendar3 mr-2"></i>Semesters</a></li>
						<li><a href="{{ route('mis.user-logs') }}" class="nav-link nav-link-white hover:bg-[#006666] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('mis.user-logs*') ? 'bg-[#006666]' : '' }}"><i class="bi bi-journal-text mr-2"></i>User Logs</a></li>
						<li><a href="{{ route('mis.curriculum') }}" class="nav-link nav-link-white hover:bg-[#006666] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('mis.curriculum*') ? 'bg-[#006666]' : '' }}"><i class="bi bi-book mr-2"></i>Curriculum of Subjects</a></li>
					@elseif(auth()->user()->role && auth()->user()->role->name === 'VPAA')
						<li><a href="{{ route('monitor.departments') }}" class="nav-link nav-link-white hover:bg-[#006666] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('monitor.*') ? 'bg-[#006666]' : '' }}"><i class="bi bi-building mr-2"></i>Departments</a></li>
						<li><a href="{{ route('reports.index') }}" class="nav-link nav-link-white hover:bg-[#006666] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('reports.*') ? 'bg-[#006666]' : '' }}"><i class="bi bi-graph-up mr-2"></i>Reports</a></li>
					@elseif(auth()->user()->role && auth()->user()->role->name === 'Dean')
						<li><a href="{{ route('monitor.faculty') }}" class="nav-link nav-link-white hover:bg-[#006666] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('monitor.*') ? 'bg-[#006666]' : '' }}"><i class="bi bi-people mr-2"></i>Monitor Faculty</a></li>
						<li><a href="{{ route('reports.index') }}" class="nav-link nav-link-white hover:bg-[#006666] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('reports.*') ? 'bg-[#006666]' : '' }}"><i class="bi bi-file-earmark-pdf mr-2"></i>Reports</a></li>
					@elseif(auth()->user()->role && auth()->user()->role->name === 'Program Head')
						<li><a href="{{ route('monitor.compliances') }}" class="nav-link nav-link-white hover:bg-[#006666] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('monitor.*') ? 'bg-[#006666]' : '' }}"><i class="bi bi-journal-check mr-2"></i>Monitor Compliance</a></li>
						<li><a href="{{ route('assignments.index') }}" class="nav-link nav-link-white hover:bg-[#006666] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('assignments.*') ? 'bg-[#006666]' : '' }}"><i class="bi bi-diagram-3 mr-2"></i>Faculty Load</a></li>
					@elseif(auth()->user()->role && auth()->user()->role->name === 'Faculty Member')
						<li><a href="{{ route('compliances.index') }}" class="nav-link nav-link-white hover:bg-[#006666] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('compliances.*') ? 'bg-[#006666]' : '' }}"><i class="bi bi-journal-check mr-2"></i>Compliance</a></li>
					@endif
					
					@if(auth()->user()->role && auth()->user()->role->name !== 'MIS' && auth()->user()->current_semester_id)
						<li class="mt-4 pt-2 border-t border-[#006666]">
							<div class="px-3 py-2 text-white text-sm opacity-75">
								<i class="bi bi-calendar mr-2"></i>Current Semester
							</div>
							<div class="px-3 py-1 text-white text-xs">
								{{ auth()->user()->currentSemester->name ?? '' }} {{ auth()->user()->currentSemester->year ?? '' }}
							</div>
							<a href="{{ route('semester.change') }}" class="text-white text-xs px-3 hover:underline">Change Semester</a>
						</li>
					@endif
				</ul>
			</nav>
		</aside>
		<main class="flex-1">
			<header class="flex items-center justify-between px-6 py-4 header-bg shadow-lg">
				<div class="flex items-center gap-2 text-white">
					<span class="font-semibold"></span>
				</div>
				<div class="flex items-center gap-4">
					<!-- Notification Bell -->
					<div class="relative">
						<button class="notification-bell text-white hover:text-gray-200 transition-colors p-2">
							<i class="bi bi-bell text-xl"></i>
							<!-- Notification badge (optional) -->
							<span class="notification-badge absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"></span>
						</button>
					</div>

					<!-- User Profile Dropdown -->
					<div class="relative">
						<div class="group inline-block">
							<button class="profile-dropdown-btn flex items-center gap-3 text-white hover:text-gray-200 transition-colors">
								<!-- User Avatar with Initials -->
								<div class="user-avatar w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-sm">
									{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', auth()->user()->name)[1] ?? '', 0, 1)) }}
								</div>
							</button>
							<div class="profile-dropdown-menu hidden absolute right-0 mt-2 w-48 bg-white border rounded-lg shadow-lg z-50">
								<a class="block px-4 py-2 hover:bg-gray-50 text-gray-700" href="{{ route('profile.settings') }}">
									<i class="bi bi-person mr-2"></i>Profile Settings
								</a>
								<button onclick="showLogoutModal()" class="w-full text-left px-4 py-2 hover:bg-gray-50 text-gray-700">
									<i class="bi bi-box-arrow-right mr-2"></i>Logout
								</button>
							</div>
						</div>
					</div>
				</div>
			</header>
			<section class="p-6">
				<!-- Flash Messages -->
				@if(session('success'))
					<div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
						<div class="flex items-center">
							<i class="bi bi-check-circle text-green-500 mr-3"></i>
							<span class="text-green-700">{{ session('success') }}</span>
						</div>
					</div>
				@endif

				@if(session('error'))
					<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
						<div class="flex items-center">
							<i class="bi bi-exclamation-circle text-red-500 mr-3"></i>
							<span class="text-red-700">{{ session('error') }}</span>
						</div>
					</div>
				@endif

				@if(session('warning'))
					<div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
						<div class="flex items-center">
							<i class="bi bi-exclamation-triangle text-yellow-500 mr-3"></i>
							<span class="text-yellow-700">{{ session('warning') }}</span>
						</div>
					</div>
				@endif

				@if(session('info'))
					<div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
						<div class="flex items-center">
							<i class="bi bi-info-circle text-blue-500 mr-3"></i>
							<span class="text-blue-700">{{ session('info') }}</span>
						</div>
					</div>
				@endif

				@yield('content')
			</section>
			<footer class="text-center py-4 text-sm text-gray-600 bg-white border-t">Tracademics System v1.0 © 2025</footer>
		</main>
	</div>

	<!-- Logout Confirmation Modal -->
	<div id="logoutModal" class="fixed inset-0 modal-backdrop hidden z-50">
		<div class="flex items-center justify-center min-h-screen p-4">
			<div class="bg-white rounded-lg p-6 max-w-sm w-full shadow-xl">
				<div class="text-center">
					<div class="inline-flex justify-center items-center w-16 h-16 rounded-full bg-blue-100 mb-5">
						<svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
							<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-14h2v6h-2zm0 8h2v2h-2z"></path>
						</svg>
					</div>
					<h3 class="text-xl font-medium text-gray-900 mb-2">Confirm Logout</h3>
					<p class="text-gray-600 mb-6">Are you sure you want to logout?</p>
					<div class="flex gap-4 justify-center">
						<button onclick="hideLogoutModal()" class="px-6 py-2 text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">Cancel</button>
						<form method="POST" action="{{ route('logout') }}" class="inline">
							@csrf
							<button type="submit" class="px-6 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">Logout</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
		function showLogoutModal() {
			document.getElementById('logoutModal').classList.remove('hidden');
		}

		function hideLogoutModal() {
			document.getElementById('logoutModal').classList.add('hidden');
		}

		// Close modal when clicking outside
		document.getElementById('logoutModal').addEventListener('click', function(e) {
			if (e.target === this) {
				hideLogoutModal();
			}
		});

		// Profile dropdown functionality
		document.addEventListener('DOMContentLoaded', function() {
			const dropdownBtn = document.querySelector('.profile-dropdown-btn');
			const dropdownMenu = document.querySelector('.profile-dropdown-menu');
			const notificationBell = document.querySelector('.notification-bell');

			// Profile dropdown
			if (dropdownBtn && dropdownMenu) {
				dropdownBtn.addEventListener('click', function(e) {
					e.stopPropagation();
					dropdownMenu.classList.toggle('hidden');
				});

				// Close dropdown when clicking outside
				document.addEventListener('click', function(e) {
					if (!e.target.closest('.group')) {
						dropdownMenu.classList.add('hidden');
					}
				});
			}

			// Notification bell functionality (placeholder for future implementation)
			if (notificationBell) {
				notificationBell.addEventListener('click', function(e) {
					e.stopPropagation();
					// TODO: Add notification dropdown or redirect to notifications page
					console.log('Notification bell clicked');
				});
			}
		});
	</script>
</body>
</html>



