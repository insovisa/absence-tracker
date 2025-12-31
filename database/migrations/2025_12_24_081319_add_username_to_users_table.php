<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }
        });
        
        // Update existing users with default usernames
        \Illuminate\Support\Facades\DB::table('users')->whereNull('username')->get()->each(function($user) {
            $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $user->name));
            if (empty($username)) $username = 'user' . $user->id;
            
            // Ensure uniqueness
            $originalUsername = $username;
            $counter = 1;
            while (\Illuminate\Support\Facades\DB::table('users')->where('username', $username)->exists()) {
                $username = $originalUsername . $counter;
                $counter++;
            }
            
            \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $user->id)
                ->update(['username' => $username]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};