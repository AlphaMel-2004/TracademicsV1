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
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
	<style>
		/* Fallback styles to ensure colors are applied */
		.sidebar-bg { background-color: #72c4d0 !important; }
		.header-bg { background-color: #359aca !important; }
		.page-bg { background-color: #dbffef !important; }
		.nav-link-white { color: white !important; }
		.nav-link-white:hover { background-color: #5bb3c0 !important; }
	</style>
</head>
<body class="page-bg min-h-screen">
	<div class="flex">
		<aside class="w-64 sidebar-bg min-h-screen shadow-lg">
			<div class="p-4 font-bold text-lg text-white border-b border-[#5bb3c0]">Tracademics</div>
			<nav class="px-2 py-4">
				<ul class="space-y-2">
					<li><a href="{{ route('dashboard') }}" class="nav-link nav-link-white hover:bg-[#5bb3c0] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('dashboard') ? 'bg-[#5bb3c0]' : '' }}"><i class="bi bi-speedometer2 mr-2"></i>Dashboard</a></li>
					
					@if(auth()->user()->hasRole('VPAA'))
						<li><a href="{{ route('monitor.departments') }}" class="nav-link nav-link-white hover:bg-[#5bb3c0] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('monitor.*') ? 'bg-[#5bb3c0]' : '' }}"><i class="bi bi-building mr-2"></i>Monitor</a></li>
						<li><a href="{{ route('reports.index') }}" class="nav-link nav-link-white hover:bg-[#5bb3c0] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('reports.*') ? 'bg-[#5bb3c0]' : '' }}"><i class="bi bi-graph-up mr-2"></i>Reports</a></li>
					@elseif(auth()->user()->hasRole('Dean'))
						<li><a href="{{ route('monitor.faculty') }}" class="nav-link nav-link-white hover:bg-[#5bb3c0] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('monitor.*') ? 'bg-[#5bb3c0]' : '' }}"><i class="bi bi-people mr-2"></i>Monitor Faculty</a></li>
						<li><a href="{{ route('reports.index') }}" class="nav-link nav-link-white hover:bg-[#5bb3c0] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('reports.*') ? 'bg-[#5bb3c0]' : '' }}"><i class="bi bi-file-earmark-pdf mr-2"></i>Reports</a></li>
					@elseif(auth()->user()->hasRole('Program Head'))
						<li><a href="{{ route('monitor.compliances') }}" class="nav-link nav-link-white hover:bg-[#5bb3c0] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('monitor.*') ? 'bg-[#5bb3c0]' : '' }}"><i class="bi bi-journal-check mr-2"></i>Monitor Compliances</a></li>
						<li><a href="{{ route('assignments.index') }}" class="nav-link nav-link-white hover:bg-[#5bb3c0] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('assignments.*') ? 'bg-[#5bb3c0]' : '' }}"><i class="bi bi-diagram-3 mr-2"></i>Assignments</a></li>
					@elseif(auth()->user()->hasRole('Faculty Member'))
						<li><a href="{{ route('subjects.index') }}" class="nav-link nav-link-white hover:bg-[#5bb3c0] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('subjects.*') ? 'bg-[#5bb3c0]' : '' }}"><i class="bi bi-book mr-2"></i>Subjects</a></li>
						<li><a href="{{ route('compliances.index') }}" class="nav-link nav-link-white hover:bg-[#5bb3c0] rounded-lg px-3 py-2 transition-colors {{ request()->routeIs('compliances.*') ? 'bg-[#5bb3c0]' : '' }}"><i class="bi bi-journal-check mr-2"></i>Compliance</a></li>
					@endif
				</ul>
			</nav>
		</aside>
		<main class="flex-1">
			<header class="flex items-center justify-between px-6 py-4 header-bg shadow-lg">
				<div class="flex items-center gap-2 text-white">
					<img src="{{ asset('favicon.ico') }}" class="h-6 w-6" alt="Logo">
					<span class="font-semibold">Tracademics</span>
				</div>
				<div class="relative">
					<div class="group inline-block">
						<button class="flex items-center gap-2 text-white hover:text-gray-200 transition-colors">
							<i class="bi bi-person-circle text-2xl"></i>
							<span class="hidden md:inline">{{ auth()->user()->name }}</span>
							<i class="bi bi-chevron-down text-sm"></i>
						</button>
						<div class="hidden group-hover:block absolute right-0 mt-2 w-48 bg-white border rounded-lg shadow-lg z-50">
							<a class="block px-4 py-2 hover:bg-gray-50 text-gray-700" href="{{ route('profile.settings') }}">Profile Settings</a>
							<button onclick="showLogoutModal()" class="w-full text-left px-4 py-2 hover:bg-gray-50 text-gray-700">Logout</button>
						</div>
					</div>
				</div>
			</header>
			<section class="p-6">
				@yield('content')
			</section>
			<footer class="text-center py-4 text-sm text-gray-600 bg-white border-t">Tracademics System v1.0 © 2025</footer>
		</main>
	</div>

	<!-- Logout Confirmation Modal -->
	<div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-20 backdrop-blur-sm hidden z-50">
		<div class="flex items-center justify-center min-h-screen p-4">
			<div class="bg-white rounded-lg p-6 max-w-sm w-full">
				<div class="text-center">
					<i class="bi bi-question-circle text-4xl text-blue-500 mb-4"></i>
					<h3 class="text-lg font-medium text-gray-900 mb-2">Confirm Logout</h3>
					<p class="text-gray-500 mb-6">Are you sure you want to logout?</p>
					<div class="flex gap-3 justify-center">
						<button onclick="hideLogoutModal()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
						<form method="POST" action="{{ route('logout') }}" class="inline">
							@csrf
							<button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">Logout</button>
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

		// Close dropdown when clicking outside
		document.addEventListener('click', function(e) {
			if (!e.target.closest('.group')) {
				const dropdowns = document.querySelectorAll('.group-hover\\:block');
				dropdowns.forEach(dropdown => {
					dropdown.classList.add('hidden');
				});
			}
		});
	</script>
</body>
</html>



