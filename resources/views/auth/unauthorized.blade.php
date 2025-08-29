<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access - Tracademics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <div class="mx-auto h-24 w-24 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="bi bi-shield-x text-red-600 text-4xl"></i>
                </div>
                <h1 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Access Denied
                </h1>
                <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-800">
                        {{ $message ?? 'You do not have permission to access this resource.' }}
                    </p>
                </div>
            </div>

            <div class="mt-8 space-y-4">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">What can you do?</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <i class="bi bi-check-circle text-green-500 mr-2"></i>
                            Contact your administrator if you believe this is an error
                        </li>
                        <li class="flex items-center">
                            <i class="bi bi-check-circle text-green-500 mr-2"></i>
                            Check if you're logged in with the correct account
                        </li>
                        <li class="flex items-center">
                            <i class="bi bi-check-circle text-green-500 mr-2"></i>
                            Verify your role permissions
                        </li>
                    </ul>
                </div>

                <div class="flex flex-col space-y-3">
                    @auth
                        <a href="{{ route('dashboard') }}" 
                           class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                            <i class="bi bi-house mr-2"></i>
                            Go to Dashboard
                        </a>
                        
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Logged in as: <span class="font-medium">{{ Auth::user()->name }}</span></p>
                            <p class="text-xs text-gray-500">Role: {{ Auth::user()->role->name ?? 'No role assigned' }}</p>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" 
                                    class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                <i class="bi bi-box-arrow-right mr-2"></i>
                                Logout and Login with Different Account
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" 
                           class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                            <i class="bi bi-box-arrow-in-right mr-2"></i>
                            Login
                        </a>
                    @endauth
                </div>
            </div>

            <div class="text-center">
                <p class="text-xs text-gray-500">
                    Tracademics &copy; {{ date('Y') }} - Brokenshire College
                </p>
            </div>
        </div>
    </div>
</body>
</html>


