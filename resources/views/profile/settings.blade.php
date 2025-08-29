@extends('layouts.app')

@section('title', 'Profile Settings')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Profile Settings</h1>
    <p class="text-gray-600 mt-2">Update your personal information and account settings</p>
</div>

<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <i class="bi bi-check-circle text-green-500 mr-2"></i>
                    <span class="text-green-700">{{ session('status') }}</span>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf
            
            <!-- Personal Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" 
                               class="form-input @error('name') border-red-500 @enderror" required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" 
                               class="form-input @error('email') border-red-500 @enderror" required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Role Information (Read-only) -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Role Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <input type="text" value="{{ $user->role ? $user->role->name : 'No Role Assigned' }}" class="form-input bg-gray-50" readonly>
                    </div>
                    
                    @if($user->department)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <input type="text" value="{{ $user->department->name }}" class="form-input bg-gray-50" readonly>
                    </div>
                    @endif
                    
                    @if($user->program)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Program</label>
                        <input type="text" value="{{ $user->program->name }}" class="form-input bg-gray-50" readonly>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Password Change -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
                <p class="text-sm text-gray-600 mb-4">Leave blank if you don't want to change your password</p>
                
                <div class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                        <input type="password" id="current_password" name="current_password" 
                               class="form-input @error('current_password') border-red-500 @enderror">
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" id="new_password" name="new_password" 
                               class="form-input @error('new_password') border-red-500 @enderror">
                        @error('new_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" 
                               class="form-input @error('new_password_confirmation') border-red-500 @enderror">
                        @error('new_password_confirmation')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="btn-primary">
                    <i class="bi bi-check-circle mr-2"></i>Update Profile
                </button>
            </div>
        </form>
    </div>

    <!-- Account Information -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <i class="bi bi-info-circle text-blue-500 text-xl mr-3 mt-0.5"></i>
            <div>
                <h4 class="font-medium text-blue-900 mb-1">Account Information</h4>
                <p class="text-blue-700 text-sm">
                    Your role, department, and program information are managed by system administrators. 
                    Contact them if you need to make changes to these details.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
