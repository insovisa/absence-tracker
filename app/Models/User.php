<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',  // ADD THIS
        'email',
        'password',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    // Relationship with periods
    public function periods()
    {
        return $this->hasMany(Period::class);
    }

    // Relationship with absences
    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    // Check if user can create new user
    public function canCreateUser()
    {
        if (!$this->is_admin) {
            return false;
        }
        
        return User::count() < 5;
    }
}