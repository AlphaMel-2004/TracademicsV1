<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>@yield('title', 'Tracademics')</title>
	@vite(['resources/css/app.css','resources/js/app.js'])
	<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-[#DDF5E6] min-h-screen">
	<div class="flex">
		<aside class="w-64 bg-white min-h-screen shadow">
			<div class="p-4 font-semibold text-lg">Tracademics</div>
			<nav class="px-2">
				<ul class="space-y-1">
					<li><a href="{{ route('dashboard') }}" class="nav-link"><i class="bi bi-speedometer2 mr-2"></i>Dashboard</a></li>
					@if(optional(auth()->user()->role)->name === 'VPAA')
						<li><a href="{{ route('reports.index') }}" class="nav-link"><i class="bi bi-graph-up mr-2"></i>Reports</a></li>
						<li><a href="{{ route('program.monitoring') }}" class="nav-link"><i class="bi bi-graph-up mr-2"></i>Program Monitoring</a></li>
					@endif
					@if(optional(auth()->user()->role)->name === 'Dean' || optional(auth()->user()->role)->name === 'VPAA')
						<li><a href="{{ route('reports.index') }}" class="nav-link"><i class="bi bi-people mr-2"></i>Department Monitoring</a></li>
						<li><a href="{{ route('program.monitoring') }}" class="nav-link"><i class="bi bi-graph-up mr-2"></i>Program Monitoring</a></li>
					@endif
					@if(optional(auth()->user()->role)->name === 'Program Head')
						<li><a href="{{ route('assignments.index') }}" class="nav-link"><i class="bi bi-diagram-3 mr-2"></i>Assignments</a></li>
						<li><a href="{{ route('program.monitoring') }}" class="nav-link"><i class="bi bi-graph-up mr-2"></i>Program Monitoring</a></li>
					@endif
					@if(optional(auth()->user()->role)->name === 'Faculty Member')
						<li><a href="{{ route('compliances.index') }}" class="nav-link"><i class="bi bi-journal-check mr-2"></i>My Compliance</a></li>
					@endif
				</ul>
			</nav>
		</aside>
		<main class="flex-1">
			<header class="flex items-center justify-between px-6 py-4 bg-white shadow">
				<div class="flex items-center gap-2">
					<img src="{{ asset('favicon.ico') }}" class="h-6 w-6" alt="Logo">
					<span class="font-semibold">Tracademics</span>
				</div>
				<div class="relative" id="profileMenuContainer">
					<button id="profileMenuButton" class="flex items-center gap-2">
						<i class="bi bi-person-circle text-2xl"></i>
					</button>
					<div id="profileMenuDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white border rounded shadow">
						<a class="block px-4 py-2 hover:bg-gray-50" href="#">Profile Settings</a>
						<form method="POST" action="{{ route('logout') }}">
							@csrf
							<button class="w-full text-left px-4 py-2 hover:bg-gray-50" type="submit">Logout</button>
						</form>
					</div>
				</div>
			</header>
			<section class="p-6">
				@yield('content')
			</section>
			<footer class="text-center py-4 text-sm text-gray-600">Tracademics System v1.0 © 2025</footer>
		</main>
	</div>
</body>
</html>
<script>
document.addEventListener('DOMContentLoaded', function(){
	const container = document.getElementById('profileMenuContainer');
	const button = document.getElementById('profileMenuButton');
	const dropdown = document.getElementById('profileMenuDropdown');
	if(button && dropdown && container){
		button.addEventListener('click', function(e){
			e.stopPropagation();
			dropdown.classList.toggle('hidden');
		});
		document.addEventListener('click', function(e){
			if(!container.contains(e.target)){
				dropdown.classList.add('hidden');
			}
		});
		document.addEventListener('keydown', function(e){
			if(e.key === 'Escape') dropdown.classList.add('hidden');
		});
	}
});
</script>
</html>


