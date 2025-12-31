<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Periods - Absence Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-6 max-w-4xl">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Manage Periods</h1>
                <p class="text-gray-600">Manage your absence tracking periods</p>
            </div>
            <div class="flex gap-3">
                <a href="/dashboard" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
                <a href="/logout" 
                   class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
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
                    <p class="text-sm text-gray-500">{{ session('user_id') ? 'User ID: ' . session('user_id') : '' }}</p>
                </div>
                <a href="/users" class="text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-users mr-1"></i>Manage Users
                </a>
            </div>
        </div>
        
        <!-- Periods List -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">Your Periods</h2>
                <button onclick="openAddPeriodModal()" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-plus mr-2"></i>New Period
                </button>
            </div>
            
            @if($periods->isEmpty())
            <div class="text-center py-12">
                <i class="fas fa-calendar-plus text-5xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">No periods created yet</p>
                <p class="text-gray-400 mt-2">Create your first period to start tracking</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-6 text-left text-gray-700 font-semibold">Name</th>
                            <th class="py-3 px-6 text-left text-gray-700 font-semibold">Date Range</th>
                            <th class="py-3 px-6 text-left text-gray-700 font-semibold">Days Limit</th>
                            <th class="py-3 px-6 text-left text-gray-700 font-semibold">Status</th>
                            <th class="py-3 px-6 text-left text-gray-700 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($periods as $period)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-6 font-medium">{{ $period->name }}</td>
                            <td class="py-3 px-6">
                                {{ \Carbon\Carbon::parse($period->start_date)->format('M j, Y') }} - 
                                {{ \Carbon\Carbon::parse($period->end_date)->format('M j, Y') }}
                            </td>
                            <td class="py-3 px-6">{{ $period->max_days }} days</td>
                            <td class="py-3 px-6">
                                @if($period->is_active)
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">Active</span>
                                @else
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3 px-6">
                                <div class="flex gap-2">
                                    @if(!$period->is_active)
                                    <form action="/switch-period/{{ $period->id }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                class="text-blue-600 hover:text-blue-800 px-3 py-1 text-sm">
                                            <i class="fas fa-eye mr-1"></i>View
                                        </button>
                                    </form>
                                    @endif
                                    
                                    <form action="/delete-period/{{ $period->id }}" method="POST" 
                                          onsubmit="return confirm('Delete period {{ $period->name }}?')">
                                        @csrf
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-800 px-3 py-1 text-sm">
                                            <i class="fas fa-trash-alt mr-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        
        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-blue-600 text-xl mt-1"></i>
                <div>
                    <h3 class="font-bold text-blue-800">About Periods</h3>
                    <ul class="text-blue-700 mt-2 space-y-1">
                        <li>• Only one period can be active at a time</li>
                        <li>• Deleting a period also deletes its absences</li>
                        <li>• You can view any period even if it's not active</li>
                        <li>• System auto-switches to current periods</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Period Modal (same as dashboard) -->
    <div id="addPeriodModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
        <div class="min-h-screen flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-800">Create New Period</h3>
                        <button onclick="closeAddPeriodModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <form action="/add-period" method="POST">
                        @csrf
                        
                        <div class="space-y-4">
                            <!-- Period Name -->
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Period Name</label>
                                <input type="text" name="name" required 
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="e.g., Semester 1 2024, Summer Term">
                            </div>
                            
                            <!-- Dates -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Start Date</label>
                                    <input type="date" name="start_date" required 
                                           class="w-full p-3 border border-gray-300 rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">End Date</label>
                                    <input type="date" name="end_date" required 
                                           class="w-full p-3 border border-gray-300 rounded-lg">
                                </div>
                            </div>
                            
                            <!-- Days Limit -->
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Absence Limit (Days)</label>
                                <input type="number" name="max_days" value="19" min="1" max="100" required
                                       class="w-full p-3 border border-gray-300 rounded-lg">
                                <p class="text-xs text-gray-500 mt-1">University standard is 19 days per 3 months</p>
                            </div>
                            
                            <!-- Set Active -->
                            <div class="flex items-center">
                                <input type="checkbox" name="set_active" value="1" id="set_active" checked
                                       class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                <label for="set_active" class="ml-2 text-gray-700">
                                    Set as active period immediately
                                </label>
                            </div>
                        </div>
                        
                        <div class="mt-8 flex gap-3">
                            <button type="button" onclick="closeAddPeriodModal()"
                                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-4 rounded-lg transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold py-3 px-4 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all">
                                Create Period
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function openAddPeriodModal() {
            document.getElementById('addPeriodModal').classList.remove('hidden');
        }
        
        function closeAddPeriodModal() {
            document.getElementById('addPeriodModal').classList.add('hidden');
        }
        
        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddPeriodModal();
            }
        });
    </script>
</body>
</html>