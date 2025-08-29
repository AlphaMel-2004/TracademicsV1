<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'compliance_id',
        'drive_link',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function compliance(): BelongsTo
    {
        return $this->belongsTo(ComplianceDocument::class, 'compliance_id');
    }
}
