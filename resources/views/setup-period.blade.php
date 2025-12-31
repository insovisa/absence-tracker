<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Your First Period</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
        <div class="p-8">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-calendar-plus text-4xl text-blue-600"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Setup Your First Period</h1>
                <p class="text-gray-600 mt-2">Define your 3-month tracking period</p>
            </div>
            
            <form action="/save-period" method="POST">
                @csrf
                
                <div class="space-y-6">
                    <!-- Period Name -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Period Name</label>
                        <input type="text" name="name" required 
                               class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                               placeholder="e.g., Semester 1 2024">
                        <p class="text-sm text-gray-500 mt-1">Give your period a recognizable name</p>
                    </div>
                    
                    <!-- Dates -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Start Date</label>
                            <input type="date" name="start_date" required 
                                   class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-blue-500"
                                   value="{{ date('Y-m-d') }}">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">End Date</label>
                            <input type="date" name="end_date" required 
                                   class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-blue-500"
                                   value="{{ date('Y-m-d', strtotime('+90 days')) }}">
                            <p class="text-xs text-gray-500 mt-1">Typically 90 days (3 months)</p>
                        </div>
                    </div>
                    
                    <!-- Days Limit -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Absence Limit (Days)</label>
                        <div class="relative">
                            <input type="number" name="max_days" value="19" min="1" max="100" required
                                   class="w-full p-4 border-2 border-gray-200 rounded-xl focus:border-blue-500 pr-12">
                            <div class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-500">
                                days
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">University standard is 19 days absence limit per 3 months</p>
                    </div>
                    
                    <!-- Info Box -->
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-blue-600 text-xl mt-1"></i>
                            <div>
                                <p class="font-bold text-blue-800">How this works:</p>
                                <p class="text-blue-700 text-sm mt-1">
                                    • System will track absences only within this date range<br>
                                    • Authorized absences count as 0.5 days<br>
                                    • Unauthorized absences count as 1.0 day<br>
                                    • You can create multiple periods later
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8">
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold py-4 px-6 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all text-lg">
                        <i class="fas fa-rocket mr-2"></i>
                        Start Tracking!
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Set reasonable default dates
        document.querySelector('input[name="start_date"]').valueAsDate = new Date();
        
        // Set end date to 90 days from now
        const endDate = new Date();
        endDate.setDate(endDate.getDate() + 90);
        document.querySelector('input[name="end_date"]').valueAsDate = endDate;
    </script>
</body>
</html>