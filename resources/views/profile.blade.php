<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Absence Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-6 max-w-2xl">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">My Profile</h1>
                <p class="text-gray-600">Update your account information</p>
            </div>
            <div class="flex gap-3">
                <a href="/dashboard" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-arrow-left mr-2"></i>Dashboard
                </a>
                <a href="/logout" 
                   class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>
        
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif
        
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
        @endif
        
        <!-- Profile Form -->
        <div class="bg-white rounded-xl shadow p-6">
            <form action="/update-profile" method="POST">
                @csrf
                
                <div class="space-y-6">
                    <!-- In the Basic Information section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Full Name</label>
                            <input type="text" name="name" value="{{ $user->name }}" required 
                                class="w-full p-3 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                            <input type="text" name="username" value="{{ $user->username }}" required 
                                class="w-full p-3 border border-gray-300 rounded-lg"
                                pattern="[a-zA-Z0-9_]+"
                                title="Only letters, numbers, and underscores allowed">
                            <p class="text-xs text-gray-500 mt-1">Used for login</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                            <input type="email" name="email" value="{{ $user->email }}" required 
                                class="w-full p-3 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                    
                    <!-- Role Info -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-user-shield text-blue-600 text-xl"></i>
                            <div>
                                <p class="font-bold text-blue-800">
                                    @if($user->is_admin)
                                    Administrator Account
                                    @else
                                    Standard User Account
                                    @endif
                                </p>
                                <p class="text-blue-600 text-sm mt-1">
                                    Registered: {{ $user->created_at->format('F j, Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Password Change -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Change Password</h3>
                        <p class="text-gray-600 text-sm mb-4">Leave blank to keep current password</p>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Current Password</label>
                                <input type="password" name="current_password" 
                                       class="w-full p-3 border border-gray-300 rounded-lg"
                                       placeholder="Enter current password to change">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">New Password</label>
                                    <input type="password" name="new_password" 
                                           class="w-full p-3 border border-gray-300 rounded-lg"
                                           placeholder="Min. 6 characters">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                                    <input type="password" name="confirm_password" 
                                           class="w-full p-3 border border-gray-300 rounded-lg"
                                           placeholder="Confirm new password">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit -->
                    <div class="pt-4 border-t border-gray-200">
                        <button type="submit"
                                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold py-3 px-4 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all">
                            <i class="fas fa-save mr-2"></i>Update Profile
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Quick Stats -->
        <div class="mt-6 grid grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">
                    {{ $periodsCount }}
                </div>
                <div class="text-gray-600 text-sm">Periods</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-2xl font-bold text-green-600">
                    {{ $absencesCount }}
                </div>
                <div class="text-gray-600 text-sm">Absences</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <div class="text-2xl font-bold text-purple-600">
                    {{ number_format($totalDays, 1) }}
                </div>
                <div class="text-gray-600 text-sm">Total Days</div>
            </div>
        </div>
        
        <!-- Danger Zone -->
        @if(!$user->is_admin)
        <div class="mt-6 bg-red-50 border border-red-200 rounded-xl p-6">
            <h3 class="text-lg font-bold text-red-800 mb-3">Account Management</h3>
            <p class="text-red-700 mb-4">As a regular user, you cannot delete your account. Please contact an administrator.</p>
            
            <div class="flex gap-3">
                @if(session('is_admin'))
                <a href="/users" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-users mr-2"></i>Manage Users
                </a>
                @endif
            </div>
        </div>
        @endif
    </div>
</body>
</html>