<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="@yield('meta_description','Secure access to Tracademics, the academic compliance and monitoring system.')">
	<meta name="robots" content="@yield('meta_robots','noindex,nofollow')">
	<link rel="canonical" href="@yield('canonical', url()->current())">

	<meta property="og:type" content="website">
	<meta property="og:title" content="@yield('meta_title', 'Tracademics Login')">
	<meta property="og:description" content="@yield('meta_description','Secure access to Tracademics, the academic compliance and monitoring system.')">
	<meta property="og:url" content="@yield('canonical', url()->current())">
	<meta property="og:site_name" content="Tracademics">
	<meta property="og:image" content="@yield('meta_image', asset('images/logo.png'))">

	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="@yield('meta_title', 'Tracademics Login')">
	<meta name="twitter:description" content="@yield('meta_description','Secure access to Tracademics, the academic compliance and monitoring system.')">
	<meta name="twitter:image" content="@yield('meta_image', asset('images/logo.png'))">

	@yield('structured_data')
	@yield('extra_meta')
	<title>@yield('title','Tracademics')</title>
	@vite(['resources/css/app.css','resources/js/app.js'])
	<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
	<link rel="stylesheet" href="{{ asset('css/login-bg.css') }}">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
 </head>
 <body class="min-h-screen login-background">
 	@yield('content')
 </body>
</html>


