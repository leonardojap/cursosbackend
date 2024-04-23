<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class students extends Model
{
    use HasFactory;
    protected $table = 'students';
    protected $fillable = ['name', 'lastname', 'email','age','identification'];
    protected $hidden = ['pivot'];

    public function courses()
    {
        return $this->belongsToMany(courses::class, 'course_student', 'student_id', 'course_id');
    }
}
