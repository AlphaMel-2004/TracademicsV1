<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplianceDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id','document_type_id','self_evaluation','status','drive_link'
    ];

    public function assignment()
    {
        return $this->belongsTo(FacultyAssignment::class, 'assignment_id');
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }
}


