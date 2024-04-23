<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    use HasFactory, HasApiTokens;
    protected $table = 'users';
    protected $fillable = ['name', 'lastname', 'email','password'];

    public function courses(){
        return $this->hasMany(courses::class, 'user_id');
    }
}
