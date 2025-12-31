<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('absence_type', ['authorized', 'unauthorized']);
            $table->enum('authorized_type', ['paper', 'counter', 'phone'])->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
            
            // Index for faster queries
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};