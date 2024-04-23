<?php

namespace App\Http\Controllers;

use App\Models\students;
use App\Models\courses;
use App\Models\course_student;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class studenstController
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
            $search = $request->search;
            $query = students::where('user_id', $request->user_id);
            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->orWhere('name', 'like', '%' . $search . '%')
                        ->orWhere('lastname', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('age', 'like', '%' . $search . '%')
                        ->orWhere('identification', 'like', '%' . $search . '%');
                });
            }
            $students = $query->paginate($limit, ['*'], 'page', $page);
            $response = [
                ...$students->toArray(),
                'message' => 'Students retrieved successfully',
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
                'name' => 'required|min:3|max:100',
                'lastname' => 'required|min:3|max:100',
                'email' => 'required|email',
                'age' => 'required|integer|min:18',
                'identification' => 'required|max:11',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $email = $request->email;
            $identification = $request->identification;

            $getStudent = students::where('user_id', $request->user_id)
                ->where(function ($query) use ($email, $identification) {
                    $query->where('email', $email)
                        ->orWhere('identification', $identification);
                })
                ->first();
            if ($getStudent) {
                return response()->json(['error' => 'Student already exists'], 400);
            }

            $student = new students();
            $student->name = $request->name;
            $student->lastname = $request->lastname;
            $student->email = $request->email;
            $student->age = $request->age;
            $student->identification = $request->identification;
            $student->user_id = $request->user_id;
            $student->save();
            return response()->json(['student' => $student, 'message' => 'Student created successfully']);
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

            $user_id = $request->user_id;

            $student = students::where('students.id', $id)
                ->where('students.user_id', $user_id)
                ->with(['courses' => function ($query) use ($user_id, $id) {
                    $query->where('courses.user_id', $user_id)->with(['schedules']);
                }])
                ->first();
            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }
            return response()->json([
                'student' => $student,
                'message' => 'Student retrieved successfully',
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
                'name' => 'required|min:3|max:100',
                'lastname' => 'required|min:3|max:100',
                'email' => 'required|email',
                'age' => 'required|integer|min:18',
                'identification' => 'required|max:11',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $student = students::where('id', $id)
                ->where('user_id', $request->user_id)
                ->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            $student->name = $request->name;
            $student->lastname = $request->lastname;
            $student->email = $request->email;
            $student->age = $request->age;
            $student->identification = $request->identification;
            $student->user_id = $request->user_id;
            $student->save();

            return response()->json(['student' => $student, 'message' => 'Student updated successfully']);
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
            $student = students::where('id', $id)
                ->where('user_id', $request->user_id)
                ->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }


            $course = course_student::where('student_id', $id)->first();

            if ($course) {
                return response()->json(['error' => 'Student is enrolled in a course'], 400);
            }

            $student->delete();

            return response()->json(['student' => $student, 'message' => 'Student deleted successfully']);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    public function bindStudentCourse(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'student_id' => 'required|integer',
                'course_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $isStudentOfProfessor = students::where('id', $request->student_id)
                ->where('user_id', $request->user_id)
                ->first();
            if (!$isStudentOfProfessor) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            $isProfessorCourse = courses::where('id', $request->course_id)
                ->where('user_id', $request->user_id)
                ->first();
            if (!$isProfessorCourse) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            $isAlreadyEnrolled = course_student::where('student_id', $request->student_id)
                ->where('course_id', $request->course_id)
                ->first();
            if ($isAlreadyEnrolled) {
                return response()->json(['error' => 'Student is already enrolled in this course'], 400);
            }

            $course_student = new course_student();
            $course_student->student_id = $request->student_id;
            $course_student->course_id = $request->course_id;
            $course_student->save();

            return response()->json(['course_student' => $course_student, 'message' => 'Student enrolled in course successfully']);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    public function unbindStudentCourse(string $student_id, string $course_id,Request $request)
    {
        try {
            $course_student = course_student::where('student_id', $student_id)
                ->where('course_id', $course_id)
                ->first();

            if (!$course_student) {
                return response()->json(['error' => 'Student is not enrolled in this course'], 400);
            }
            $isStudentOfProfessor = students::where('id', $student_id)
                ->where('user_id', $request->user_id)
                ->first();
            if (!$isStudentOfProfessor) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            $isProfessorCourse = courses::where('id', $course_id)
                ->where('user_id', $request->user_id)
                ->first();
            if (!$isProfessorCourse) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            $course_student->delete();

            return response()->json(['message' => 'Student unenrolled from course successfully']);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    public function stadistics(Request $request)
    {
        try {
            $topSixMoths = courses::where('user_id', $request->user_id)
                ->where('created_at', '>=', now()->subMonths(6))
                ->withCount('students')
                ->orderBy('students_count', 'desc')
                ->limit(3)
                ->get();
            // top 3 de estudiantes con más cursos
            $topStudents = students::where('user_id', $request->user_id)
                ->withCount('courses')
                ->orderBy('courses_count', 'desc')
                ->limit(3)
                ->get();
            $totalStudents = students::where('user_id', $request->user_id)->count();
            $totalCourses = courses::where('user_id', $request->user_id)->count();
            return response()->json([
                'data' => [
                    'topSixMoths' => $topSixMoths,
                    'topStudents' => $topStudents,
                    'totalStudents' => $totalStudents,
                    'totalCourses' => $totalCourses,
                ],
                'message' => 'Stadistics retrieved successfully',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

}
