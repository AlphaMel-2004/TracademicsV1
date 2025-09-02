<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\FacultyAssignment;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'role_id',
        'department_id',
        'program_id',
        'faculty_type',
        'current_semester_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @return array<string, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Query Scopes for better performance
    public function scopeFacultyMembers($query)
    {
        return $query->whereHas('role', function($q) {
            $q->where('name', 'Faculty Member');
        });
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }

    public function scopeByFacultyType($query, $type)
    {
        return $query->where('faculty_type', $type);
    }

    public function scopeWithRole($query, $roleName)
    {
        return $query->whereHas('role', function($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    // Relationships
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function facultyAssignments(): HasMany
    {
        return $this->hasMany(FacultyAssignment::class, 'faculty_id');
    }

    public function complianceDocuments(): HasMany
    {
        return $this->hasMany(ComplianceDocument::class);
    }

    public function hasRole($role): bool
    {
        return $this->role && $this->role->name === $role;
    }

    public function currentSemester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'current_semester_id');
    }

    public function semesterSessions(): HasMany
    {
        return $this->hasMany(SemesterSession::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(UserActivityLog::class);
    }

    /**
     * Additional optimized query scopes for performance
     */
    public function scopeWithCompleteProfile($query)
    {
        return $query->with(['role', 'department', 'program'])
            ->whereNotNull('role_id')
            ->whereNotNull('department_id');
    }

    public function scopeActiveInCurrentSemester($query)
    {
        return $query->whereNotNull('current_semester_id')
            ->whereHas('currentSemester', function ($q) {
                $q->where('is_active', true);
            });
    }

    public function scopeWithAssignmentStats($query)
    {
        return $query->withCount([
            'facultyAssignments',
            'facultyAssignments as complied_assignments_count' => function ($q) {
                $q->whereHas('complianceDocuments', function ($subQuery) {
                    $subQuery->where('status', 'Complied');
                });
            }
        ]);
    }

    public function scopeForPerformanceReport($query, $departmentId = null, $programId = null)
    {
        $query->select([
            'users.id',
            'users.name',
            'users.email',
            'users.faculty_type',
            'departments.name as department_name',
            'programs.name as program_name'
        ])
        ->join('roles', 'users.role_id', '=', 'roles.id')
        ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
        ->leftJoin('programs', 'users.program_id', '=', 'programs.id')
        ->where('roles.name', 'Faculty Member');

        if ($departmentId) {
            $query->where('users.department_id', $departmentId);
        }

        if ($programId) {
            $query->where('users.program_id', $programId);
        }

        return $query;
    }
}
