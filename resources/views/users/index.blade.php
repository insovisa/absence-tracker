<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Absence Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-6 max-w-4xl">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">User Management</h1>
                <p class="text-gray-600">Admin panel - Manage users</p>
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
        
        <!-- Stats -->
        <div class="bg-white rounded-xl shadow p-6 mb-6">
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $userCount }}</div>
                    <div class="text-gray-600">Total Users</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">{{ 5 - $userCount }}</div>
                    <div class="text-gray-600">Slots Available</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600">
                        {{ $users->where('is_admin', true)->count() }}
                    </div>
                    <div class="text-gray-600">Admins</div>
                </div>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Registered Users</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-6 text-left text-gray-700 font-semibold">ID</th>
                            <th class="py-3 px-6 text-left text-gray-700 font-semibold">Name</th>
                            <th class="py-3 px-6 text-left text-gray-700 font-semibold">Username</th>
                            <th class="py-3 px-6 text-left text-gray-700 font-semibold">Email</th>
                            <th class="py-3 px-6 text-left text-gray-700 font-semibold">Role</th>
                            <th class="py-3 px-6 text-left text-gray-700 font-semibold">Registered</th>
                            <th class="py-3 px-6 text-left text-gray-700 font-semibold">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($users as $user)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-6">{{ $user->id }}</td>
                            <td class="py-3 px-6 font-medium">{{ $user->name }}</td>
                            <td class="py-3 px-6">
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm">{{ $user->username }}</code>
                            </td>
                            <td class="py-3 px-6">{{ $user->email }}</td>
                            <td class="py-3 px-6">
                                @if($user->is_admin)
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">Admin</span>
                                @else
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">User</span>
                                @endif
                            </td>
                            <td class="py-3 px-6">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="py-3 px-6">
                                @if($user->id != session('user_id'))
                                <div class="flex gap-2">
                                    <button onclick="openResetPasswordModal({{ $user->id }}, '{{ $user->name }}')"
                                            class="text-blue-600 hover:text-blue-800 px-3 py-1 text-sm">
                                        <i class="fas fa-key mr-1"></i>Reset Password
                                    </button>
                                    
                                    <form action="/delete-user/{{ $user->id }}" method="POST" 
                                        onsubmit="return confirm('Delete user {{ $user->name }}? This will delete all their data.')">
                                        @csrf
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-800 px-3 py-1 text-sm">
                                            <i class="fas fa-trash-alt mr-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                                @else
                                <span class="text-gray-400">Current User</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Info Box -->
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl mt-1"></i>
                <div>
                    <h3 class="font-bold text-yellow-800">Important Notes</h3>
                    <ul class="text-yellow-700 mt-2 space-y-1">
                        <li>• Maximum 5 users allowed total</li>
                        <li>• First registered user becomes admin</li>
                        <li>• Deleting a user removes all their periods and absences</li>
                        <li>• Admin cannot delete themselves</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Current User Info -->
        <div class="bg-white rounded-xl shadow p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-700">
                        <span class="font-bold">{{ session('user_name') }}</span>
                        @if(session('is_admin'))
                        <span class="ml-2 bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Admin</span>
                        @endif
                    </p>
                    <p class="text-sm text-gray-500">Logged in as administrator</p>
                </div>
                <div class="flex gap-3">
                    <a href="/profile" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-user-circle mr-1"></i>My Profile
                    </a>
                    <a href="/dashboard" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-home mr-1"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Add this modal before the closing </body> tag (Reset Password) -->
    <div id="resetPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
        <div class="min-h-screen flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-800">Reset Password</h3>
                        <button onclick="closeResetPasswordModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <form id="resetPasswordForm" method="POST">
                        @csrf
                        <input type="hidden" name="user_id" id="resetUserId">
                        
                        <div class="mb-6">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <p class="text-blue-700 font-medium" id="resetUserName"></p>
                                <p class="text-blue-600 text-sm mt-1">Enter new password for this user</p>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">New Password</label>
                                    <input type="password" name="new_password" required 
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        placeholder="Enter new password"
                                        minlength="6">
                                </div>
                                
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                                    <input type="password" name="confirm_password" required 
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                        placeholder="Confirm new password">
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <div class="flex items-center">
                                    <input type="checkbox" id="notify_user" name="notify_user" value="1" 
                                        class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                    <label for="notify_user" class="ml-2 text-gray-700 text-sm">
                                        Send email notification to user (if email is configured)
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    If unchecked, you'll need to manually inform the user of their new password.
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex gap-3">
                            <button type="button" onclick="closeResetPasswordModal()"
                                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-4 rounded-lg transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold py-3 px-4 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all">
                                Reset Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
<script>
    let currentResetUserId = null;
    
    function openResetPasswordModal(userId, userName) {
        currentResetUserId = userId;
        document.getElementById('resetUserId').value = userId;
        document.getElementById('resetUserName').textContent = 'Resetting password for: ' + userName;
        document.getElementById('resetPasswordForm').action = '/reset-password/' + userId;
        document.getElementById('resetPasswordModal').classList.remove('hidden');
    }
    
    function closeResetPasswordModal() {
        document.getElementById('resetPasswordModal').classList.add('hidden');
        document.getElementById('resetPasswordForm').reset();
        currentResetUserId = null;
    }
    
    // Password confirmation validation
    document.getElementById('resetPasswordForm')?.addEventListener('submit', function(e) {
        const newPassword = document.querySelector('input[name="new_password"]').value;
        const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        if (newPassword.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters!');
            return false;
        }
    });
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeResetPasswordModal();
        }
    });
</script>
</html>