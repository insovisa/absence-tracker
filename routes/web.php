<?php

use Illuminate\Support\Facades\Route;
use App\Models\Absence;
use App\Models\Period;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

// Home redirects to login
Route::get('/', function () {
    return redirect('/login');
});

// Login Page
Route::get('/login', function () {
    return view('auth.login');
});

// Login Action
Route::post('/login', function () {
    $credentials = request()->validate([
        'username' => 'required|string',  // Accepts both username and email
        'password' => 'required'
    ]);
    
    // Try to find user by username OR email
    $user = User::where('username', $credentials['username'])
                ->orWhere('email', $credentials['username'])
                ->first();
    
    if (!$user || !Hash::check($credentials['password'], $user->password)) {
        return redirect('/login')->with('error', 'Invalid username/email or password');
    }
    
    // Manual login
    session(['user_id' => $user->id]);
    session(['user_name' => $user->name]);
    session(['is_admin' => $user->is_admin]);
    
    return redirect('/dashboard');
});

// Register Page
Route::get('/register', function () {
    $userCount = User::count();
    
    if ($userCount >= 5) {
        return redirect('/login')->with('error', 'Maximum 5 users reached');
    }
    
    return view('auth.register');
});

// Register Action
Route::post('/register', function () {
    $userCount = User::count();
    
    if ($userCount >= 5) {
        return redirect('/register')->with('error', 'Maximum 5 users reached. Cannot register new user.');
    }
    
    $validated = request()->validate([
        'name' => 'required|string|max:100',
        'username' => 'required|string|max:50|unique:users|regex:/^[a-zA-Z0-9_]+$/',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6'
    ]);
    
    // First user becomes admin
    $isAdmin = ($userCount === 0);
    
    $user = User::create([
        'name' => $validated['name'],
        'username' => $validated['username'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'is_admin' => $isAdmin
    ]);
    
    return redirect('/login')->with('success', 'Registration successful! Please login with your username.');
});

// Logout
Route::get('/logout', function () {
    session()->flush();
    return redirect('/login');
});

// Middleware to check if user is logged in
function checkAuth() {
    if (!session('user_id')) {
        // Debug: Log why session is empty
        \Log::info('Authe failed: user_id=' . session('user_id') . ', session_id=' . session()->getId());

        return redirect('/login')->with('error', 'Please login first');
    }
    return null;
}

// Dashboard (Protected)
Route::get('/dashboard', function () {
    $authCheck = checkAuth();
    if ($authCheck) return $authCheck;
    
    $userId = session('user_id');
    
    // Determine which period to track new absences in (ACTIVE/TRACKING period)
    $trackingPeriod = Period::autoSwitchPeriod();
    
    // Check if user has any periods at all
    $userPeriodsCount = Period::where('user_id', $userId)->count();
    
    if ($userPeriodsCount === 0) {
        // No periods exist for this user - redirect to setup
        return redirect('/setup-period');
    }
    
    // If autoSwitchPeriod returned null, get the first period for this user
    if (!$trackingPeriod) {
        $trackingPeriod = Period::where('user_id', $userId)
            ->orderBy('start_date', 'desc')
            ->first();
    }
    
    // Determine which period to VIEW (from session or tracking period)
    $viewingPeriodId = session('viewing_period_id');
    
    if ($viewingPeriodId) {
        $viewingPeriod = Period::where('user_id', $userId)->find($viewingPeriodId);
        if (!$viewingPeriod) {
            $viewingPeriod = $trackingPeriod;
            session()->forget('viewing_period_id');
        }
    } else {
        $viewingPeriod = $trackingPeriod;
    }
    
    $today = Carbon::today();
    
    // Status flags for VIEWING period
    $periodEnded = $today->gt($viewingPeriod->end_date);
    $periodEndsSoon = $today->between($viewingPeriod->start_date, $viewingPeriod->end_date) && 
                      $today->diffInDays($viewingPeriod->end_date, false) <= 7;
    $periodNotStarted = $today->lt($viewingPeriod->start_date);
    
    // Get all absences for VIEWING period for this user
    $periodAbsences = Absence::where('user_id', $userId)
        ->whereBetween('date', [
            $viewingPeriod->start_date, 
            $viewingPeriod->end_date
        ])->orderBy('date', 'desc')->get();
    
    // Calculate totals for VIEWING period
    $periodTotalDays = $periodAbsences->sum(function($absence){
        return $absence->day_count; // Use the accessor
    });
    $remainingDays = max(0, $viewingPeriod->max_days - $periodTotalDays);
    $percentage = $viewingPeriod->max_days > 0 ? min(100, ($periodTotalDays / $viewingPeriod->max_days) * 100) : 0;
    
    // All-time totals for this user
    $allAbsences = Absence::where('user_id', $userId)->orderBy('date', 'desc')->get();
    $allTimeTotalDays = $allAbsences->sum(function($absence){
        return $absence->day_count; // Use the accessor
    });
    
    // Breakdown by authorized type within VIEWING period
    $authorizedBreakdown = [
        'paper' => $periodAbsences->where('authorized_type', 'paper')->count(),
        'counter' => $periodAbsences->where('authorized_type', 'counter')->count(),
        'phone' => $periodAbsences->where('authorized_type', 'phone')->count(),
    ];
    
    // Get all periods for this user for dropdown
    $allPeriods = Period::where('user_id', $userId)->orderBy('start_date', 'desc')->get();
    
    return view('dashboard', compact(
        'allAbsences',
        'trackingPeriod',
        'viewingPeriod',
        'periodAbsences',
        'periodTotalDays',
        'remainingDays',
        'percentage',
        'allTimeTotalDays',
        'authorizedBreakdown',
        'allPeriods',
        'periodEnded',
        'periodEndsSoon',
        'periodNotStarted'
    ));
});

// Setup Period (First-time use for user)
Route::get('/setup-period', function () {
    $authCheck = checkAuth();
    if ($authCheck) return $authCheck;
    
    return view('setup-period');
});

Route::post('/save-period', function () {
    $authCheck = checkAuth();
    if ($authCheck) return $authCheck;
    
    $userId = session('user_id');
    
    $validated = request()->validate([
        'name' => 'required|string|max:100',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'max_days' => 'required|integer|min:1|max:100'
    ]);
    
    // Deactivate all other periods for this user
    Period::where('user_id', $userId)->update(['is_active' => false]);
    
    // Create new active period for this user
    $newPeriod = Period::create(array_merge($validated, [
        'is_active' => true,
        'user_id' => $userId
    ]));
    
    // Set as viewing period
    session(['viewing_period_id' => $newPeriod->id]);
    
    return redirect('/dashboard')->with('success', 'Period set successfully!');
});

// Switch to VIEW a different period
Route::post('/switch-period/{id}', function ($id) {
    $authCheck = checkAuth();
    if ($authCheck) return $authCheck;
    
    $userId = session('user_id');
    
    // Verify the period belongs to this user
    $period = Period::where('id', $id)->where('user_id', $userId)->first();
    
    if (!$period) {
        return redirect('/dashboard')->with('error', 'Period not found');
    }
    
    // Just store in session which period to VIEW
    session(['viewing_period_id' => $id]);
    
    return redirect('/dashboard')->with('success', 'Now viewing period!');
});

// Add New Period
Route::post('/add-period', function () {
    $authCheck = checkAuth();
    if ($authCheck) return $authCheck;
    
    $userId = session('user_id');
    
    $validated = request()->validate([
        'name' => 'required|string|max:100',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'max_days' => 'required|integer|min:1|max:100',
        'set_active' => 'nullable|boolean'
    ]);
    
    $setActive = request('set_active', false);
    
    if ($setActive) {
        Period::where('user_id', $userId)->update(['is_active' => false]);
    }
    
    $newPeriod = Period::create(array_merge($validated, [
        'is_active' => $setActive,
        'user_id' => $userId
    ]));
    
    // If set as active, also set as viewing period
    if ($setActive) {
        session(['viewing_period_id' => $newPeriod->id]);
    }
    
    return redirect('/dashboard')->with('success', 'Period added successfully!');
});

// Add Absence - always use TRACKING period for validation
Route::post('/add-absence', function () {
    $authCheck = checkAuth();
    if ($authCheck) return $authCheck;
    
    $userId = session('user_id');
    $trackingPeriod = Period::autoSwitchPeriod();
    
    if (!$trackingPeriod) {
        // If no tracking period, get the first period for this user
        $trackingPeriod = Period::where('user_id', $userId)
            ->orderBy('start_date', 'desc')
            ->first();
            
        if (!$trackingPeriod) {
            return redirect('/setup-period')->with('error', 'Please set up a period first.');
        }
    }
    
    $today = Carbon::today();
    
    // Check if TRACKING period has ended
    if ($today->gt($trackingPeriod->end_date)) {
        return redirect('/dashboard')->with('error', 'Cannot add absence: Current tracking period has ended. Please create a new period.');
    }
    
    $validated = request()->validate([
        'date' => 'required|date',
        'absence_type' => 'required|in:authorized,unauthorized',
        'authorized_type' => 'required_if:absence_type,authorized|in:paper,counter,phone',
        'reason' => 'nullable|string|max:500'
    ]);
    
    // Check if absence date is within TRACKING period
    $absenceDate = Carbon::parse($validated['date']);
    if (!$trackingPeriod->containsDate($absenceDate)) {
        return redirect('/dashboard')->with('error', 'Cannot add absence: Date is outside current tracking period (' . 
            \Carbon\Carbon::parse($trackingPeriod->start_date)->format('M j') . ' - ' . 
            \Carbon\Carbon::parse($trackingPeriod->end_date)->format('M j, Y') . ')');
    }
    
    if ($validated['absence_type'] === 'unauthorized') {
        $validated['authorized_type'] = null;
    }
    
    // Add user_id to absence
    $validated['user_id'] = $userId;
    
    Absence::create($validated);
    
    return redirect('/dashboard')->with('success', 'Absence recorded successfully!');
});

// Delete Absence
Route::post('/delete-absence/{id}', function ($id) {
    $authCheck = checkAuth();
    if ($authCheck) return $authCheck;
    
    $userId = session('user_id');
    
    // Verify the absence belongs to this user
    $absence = Absence::where('id', $id)->where('user_id', $userId)->first();
    
    if (!$absence) {
        return redirect('/dashboard')->with('error', 'Absence not found');
    }
    
    $absence->delete();
    
    return redirect('/dashboard')->with('success', 'Absence deleted!');
});

// Manage Periods Page
Route::get('/manage-periods', function () {
    $authCheck = checkAuth();
    if ($authCheck) return $authCheck;
    
    $userId = session('user_id');
    $periods = Period::where('user_id', $userId)->orderBy('start_date', 'desc')->get();
    
    return view('manage-periods', compact('periods'));
});

// Delete Period
Route::post('/delete-period/{id}', function ($id) {
    $authCheck = checkAuth();
    if ($authCheck) return $authCheck;
    
    $userId = session('user_id');
    
    // Verify the period belongs to this user
    $period = Period::where('id', $id)->where('user_id', $userId)->first();
    
    if (!$period) {
        return redirect('/manage-periods')->with('error', 'Period not found');
    }
    
    // Don't delete if it's the only period for this user
    if (Period::where('user_id', $userId)->count() <= 1) {
        return redirect('/manage-periods')->with('error', 'Cannot delete your only period!');
    }
    
    // If deleting active period, activate another one for this user
    if ($period->is_active) {
        Period::where('user_id', $userId)
              ->where('id', '!=', $id)
              ->first()
              ->update(['is_active' => true]);
    }
    
    // If deleting the period we're viewing, clear viewing session
    if (session('viewing_period_id') == $id) {
        session()->forget('viewing_period_id');
    }
    
    $period->delete();
    
    return redirect('/manage-periods')->with('success', 'Period deleted!');
});

// Admin: User Management (only for admin)
Route::get('/users', function () {
    $authCheck = checkAuth();
    if ($authCheck) return $authCheck;
    
    if (!session('is_admin')) {
        return redirect('/dashboard')->with('error', 'Admin access required');
    }
    
    $users = User::all();
    $userCount = User::count();
    
    return view('users.index', compact('users', 'userCount'));
});

// Admin: Delete User
Route::post('/delete-user/{id}', function ($id) {
    $authCheck = checkAuth();
    if ($authCheck) return $authCheck;
    
    if (!session('is_admin')) {
        return redirect('/dashboard')->with('error', 'Admin access required');
    }
    
    $currentUserId = session('user_id');
    
    // Cannot delete yourself
    if ($id == $currentUserId) {
        return redirect('/users')->with('error', 'Cannot delete yourself');
    }
    
    $user = User::find($id);
    
    if (!$user) {
        return redirect('/users')->with('error', 'User not found');
    }
    
    // Delete user's periods and absences first
    Period::where('user_id', $id)->delete();
    Absence::where('user_id', $id)->delete();
    
    // Then delete user
    $user->delete();
    
    return redirect('/users')->with('success', 'User deleted successfully');
});

// Admin: Reset User Password
Route::post('/reset-password/{id}', function ($id) {
    $authCheck = checkAuth();
    if ($authCheck) return $authCheck;
    
    if (!session('is_admin')) {
        return redirect('/dashboard')->with('error', 'Admin access required');
    }
    
    $currentUserId = session('user_id');
    
    // Cannot reset your own password here (users should use profile page)
    if ($id == $currentUserId) {
        return redirect('/users')->with('error', 'Please use profile page to change your own password');
    }
    
    $validated = request()->validate([
        'new_password' => 'required|min:6',
        'confirm_password' => 'required|same:new_password',
        'notify_user' => 'nullable|boolean'
    ]);
    
    $user = User::find($id);
    
    if (!$user) {
        return redirect('/users')->with('error', 'User not found');
    }
    
    // Update password
    $user->password = Hash::make($validated['new_password']);
    $user->save();
    
    // Log the password reset (optional)
    \Log::info('Password reset for user: ' . $user->email . ' by admin: ' . session('user_name'));
    
    $notifyUser = request('notify_user', false);
    
    if ($notifyUser) {
        // Here you could add email notification logic
        // For now, just log it
        \Log::info('Password reset notification requested for: ' . $user->email);
    }
    
    return redirect('/users')->with('success', 'Password reset successfully for ' . $user->name);
});

// User Profile Page
Route::get('/profile', function () {
    $authCheck = checkAuth();
    if ($authCheck) return $authCheck;
    
    $userId = session('user_id');
    $user = User::find($userId);
    
    // Calculate stats properly
    $periodsCount = Period::where('user_id', $userId)->count();
    $absencesCount = Absence::where('user_id', $userId)->count();
    $totalDays = Absence::where('user_id', $userId)->get()->sum(function($absence) {
        return $absence->day_count;
    });
    
    return view('profile', compact('user', 'periodsCount', 'absencesCount', 'totalDays'));
});

// Update Profile
Route::post('/update-profile', function () {
    $authCheck = checkAuth();
    if ($authCheck) return $authCheck;
    
    $userId = session('user_id');
    $user = User::find($userId);
    
    $validated = request()->validate([
        'name' => 'required|string|max:100',
        'username' => 'required|string|max:50|unique:users,username,' . $userId . '|regex:/^[a-zA-Z0-9_]+$/',
        'email' => 'required|email|unique:users,email,' . $userId,
        'current_password' => 'nullable',
        'new_password' => 'nullable|min:6',
        'confirm_password' => 'nullable|same:new_password'
    ]);
    
    // Check current password if trying to change password
    if (request('new_password')) {
        if (!Hash::check(request('current_password'), $user->password)) {
            return redirect('/profile')->with('error', 'Current password is incorrect');
        }
        
        $user->password = Hash::make($validated['new_password']);
    }
    
    $user->name = $validated['name'];
    $user->username = $validated['username'];
    $user->email = $validated['email'];
    $user->save();
    
    // Update session name
    session(['user_name' => $user->name]);
    
    return redirect('/profile')->with('success', 'Profile updated successfully');
});