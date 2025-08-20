<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'year'];

    /**
     * Get the faculty assignments for the term.
     */
    public function facultyAssignments()
    {
        return $this->hasMany(FacultyAssignment::class);
    }
}



