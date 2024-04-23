<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class schedules extends Model
{
    use HasFactory;
    protected $table = 'schedules';
    protected $fillable = ['day', 'start_hour', 'end_hour' ,'course_id'];
    protected $hidden = ['pivot'];

    public function course(){
        return $this->belongsTo(courses::class, 'id');
    }

}
