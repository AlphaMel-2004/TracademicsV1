<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// This model is kept for backward compatibility
// New functionality should use the Semester model
class Term extends Model
{
    use HasFactory;

    protected $table = 'semesters'; // Point to the renamed table

    protected $fillable = ['name', 'year'];

    /**
     * Get the faculty assignments for the term.
     */
    public function facultyAssignments()
    {
        return $this->hasMany(FacultyAssignment::class, 'semester_id');
    }
}



