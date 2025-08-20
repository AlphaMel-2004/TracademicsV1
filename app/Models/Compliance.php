<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compliance extends Model
{
    use HasFactory;

    protected $fillable = ['assignment_id', 'status', 'drive_link', 'remarks'];

    public function assignment()
    {
        return $this->belongsTo(FacultyAssignment::class, 'assignment_id');
    }
}



