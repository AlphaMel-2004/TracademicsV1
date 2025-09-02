<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assignment_id',
        'document_type_id',
        'term_id',
        'self_evaluation',
        'status',
        'drive_link'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(FacultyAssignment::class, 'assignment_id');
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'assignment.subject_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ComplianceSubmission::class, 'compliance_id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(ComplianceLink::class);
    }

    // Check if the document has any valid links
    public function hasLinks(): bool
    {
        return $this->links()->exists();
    }

    // Get the current status based on links
    public function getCalculatedStatus(): string
    {
        if ($this->hasLinks()) {
            return 'Complied';
        }

        // Return not complied if no links
        return 'Not Complied';
    }

    // Check if the status should be "Not Applicable"
    public function shouldBeNotApplicable(): bool
    {
        // This can be customized based on business logic
        // For now, documents are only "Not Applicable" if manually set
        return $this->status === 'Not Applicable';
    }

    // Query Scopes for better performance
    public function scopeComplied($query)
    {
        return $query->where('status', 'Complied');
    }

    public function scopeNotComplied($query)
    {
        return $query->where('status', 'Not Complied');
    }

    public function scopeNotApplicable($query)
    {
        return $query->where('status', 'Not Applicable');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->whereHas('assignment.user', function($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        });
    }

    public function scopeByProgram($query, $programId)
    {
        return $query->whereHas('assignment.user', function($q) use ($programId) {
            $q->where('program_id', $programId);
        });
    }

    public function scopeByTerm($query, $termId)
    {
        return $query->where('term_id', $termId);
    }

    /**
     * Additional optimized scopes for performance
     */
    public function scopeWithAssignmentDetails($query)
    {
        return $query->with([
            'assignment.user:id,name,email,department_id,program_id',
            'assignment.subject:code,title',
            'documentType:id,name,submission_type'
        ]);
    }

    public function scopeRecentlyUpdated($query, $hours = 24)
    {
        return $query->where('updated_at', '>=', now()->subHours($hours));
    }

    public function scopeForPerformanceAnalysis($query)
    {
        return $query->select([
            'compliance_documents.*',
            'document_types.name as document_name',
            'users.name as faculty_name',
            'departments.name as department_name'
        ])
        ->join('document_types', 'compliance_documents.document_type_id', '=', 'document_types.id')
        ->join('faculty_assignments', 'compliance_documents.assignment_id', '=', 'faculty_assignments.id')
        ->join('users', 'faculty_assignments.faculty_id', '=', 'users.id')
        ->leftJoin('departments', 'users.department_id', '=', 'departments.id');
    }

    public function scopePendingSubmissions($query)
    {
        return $query->where('status', 'Not Complied')
            ->where('created_at', '<=', now()->subDays(7)); // Pending for more than a week
    }

    public function scopeCompletedToday($query)
    {
        return $query->where('status', 'Complied')
            ->whereDate('updated_at', today());
    }

    public function scopeBySubmissionType($query, $submissionType)
    {
        return $query->whereHas('documentType', function ($q) use ($submissionType) {
            $q->where('submission_type', $submissionType);
        });
    }
}


