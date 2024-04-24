<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ScheduleController;
use App\Http\Middleware\CheckSanctumAuth;


Route::post('/register', [UserController::class, 'store']);
Route::post('/login', [UserController::class, 'login']);


Route::middleware(CheckSanctumAuth::class)->group(function () {
    // students routes
    Route::get('/students', [StudentController::class, 'index']);
    Route::post('/students', [StudentController::class, 'store']);
    Route::get('/students/{id}', [StudentController::class, 'show']);
    Route::put('/students/{id}', [StudentController::class, 'update']);
    Route::delete('/students/{id}', [StudentController::class, 'destroy']);
    Route::post('/bind-student-course', [StudentController::class, 'bindStudentCourse']);
    Route::delete('/student-courses/{student_id}/{course_id}', [StudentController::class, 'unbindStudentCourse']);
    // courses routes
    Route::get('/courses', [CourseController::class, 'index']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);
    Route::put('/courses/{id}', [CourseController::class, 'update']);
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

    // schedules routes
    Route::post('/schedules', [ScheduleController::class, 'store']);
    Route::put('/schedules/{id}', [ScheduleController::class, 'update']);
    Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy']);

    // dashboard routes
    Route::get('/dashboard', [StudentController::class, 'stadistics']);

    Route::get('/logout', [UserController::class, 'logout']);

});




