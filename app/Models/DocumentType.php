<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'submission_type', 'description', 'order'];

    public function complianceDocuments()
    {
        return $this->hasMany(ComplianceDocument::class);
    }

    // Scope for semester-wide requirements
    public function scopeSemesterWide($query)
    {
        return $query->where('submission_type', 'semester');
    }

    // Scope for subject-specific requirements
    public function scopeSubjectSpecific($query)
    {
        return $query->where('submission_type', 'subject');
    }
}
