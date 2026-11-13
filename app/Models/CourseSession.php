<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseSession extends Model
{
    protected $fillable = [
        'course_id',
        'course_schedule_id',
        'session_date',
        'starts_at',
        'ends_at',
        'room',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(CourseSchedule::class, 'course_schedule_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }
}
