<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['faculty_id', 'subject_id', 'term_id', 'program_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
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



