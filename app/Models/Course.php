<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $table = 'courses';
    protected $fillable = ['name', 'start_date','end_date','type','user_id'];
    protected $hidden = ['pivot'];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'course_student', 'course_id', 'student_id');
    }

    public function schedules(){
        return $this->hasMany(Schedule::class, 'course_id');
    }


}
