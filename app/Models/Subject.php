<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'title', 'program_id'];

    /**
     * Get the program that owns the subject.
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}



