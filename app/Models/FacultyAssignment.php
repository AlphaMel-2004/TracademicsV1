<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id', 
        'subject_id', 
        'subject_code', 
        'subject_description',
        'semester_id', 
        'program_id',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // Add accessor for consistent subject access when no subject_id exists
    public function getSubjectAttribute()
    {
        // If we have a subject_id and the relationship is loaded, use it
        if ($this->subject_id && $this->relationLoaded('subject')) {
            return $this->getRelation('subject');
        }
        
        // Otherwise, create a virtual subject object from stored data
        return (object) [
            'id' => $this->subject_id,
            'code' => $this->subject_code,
            'title' => $this->subject_description,
        ];
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function term()
    {
        return $this->belongsTo(Semester::class, 'semester_id'); // Keep for backward compatibility
    }

    // Add accessor for consistent term access
    public function getTermAttribute()
    {
        // Return the semester as term for view compatibility
        return $this->semester;
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function complianceDocuments()
    {
        return $this->hasMany(ComplianceDocument::class, 'assignment_id');
    }
}



