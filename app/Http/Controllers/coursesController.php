<?php

namespace App\Http\Controllers;

use App\Models\courses;
use App\Models\schedules;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Models\course_student;

class coursesController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'page' => 'required|integer',
                'limit' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $page = $request->page;
            $limit = $request->limit;
            $user_id = $request->user_id;
            $search = $request->search;
            $query = courses::where('user_id', $user_id);
            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->orWhere('name', 'like', '%' . $search . '%')
                        ->orWhere('start_date', 'like', '%' . $search . '%')
                        ->orWhere('end_date', 'like', '%' . $search . '%')
                        ->orWhere('type', 'like', '%' . $search . '%');
                });
            }
            $courses = $query->paginate($limit, ['*'], 'page', $page);

            $response = [
                ...$courses->toArray(),
                'message' => 'Courses retrieved successfully',
            ];

            return response()->json($response);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:50',
                'start_date' => 'required',
                'end_date' => 'required',
                'type' => 'required|in:OFFLINE,ONLINE',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $course = new courses();
            $course->name = $request->name;
            $course->start_date = $request->start_date;
            $course->end_date = $request->end_date;
            $course->type = $request->type;
            $course->user_id = $request->user_id;
            $course->save();
            return $course;

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        try {

            $course = courses::with('schedules')
            ->where('id', $id)
            ->where('user_id', $request->user_id)
            ->first();


            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            return response()->json([
                'course' => $course,
                'message' => 'Course retrieved successfully',
            ]);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $id, Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required|max:50',
                'start_date' => 'required',
                'end_date' => 'required',
                'type' => 'required|in:OFFLINE,ONLINE',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $course = courses::where('id', $id)
                ->where('user_id', $request->user_id)
                ->first();
            $course->name = $request->name;
            $course->start_date = $request->start_date;
            $course->end_date = $request->end_date;
            $course->type = $request->type;
            $course->save();
            return response()->json(['course' => $course, 'message' => 'Course updated successfully']);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        try {
            $course = courses::where('id', $id)
                ->where('user_id', $request->user_id)
                ->first();

            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            $studentsInCourse = course_student::where('course_id', $id)->count();

            if ($studentsInCourse > 0) {
                return response()->json(['error' => 'Course has students, cannot be deleted'], 400);
            }

            schedules::where('course_id', $id)->delete();

            $course->delete();
            return response()->json(['course' => $course, 'message' => 'Course deleted successfully']);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }
}
