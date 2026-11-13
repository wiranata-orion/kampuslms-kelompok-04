<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('room', 100);
            $table->date('effective_from');
            $table->date('effective_until');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['weekday', 'effective_from', 'effective_until']);
            $table->index(['room', 'weekday']);
        });

        Schema::create('course_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_schedule_id')->constrained()->restrictOnDelete();
            $table->date('session_date');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('room', 100);
            $table->string('status', 20)->default('scheduled');
            $table->timestamps();

            $table->unique(['course_schedule_id', 'session_date']);
            $table->index(['course_id', 'starts_at']);
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_session_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamp('attended_at');
            $table->string('source', 5)->default('code');
            $table->timestamps();

            $table->unique(['course_session_id', 'user_id']);
            $table->index(['user_id', 'attended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('course_sessions');
        Schema::dropIfExists('course_schedules');
    }
};
