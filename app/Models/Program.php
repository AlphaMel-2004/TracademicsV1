<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'department_id'];

    /**
     * Get the department that owns the program.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the subjects for the program.
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}



