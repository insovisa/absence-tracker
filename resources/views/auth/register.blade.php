<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Absence Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-6">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <i class="fas fa-user-plus text-blue-600 text-5xl mb-4"></i>
                <h1 class="text-3xl font-bold text-gray-800">Register New User</h1>
                <p class="text-gray-600 mt-2">Join Absence Tracker</p>
            </div>
            
            @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                {{ session('error') }}
            </div>
            @endif
            
            @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
            @endif
            
            <form action="/register" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Full Name</label>
                    <input type="text" name="name" required 
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="John Doe">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                    <input type="text" name="username" required 
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="johndoe">
                    <p class="text-xs text-gray-500 mt-1">Used for login (letters and numbers only)</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" name="email" required 
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="your@email.com">
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" name="password" required 
                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="••••••••">
                    <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
                </div>
                
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-green-600 to-emerald-700 text-white font-bold py-3 px-4 rounded-lg hover:from-green-700 hover:to-emerald-800 transition-all">
                    <i class="fas fa-user-plus mr-2"></i>Register
                </button>
            </form>
            
            <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                <a href="/" 
                   class="text-blue-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Login
                </a>
            </div>
            
            <div class="mt-6 text-center text-sm text-gray-500">
                @php
                    $userCount = \App\Models\User::count();
                @endphp
                <p>{{ 5 - $userCount }} user slots remaining</p>
            </div>
        </div>
    </div>
</body>
</html>