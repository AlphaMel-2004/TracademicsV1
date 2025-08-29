<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'year', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the faculty assignments for the semester.
     */
    public function facultyAssignments(): HasMany
    {
        return $this->hasMany(FacultyAssignment::class);
    }

    /**
     * Get the semester sessions.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(SemesterSession::class);
    }

    /**
     * Get users currently in this semester.
     */
    public function currentUsers(): HasMany
    {
        return $this->hasMany(User::class, 'current_semester_id');
    }
}
