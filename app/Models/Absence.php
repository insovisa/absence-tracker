<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Absence extends Model
{
    protected $fillable = ['date', 'absence_type', 'authorized_type', 'reason', 'user_id'];
    
    // Add this to cast 'date' to Carbon instance
    protected $casts = [
        'date' => 'date',
    ];

    // Add user relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Helper method to get day count
    public function getDayCountAttribute(): float
    {
        return $this->absence_type === 'authorized' ? 0.5 : 1;
    }
    
    // Get authorized type label
    public function getAuthorizedTypeLabelAttribute(): string
    {
        return match($this->authorized_type) {
            'paper' => 'Write Paper',
            'counter' => 'Inform Counter',
            'phone' => 'Phone Call',
            default => 'Unknown'
        };
    }
    
    // Check if within last 3 months
    public function isWithinThreeMonths(): bool
    {
        return $this->date >= Carbon::now()->subMonths(3);
    }

    // Add this method to the Absence model
    public static function sumDayCountForUser($userId)
    {
        $absences = self::where('user_id', $userId)->get();
        return $absences->sum(function($absence) {
            return $absence->day_count;
        });
    }

    // Or for a query builder
    public static function sumDayCount($query)
    {
        $absences = $query->get();
        return $absences->sum(function($absence) {
            return $absence->day_count;
        });
    }
}