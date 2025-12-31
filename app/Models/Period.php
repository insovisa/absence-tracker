<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Period extends Model
{
    protected $fillable = ['name', 'start_date', 'end_date', 'max_days', 'is_active', 'user_id'];
    
    // Add casting for dates
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
    ];

        // Add user relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Get current active period FOR CURRENT USER
    // public static function getActivePeriod()
    // {
    //     $userId = auth()->id();
    //     return self::where('is_active', true)
    //                ->where('user_id', $userId)
    //                ->first();
    // }
    public static function getActivePeriod()
    {
        $userId = auth()->id();
        
        // Check if user_id column exists
        $table = (new self())->getTable();
        $columns = \Schema::getColumnListing($table);
        
        if (in_array('user_id', $columns)) {
            return self::where('is_active', true)
                    ->where('user_id', $userId)
                    ->first();
        } else {
            // Fallback for before migration
            return self::where('is_active', true)->first();
        }
    }
    
    // Auto-switch for CURRENT USER
    public static function autoSwitchPeriod()
    {
        $userId = auth()->id();
        $activePeriod = self::getActivePeriod();
        $today = Carbon::today();
        
        if (!$activePeriod) {
            $bestPeriod = self::findBestPeriodForDate($today, $userId);
            if ($bestPeriod) {
                $bestPeriod->update(['is_active' => true]);
            }
            return $bestPeriod;
        }
        
        if ($today->gt($activePeriod->end_date)) {
            $bestPeriod = self::findBestPeriodForDate($today, $userId);
            
            if ($bestPeriod && $bestPeriod->id !== $activePeriod->id) {
                $activePeriod->update(['is_active' => false]);
                $bestPeriod->update(['is_active' => true]);
                return $bestPeriod;
            }
        }
        
        return $activePeriod;
    }
    
    // Find best period for date FOR SPECIFIC USER
    public static function findBestPeriodForDate($date = null, $userId = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        $userId = $userId ?: auth()->id();
        
        $containingPeriod = self::where('user_id', $userId)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
        
        if ($containingPeriod) {
            return $containingPeriod;
        }
        
        $futurePeriod = self::where('user_id', $userId)
            ->where('start_date', '>', $date)
            ->orderBy('start_date', 'asc')
            ->first();
        
        if ($futurePeriod) {
            return $futurePeriod;
        }
        
        $pastPeriod = self::where('user_id', $userId)
            ->where('end_date', '<', $date)
            ->orderBy('end_date', 'desc')
            ->first();
        
        return $pastPeriod;
    }
    
    // // Get current active period
    // public static function getActivePeriod()
    // {
    //     return self::where('is_active', true)->first();
    // }
    
    // Check if date is within this period
    public function containsDate($date)
    {
        $date = $date instanceof \Carbon\Carbon ? $date : Carbon::parse($date);
        return $date->between($this->start_date, $this->end_date);
    }
    
    // Get days remaining in period (for progress)
    public function getDaysRemaining()
    {
        $today = Carbon::today();
        return max(0, $today->diffInDays($this->end_date, false));
    }

        // Find the most appropriate period for a given date
    // public static function findBestPeriodForDate($date = null)
    // {
    //     $date = $date ? Carbon::parse($date) : Carbon::today();
        
    //     // Try to find a period that contains this date
    //     $containingPeriod = self::where('start_date', '<=', $date)
    //         ->where('end_date', '>=', $date)
    //         ->first();
        
    //     if ($containingPeriod) {
    //         return $containingPeriod;
    //     }
        
    //     // If no period contains date, find the nearest future period
    //     $futurePeriod = self::where('start_date', '>', $date)
    //         ->orderBy('start_date', 'asc')
    //         ->first();
        
    //     if ($futurePeriod) {
    //         return $futurePeriod;
    //     }
        
    //     // If no future period, find the most recent past period
    //     $pastPeriod = self::where('end_date', '<', $date)
    //         ->orderBy('end_date', 'desc')
    //         ->first();
        
    //     return $pastPeriod;
    // }

    // // Auto-switch to appropriate period
    // public static function autoSwitchPeriod()
    // {
    //     $activePeriod = self::getActivePeriod();
    //     $today = Carbon::today();
        
    //     // If no active period, find best one
    //     if (!$activePeriod) {
    //         $bestPeriod = self::findBestPeriodForDate($today);
    //         if ($bestPeriod) {
    //             $bestPeriod->update(['is_active' => true]);
    //         }
    //         return $bestPeriod;
    //     }
        
    //     // If active period has ended, check if we should switch
    //     if ($today->gt($activePeriod->end_date)) {
    //         $bestPeriod = self::findBestPeriodForDate($today);
            
    //         // Only switch if we found a better period
    //         if ($bestPeriod && $bestPeriod->id !== $activePeriod->id) {
    //             // Deactivate current period
    //             $activePeriod->update(['is_active' => false]);
    //             // Activate best period
    //             $bestPeriod->update(['is_active' => true]);
    //             return $bestPeriod;
    //         }
    //     }
        
    //     return $activePeriod;
    // }
}