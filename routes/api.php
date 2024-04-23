<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\studenstController;
use App\Http\Controllers\coursesController;
use App\Http\Controllers\schedulesController;
use App\Http\Middleware\CheckSanctumAuth;


Route::post('/register', [UserController::class, 'store']);
Route::post('/login', [UserController::class, 'login']);


Route::middleware(CheckSanctumAuth::class)->group(function () {
    // students routes
    Route::get('/students', [studenstController::class, 'index']);
    Route::post('/students', [studenstController::class, 'store']);
    Route::get('/students/{id}', [studenstController::class, 'show']);
    Route::put('/students/{id}', [studenstController::class, 'update']);
    Route::delete('/students/{id}', [studenstController::class, 'destroy']);
    Route::post('/bind-student-course', [studenstController::class, 'bindStudentCourse']);
    Route::delete('/student-courses/{student_id}/{course_id}', [studenstController::class, 'unbindStudentCourse']);
    // courses routes
    Route::get('/courses', [coursesController::class, 'index']);
    Route::post('/courses', [coursesController::class, 'store']);
    Route::get('/courses/{id}', [coursesController::class, 'show']);
    Route::put('/courses/{id}', [coursesController::class, 'update']);
    Route::delete('/courses/{id}', [coursesController::class, 'destroy']);

    // schedules routes
    Route::post('/schedules', [schedulesController::class, 'store']);
    Route::put('/schedules/{id}', [schedulesController::class, 'update']);
    Route::delete('/schedules/{id}', [schedulesController::class, 'destroy']);

    // dashboard routes
    Route::get('/dashboard', [studenstController::class, 'stadistics']);

    Route::get('/logout', [UserController::class, 'logout']);

});




