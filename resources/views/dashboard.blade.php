<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absence Tracker - Custom Period</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .period-badge {
            transition: all 0.2s ease;
        }
        .period-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .progress-ring {
            transform: rotate(-90deg);
        }
        @media (max-width: 640px) {
            .mobile-card {
                margin: 0 -1rem;
                border-radius: 0;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-gray-100 min-h-screen font-sans">
    <div class="container mx-auto px-4 py-6 max-w-6xl">
        <!-- Add this to dashboard.blade.php header section -->
        <div class="flex items-center gap-4">
            <div class="text-right">
                <div class="font-medium text-gray-700">{{ session('user_name') }}</div>
                <div class="text-xs text-gray-500">
                    @if(session('is_admin'))
                    <span class="text-green-600">Admin</span>
                    @else
                    User
                    @endif
                </div>
            </div>
            <div class="flex gap-2">
                <a href="/profile" 
                class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-4 py-2 rounded-lg text-sm transition-colors">
                    <i class="fas fa-user-circle mr-2"></i>Profile
                </a>
                <a href="/logout" 
                class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg text-sm transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>

        <!-- Header with Period Selector -->
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-8 mobile-card">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div class="flex-1">
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-800 flex items-center gap-3">
                        <i class="fas fa-calendar-alt text-blue-600"></i>
                        University Absence Tracker
                    </h1>
                    <p class="text-gray-600 mt-2">Track absences within custom periods</p>
                    
                    <!-- Tracking vs Viewing Indicator -->
                    @if($viewingPeriod->id != $trackingPeriod->id)
                    <div class="mt-3 flex items-center gap-2 text-sm">
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded">
                            <i class="fas fa-edit mr-1"></i>Tracking in: {{ $trackingPeriod->name }}
                        </span>
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">
                            <i class="fas fa-eye mr-1"></i>Viewing: {{ $viewingPeriod->name }}
                        </span>
                    </div>
                    @endif
                </div>
                
                <!-- Period Selector -->
                <div class="w-full lg:w-auto">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold text-blue-800 flex items-center gap-2">
                                <i class="fas fa-eye"></i>
                                Viewing Period
                            </h3>
                            <button type="button" onclick="openPeriodModal()" 
                                    class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg">
                                Change
                            </button>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-800">{{ $viewingPeriod->name }}</div>
                            <div class="text-sm text-gray-600 mt-1">
                                {{ \Carbon\Carbon::parse($viewingPeriod->start_date)->format('M j, Y') }} - {{ \Carbon\Carbon::parse($viewingPeriod->end_date)->format('M j, Y') }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $viewingPeriod->getDaysRemaining() }} days remaining in period
                            </div>
                            
                            <!-- Show tracking period if different -->
                            @if($viewingPeriod->id != $trackingPeriod->id)
                            <div class="mt-2 pt-2 border-t border-blue-200">
                                <div class="text-xs text-green-700 font-medium">
                                    <i class="fas fa-edit mr-1"></i>
                                    Tracking new absences in: {{ $trackingPeriod->name }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <!-- Period Status Warnings -->
                    @if($periodEnded)
                    <div class="mb-6">
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-exclamation-triangle text-red-600 text-xl mt-1"></i>
                                <div class="flex-1">
                                    <h3 class="font-bold text-red-800 text-lg">Viewing Past Period</h3>
                                    <p class="text-red-700 mt-1">
                                        You're viewing <strong>{{ $viewingPeriod->name }}</strong> which ended on 
                                        {{ \Carbon\Carbon::parse($viewingPeriod->end_date)->format('M j, Y') }}.
                                    </p>
                                    @if($trackingPeriod->id != $viewingPeriod->id)
                                    <p class="text-green-700 mt-2 font-medium">
                                        <i class="fas fa-edit mr-1"></i>
                                        You're tracking new absences in: {{ $trackingPeriod->name }}
                                        ({{ \Carbon\Carbon::parse($trackingPeriod->start_date)->format('M j') }} - {{ \Carbon\Carbon::parse($trackingPeriod->end_date)->format('M j, Y') }})
                                    </p>
                                    @endif
                                    <div class="mt-4 flex gap-3 flex-wrap">
                                        <button onclick="openPeriodModal()" 
                                                class="bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold py-2 px-4 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all">
                                            <i class="fas fa-exchange-alt mr-2"></i>Switch Period
                                        </button>
                                        <button onclick="openAddPeriodModal()" 
                                                class="bg-gradient-to-r from-green-600 to-emerald-700 text-white font-bold py-2 px-4 rounded-lg hover:from-green-700 hover:to-emerald-800 transition-all">
                                            <i class="fas fa-plus mr-2"></i>Create New Period
                                        </button>
                                        @if($allPeriods->count() > 1)
                                        <a href="/manage-periods" 
                                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg transition-colors">
                                            <i class="fas fa-cog mr-2"></i>Manage All Periods
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @elseif($periodNotStarted)
                    <div class="mb-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-calendar-plus text-blue-600 text-xl mt-1"></i>
                                <div class="flex-1">
                                    <h3 class="font-bold text-blue-800">Future Period</h3>
                                    <p class="text-blue-700 mt-1">
                                        You're viewing <strong>{{ $viewingPeriod->name }}</strong> which starts on 
                                        {{ \Carbon\Carbon::parse($viewingPeriod->start_date)->format('M j, Y') }}
                                        (in {{ \Carbon\Carbon::today()->diffInDays($viewingPeriod->start_date, false) }} days).
                                    </p>
                                    <p class="text-blue-700 mt-2">
                                        You can add absences in advance, or switch to a current period.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @elseif($periodEndsSoon)
                    <div class="mb-6">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-clock text-yellow-600 text-xl mt-1"></i>
                                <div class="flex-1">
                                    <h3 class="font-bold text-yellow-800">Period Ending Soon</h3>
                                    <p class="text-yellow-700 mt-1">
                                        Current period ends on {{ \Carbon\Carbon::parse($viewingPeriod->end_date)->format('M j, Y') }}
                                        (in {{ \Carbon\Carbon::today()->diffInDays($viewingPeriod->end_date, false) }} days).
                                    </p>
                                    <div class="mt-3">
                                        <button onclick="openAddPeriodModal()" 
                                                class="bg-gradient-to-r from-green-600 to-emerald-700 text-white font-bold py-2 px-4 rounded-lg hover:from-green-700 hover:to-emerald-800 transition-all">
                                            <i class="fas fa-plus mr-2"></i>Create Next Period
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @if($viewingPeriod->id != $trackingPeriod->id)
                <div class="mt-4">
                    <form action="/switch-period/{{ $trackingPeriod->id }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-green-600 to-emerald-700 text-white font-bold py-2 px-4 rounded-lg hover:from-green-700 hover:to-emerald-800 transition-all text-sm flex items-center justify-center gap-2">
                            <i class="fas fa-sync-alt"></i>
                            Return to Tracking Period ({{ $trackingPeriod->name }})
                        </button>
                    </form>
                </div>
                @endif
            </div>
            
            <!-- Quick Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                <div class="bg-blue-50 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-blue-700">{{ $periodTotalDays }}/{{ $viewingPeriod->max_days }}</div>
                    <div class="text-xs text-blue-600">Days Used</div>
                </div>
                <div class="bg-green-50 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-green-700">{{ $remainingDays }}</div>
                    <div class="text-xs text-green-600">Days Remaining</div>
                </div>
                <div class="bg-purple-50 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-purple-700">{{ $periodAbsences->count() }}</div>
                    <div class="text-xs text-purple-600">Absences</div>
                </div>
                <div class="bg-orange-50 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-orange-700">{{ $allTimeTotalDays }}</div>
                    <div class="text-xs text-orange-600">All-time Total</div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Progress & Breakdown -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl p-6 h-full">
                    <!-- Progress -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold text-gray-800">Progress in Current Period</h3>
                            <span class="text-2xl font-bold {{ $percentage > 80 ? 'text-red-600' : ($percentage > 50 ? 'text-orange-600' : 'text-green-600') }}">
                                {{ round($percentage) }}%
                            </span>
                        </div>
                        <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-green-500 via-yellow-500 to-red-500 rounded-full" 
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600 mt-2">
                            <span>0 days</span>
                            <span>{{ $viewingPeriod->max_days }} days limit</span>
                        </div>
                    </div>
                    
                    <!-- Authorized Breakdown -->
                    <div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Authorized Absence Breakdown</h3>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-blue-50 rounded-xl p-4 text-center period-badge">
                                <div class="text-3xl font-bold text-blue-700">{{ $authorizedBreakdown['paper'] }}</div>
                                <div class="text-blue-600 font-medium mt-2">Paper</div>
                                <div class="text-xs text-blue-500 mt-1">Written submission</div>
                            </div>
                            <div class="bg-green-50 rounded-xl p-4 text-center period-badge">
                                <div class="text-3xl font-bold text-green-700">{{ $authorizedBreakdown['counter'] }}</div>
                                <div class="text-green-600 font-medium mt-2">Counter</div>
                                <div class="text-xs text-green-500 mt-1">In-person inform</div>
                            </div>
                            <div class="bg-orange-50 rounded-xl p-4 text-center period-badge">
                                <div class="text-3xl font-bold text-orange-700">{{ $authorizedBreakdown['phone'] }}</div>
                                <div class="text-orange-600 font-medium mt-2">Phone</div>
                                <div class="text-xs text-orange-500 mt-1">Phone call inform</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Absence Form -->
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-plus-circle text-blue-600"></i>
                    Add New Absence
                </h3>
                <form action="/add-absence" method="POST">
                    @csrf
                    
                    <!-- Date -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" 
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <p class="text-xs text-gray-500 mt-1">
                            <span class="font-medium">Tracking period:</span> 
                            {{ \Carbon\Carbon::parse($trackingPeriod->start_date)->format('M j') }} - {{ \Carbon\Carbon::parse($trackingPeriod->end_date)->format('M j, Y') }}
                        </p>
                        @if($viewingPeriod->id != $trackingPeriod->id)
                        <p class="text-xs text-blue-600 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            You're viewing {{ $viewingPeriod->name }}, but absences will be added to {{ $trackingPeriod->name }}
                        </p>
                        @endif
                    </div>
                    
                    <!-- Absence Type -->
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Type</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="absence_type" value="authorized" class="hidden peer" checked>
                                <div class="p-3 border-2 border-gray-200 rounded-lg text-center peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                    <div class="font-semibold text-blue-700">Authorized</div>
                                    <div class="text-xs text-gray-600">0.5 day</div>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="absence_type" value="unauthorized" class="hidden peer">
                                <div class="p-3 border-2 border-gray-200 rounded-lg text-center peer-checked:border-red-500 peer-checked:bg-red-50">
                                    <div class="font-semibold text-red-700">Unauthorized</div>
                                    <div class="text-xs text-gray-600">1.0 day</div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Authorized Type -->
                    <div id="authorizedTypes" class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Authorized Method</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="authorized_type" value="paper" class="hidden peer">
                                <div class="p-2 border-2 border-gray-200 rounded-lg text-center peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                    <i class="fas fa-file-alt text-blue-600 mb-1"></i>
                                    <div class="text-xs font-medium">Paper</div>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="authorized_type" value="counter" class="hidden peer">
                                <div class="p-2 border-2 border-gray-200 rounded-lg text-center peer-checked:border-green-500 peer-checked:bg-green-50">
                                    <i class="fas fa-user-check text-green-600 mb-1"></i>
                                    <div class="text-xs font-medium">Counter</div>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="authorized_type" value="phone" class="hidden peer" checked>
                                <div class="p-2 border-2 border-gray-200 rounded-lg text-center peer-checked:border-orange-500 peer-checked:bg-orange-50">
                                    <i class="fas fa-phone text-orange-600 mb-1"></i>
                                    <div class="text-xs font-medium">Phone</div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Reason -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Reason (Optional)</label>
                        <textarea name="reason" rows="2" 
                                  class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="e.g., Sick, Family emergency..."></textarea>
                    </div>

                    <!-- Submit -->
                    @if($periodEnded)
                    <div class="mb-6">
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                            <i class="fas fa-ban text-red-500 text-xl mb-2"></i>
                            <p class="text-red-700 font-bold">Cannot Add to Past Period</p>
                            <p class="text-red-600 text-sm">
                                This period ended on {{ \Carbon\Carbon::parse($viewingPeriod->end_date)->format('M j, Y') }}.
                            </p>
                            <div class="mt-3">
                                <button type="button" onclick="openAddPeriodModal()"
                                        class="bg-gradient-to-r from-green-600 to-emerald-700 text-white font-bold py-2 px-4 rounded-lg hover:from-green-700 hover:to-emerald-800 transition-all text-sm">
                                    <i class="fas fa-plus mr-2"></i>Create New Period
                                </button>
                            </div>
                        </div>
                    </div>
                    @elseif($periodNotStarted)
                    <div class="mb-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                            <i class="fas fa-calendar-plus text-blue-500 text-xl mb-2"></i>
                            <p class="text-blue-700 font-bold">Future Period</p>
                            <p class="text-blue-600 text-sm">
                                This period starts on {{ \Carbon\Carbon::parse($viewingPeriod->start_date)->format('M j, Y') }}.
                                You can add absences in advance.
                            </p>
                        </div>
                    </div>
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold py-3 px-4 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i>
                        Record Absence (Future)
                    </button>
                    @else
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold py-3 px-4 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i>
                        Record Absence
                    </button>
                    @endif
                </form>
                
                <!-- Quick Actions -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="flex gap-3">
                        <a href="/manage-periods" 
                           class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg text-center transition-colors">
                            <i class="fas fa-cog mr-2"></i>Manage Periods
                        </a>
                        <button onclick="openAddPeriodModal()" 
                                class="flex-1 bg-green-100 hover:bg-green-200 text-green-800 font-medium py-2 px-4 rounded-lg transition-colors">
                            <i class="fas fa-plus mr-2"></i>New Period
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Absence History -->
        <div class="bg-white rounded-2xl shadow-xl p-6 mobile-card mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-history text-blue-600"></i>
                    Absence History (Current Period)
                </h3>
                <div class="flex gap-2">
                    <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                        {{ $periodAbsences->count() }} records
                    </span>
                    <span class="bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded-full">
                        Total: {{ number_format($periodTotalDays, 1) }} days
                    </span>
                </div>
            </div>
            
            @if($periodAbsences->isEmpty())
                <div class="text-center py-12">
                    <i class="fas fa-calendar-check text-5xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">No absences in this period</p>
                    <p class="text-gray-400 mt-2">Add your first absence above</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-full">
                        <thead>
                            <tr class="border-b-2 border-gray-100">
                                <th class="text-left py-3 px-4 text-gray-700 font-semibold">Date</th>
                                <th class="text-left py-3 px-4 text-gray-700 font-semibold">Type</th>
                                <th class="text-left py-3 px-4 text-gray-700 font-semibold">Method</th>
                                <th class="text-left py-3 px-4 text-gray-700 font-semibold">Day Count</th>
                                <th class="text-left py-3 px-4 text-gray-700 font-semibold">Reason</th>
                                <th class="text-left py-3 px-4 text-gray-700 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($periodAbsences as $absence)
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="font-medium">{{ \Carbon\Carbon::parse($absence->date)->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($absence->date)->format('l') }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    @if($absence->absence_type === 'authorized')
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                        Authorized
                                    </span>
                                    @else
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                                        Unauthorized
                                    </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($absence->authorized_type)
                                    <span class="text-gray-700 font-medium">{{ $absence->authorized_type_label }}</span>
                                    @else
                                    <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-bold {{ $absence->absence_type === 'authorized' ? 'text-blue-600' : 'text-red-600' }}">
                                    {{ $absence->day_count }} day{{ $absence->day_count != 1 ? 's' : '' }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="text-gray-600 max-w-xs">{{ $absence->reason ?? '—' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <form action="/delete-absence/{{ $absence->id }}" method="POST" onsubmit="return confirm('Delete this absence?')">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-800 transition-colors p-2">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="text-center text-gray-500 text-sm">
            <p>Personal University Absence Tracker • 
                <span class="text-blue-600">Active: {{ $viewingPeriod->name }}</span> • 
                <span class="text-gray-600">{{ now()->format('F j, Y') }}</span>
            </p>
            <p class="mt-1">Custom period tracking • All device compatible • No login required</p>
        </div>
    </div>

    <!-- Period Selection Modal -->
    <div id="periodModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
        <div class="min-h-screen flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-800">Select Period</h3>
                        <button onclick="closePeriodModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($allPeriods as $period)
                        <div class="border border-gray-200 rounded-xl p-4 hover:border-blue-300 transition-colors {{ $period->is_active ? 'bg-blue-50 border-blue-300' : '' }}">
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="font-bold text-gray-800">{{ $period->name }}</div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        {{ \Carbon\Carbon::parse($period->start_date)->format('M j, Y') }} - {{ \Carbon\Carbon::parse($period->end_date)->format('M j, Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $period->max_days }} days limit • 
                                        @if($period->is_active)
                                        <span class="text-green-600 font-bold">✓ Active</span>
                                        @endif
                                    </div>
                                </div>
                                @if(!$period->is_active)
                                <form action="/switch-period/{{ $period->id }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                                        Switch to This
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <button onclick="openAddPeriodModal()" 
                                class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold py-3 px-4 rounded-lg hover:from-green-600 hover:to-emerald-700 transition-all">
                            <i class="fas fa-plus mr-2"></i>Create New Period
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Period Modal -->
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

    <!-- JavaScript -->
    <script>
        // Modal Functions
        function openPeriodModal() {
            document.getElementById('periodModal').classList.remove('hidden');
        }
        
        function closePeriodModal() {
            document.getElementById('periodModal').classList.add('hidden');
        }
        
        function openAddPeriodModal() {
            closePeriodModal();
            document.getElementById('addPeriodModal').classList.remove('hidden');
        }
        
        function closeAddPeriodModal() {
            document.getElementById('addPeriodModal').classList.add('hidden');
        }
        
        // Toggle authorized types
        document.querySelectorAll('input[name="absence_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const authorizedTypes = document.getElementById('authorizedTypes');
                if (this.value === 'authorized') {
                    authorizedTypes.style.display = 'block';
                } else {
                    authorizedTypes.style.display = 'none';
                }
            });
        });
        
        // Set default date to today
        document.querySelector('input[name="date"]').valueAsDate = new Date();
        
        // Initialize date pickers for period dates
        flatpickr('input[name="start_date"]', {
            dateFormat: 'Y-m-d',
            defaultDate: new Date()
        });
        
        flatpickr('input[name="end_date"]', {
            dateFormat: 'Y-m-d',
            defaultDate: new Date(Date.now() + 90 * 24 * 60 * 60 * 1000) // 90 days from now
        });
        
        // Close modals on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePeriodModal();
                closeAddPeriodModal();
            }
        });
        
        // Success message auto-remove
        @if(session('success'))
        setTimeout(() => {
            const toast = document.querySelector('[id^="successToast"]');
            if (toast) toast.remove();
        }, 3000);
        @endif

        // When date changes in the form, check which period it belongs to
        document.querySelector('input[name="date"]').addEventListener('change', function(e) {
            const selectedDate = e.target.value;
            
            // Make AJAX call to find appropriate period
            fetch('/find-period-for-date?date=' + selectedDate)
                .then(response => response.json())
                .then(data => {
                    if (data.period) {
                        // Show message about which period this will be added to
                        const messageDiv = document.getElementById('periodMessage');
                        if (!messageDiv) {
                            const newDiv = document.createElement('div');
                            newDiv.id = 'periodMessage';
                            newDiv.className = 'mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg';
                            newDiv.innerHTML = `
                                <p class="text-blue-700 text-sm">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    This absence will be added to <strong>${data.period.name}</strong>
                                    (${data.period.start_date} to ${data.period.end_date})
                                </p>
                            `;
                            e.target.parentNode.appendChild(newDiv);
                        }
                    }
                });
        });
    </script>
    
    <!-- Success Toast -->
    @if(session('success'))
    <div id="successToast" class="fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-xl flex items-center gap-3 z-50 animate-slide-up">
        <i class="fas fa-check-circle text-xl"></i>
        <div>
            <p class="font-bold">Success!</p>
            <p>{{ session('success') }}</p>
        </div>
    </div>
    @endif
</body>
</html>