<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Course;
use App\Models\CourseStudent;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class StudentController
{

    /**
     * @OA\Get(
     *      path="/api/students",
     *      operationId="getStudents",
     *      tags={"Students"},
     *      security={{"bearer_token":{}}},
     *      summary="Get list of students",
     *      description="Returns list of students",
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="limit",
     *          description="Page number",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="search",
     *          description="Search term",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="John"),
     *                      @OA\Property(property="lastname", type="string", example="Doe"),
     *                      @OA\Property(property="email", type="string", example="email@email.com"),
     *                      @OA\Property(property="age", type="integer", example=25),
     *                      @OA\Property(property="identification", type="string", example="12345678901"),
     *                      @OA\Property(property="user_id", type="integer", example=1),
     *                      @OA\Property(property="created_at", type="string", example="2021-09-01T00:00:00.000000Z"),
     *                      @OA\Property(property="updated_at", type="string", example="2021-09-01T00:00:00.000000Z"),
     *                  )
     *              ),
     *              @OA\Property(property="current_page", type="integer", example=1),
     *              @OA\Property(property="from", type="integer", example=1),
     *              @OA\Property(property="last_page", type="integer", example=10),
     *              @OA\Property(property="path", type="string", example="http://example.com/api/students"),
     *              @OA\Property(property="per_page", type="integer", example=15),
     *              @OA\Property(property="to", type="integer", example=15),
     *              @OA\Property(property="total", type="integer", example=150),
     *          )
     *      )
     * )
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
            $query = Student::where('user_id', $request->user_id);
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
     * @OA\Post(
     *      path="/api/students",
     *      operationId="storeStudent",
     *      tags={"Students"},
     *      security={{"bearer_token":{}}},
     *      summary="Store new student",
     *      description="Returns student data",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","lastname","email","age","identification","user_id"},
     *              @OA\Property(property="name", type="string", format="text", example="John"),
     *              @OA\Property(property="lastname", type="string", format="text", example="Doe"),
     *              @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *              @OA\Property(property="age", type="integer", example=20),
     *              @OA\Property(property="identification", type="string", format="text", example="1234567890"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="John"),
     *                  @OA\Property(property="lastname", type="string", example="Doe"),
     *                  @OA\Property(property="email", type="string", example="email@email.com"),
     *                  @OA\Property(property="age", type="integer", example=25),
     *                  @OA\Property(property="identification", type="string", example="12345678901"),
     *                  @OA\Property(property="user_id", type="integer", example=1),
     *                  @OA\Property(property="created_at", type="string", example="2021-09-01T00:00:00.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", example="2021-09-01T00:00:00.000000Z"),
     *              ),
     *              @OA\Property(property="message", type="string", example="Student created successfully"),
     *          )
     *      )
     * )
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

            $getStudent = Student::where('user_id', $request->user_id)
                ->where(function ($query) use ($email, $identification) {
                    $query->where('email', $email)
                        ->orWhere('identification', $identification);
                })
                ->first();
            if ($getStudent) {
                return response()->json(['error' => 'Student already exists'], 400);
            }

            $student = new Student();
            $student->name = $request->name;
            $student->lastname = $request->lastname;
            $student->email = $request->email;
            $student->age = $request->age;
            $student->identification = $request->identification;
            $student->user_id = $request->user_id;
            $student->save();
            return response()->json(['data' => $student, 'message' => 'Student created successfully']);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }


    /**
     * @OA\Get(
     *      path="/api/students/{id}",
     *      operationId="getStudent",
     *      tags={"Students"},
     *      security={{"bearer_token":{}}},
     *      summary="Get existing student",
     *      description="Returns student data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Student id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="John"),
     *                  @OA\Property(property="lastname", type="string", example="Doe"),
     *                  @OA\Property(property="email", type="string", example="email@email.com"),
     *                  @OA\Property(property="age", type="integer", example=25),
     *                  @OA\Property(property="identification", type="string", example="12345678901"),
     *                  @OA\Property(property="user_id", type="integer", example=1),
     *                  @OA\Property(property="created_at", type="string", example="2021-09-01T00:00:00.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", example="2021-09-01T00:00:00.000000Z"),
     *                  @OA\Property(
     *                      property="courses",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="name", type="string", example="Course 1"),
     *                          @OA\Property(property="user_id", type="integer", example=1),
     *                          @OA\Property(property="start_date", type="string", example="2021-09-01T00:00:00.000000Z"),
     *                          @OA\Property(property="end_date", type="string", example="2021-09-01T00:00:00.000000Z"),
     *                          @OA\Property(
     *                              property="schedules",
     *                              type="array",
     *                              @OA\Items(
     *                                  type="object",
     *                                  @OA\Property(property="id", type="integer", example=1),
     *                                  @OA\Property(property="day", type="string", example="LUNES"),
     *                                  @OA\Property(property="start_hour", type="integer", example=8),
     *                                  @OA\Property(property="end_hour", type="integer", example=10),
     *                                  @OA\Property(property="course_id", type="integer", example=1),
     *                              )
     *                          ),
     *                      ),
     *                      @OA\Property(property="created_at", type="string", example="2021-09-01T00:00:00.000000Z"),
     *                      @OA\Property(property="updated_at", type="string", example="2021-09-01T00:00:00.000000Z"),
     *                  ),
     *              ),
     *              @OA\Property(property="message", type="string", example="Student retrieved successfully"),
     *          )
     *      ),
     * )
     */
    public function show(string $id, Request $request)
    {
        try {

            $user_id = $request->user_id;

            $student = Student::where('students.id', $id)
                ->where('students.user_id', $user_id)
                ->with(['courses' => function ($query) use ($user_id, $id) {
                    $query->where('courses.user_id', $user_id)->with(['schedules']);
                }])
                ->first();
            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }
            return response()->json([
                'data' => $student,
                'message' => 'Student retrieved successfully',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    /**
     * @OA\Put(
     *      path="/api/students/{id}",
     *      operationId="updateStudent",
     *      tags={"Students"},
     *      security={{"bearer_token":{}}},
     *      summary="Update existing student",
     *      description="Returns updated student data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Student id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","lastname","email","age","identification","user_id"},
     *              @OA\Property(property="name", type="string", format="text", example="John"),
     *              @OA\Property(property="lastname", type="string", format="text", example="Doe"),
     *              @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *              @OA\Property(property="age", type="integer", example=20),
     *              @OA\Property(property="identification", type="string", format="text", example="1234567890"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="John"),
     *                  @OA\Property(property="lastname", type="string", example="Doe"),
     *                  @OA\Property(property="email", type="string", example="email@email.com"),
     *                  @OA\Property(property="age", type="integer", example=25),
     *                  @OA\Property(property="identification", type="string", example="12345678901"),
     *                  @OA\Property(property="user_id", type="integer", example=1),
     *                  @OA\Property(property="created_at", type="string", example="2021-09-01T00:00:00.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", example="2021-09-01T00:00:00.000000Z"),
     *              ),
     *              @OA\Property(property="message", type="string", example="Student updated successfully"),
     *          )
     *      )
     * )
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

            $student = Student::where('id', $id)
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

            return response()->json(['data' => $student, 'message' => 'Student updated successfully']);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }


    /**
     * @OA\Delete(
     *      path="/api/students/{id}",
     *      operationId="deleteStudent",
     *      tags={"Students"},
     *      security={{"bearer_token":{}}},
     *      summary="Delete existing student",
     *      description="Returns deleted student data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Student id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="John"),
     *                  @OA\Property(property="lastname", type="string", example="Doe"),
     *                  @OA\Property(property="email", type="string", example="email@email.com"),
     *                  @OA\Property(property="age", type="integer", example=25),
     *                  @OA\Property(property="identification", type="string", example="12345678901"),
     *                  @OA\Property(property="user_id", type="integer", example=1),
     *                  @OA\Property(property="created_at", type="string", example="2021-09-01T00:00:00.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", example="2021-09-01T00:00:00.000000Z"),
     *              ),
     *              @OA\Property(property="message", type="string", example="Student deleted successfully"),
     *          )
     *      ),
     * )
     */
    public function destroy(string $id, Request $request)
    {
        try {
            $student = Student::where('id', $id)
                ->where('user_id', $request->user_id)
                ->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }


            $course = CourseStudent::where('student_id', $id)->first();

            if ($course) {
                return response()->json(['error' => 'Student is enrolled in a course'], 400);
            }

            $student->delete();

            return response()->json(['data' => $student, 'message' => 'Student deleted successfully']);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }


    /**
     * @OA\Post(
     *      path="/api/bind-student-course",
     *      operationId="bindStudentCourse",
     *      tags={"Students"},
     *      security={{"bearer_token":{}}},
     *      summary="Bind student to a course",
     *      description="Returns student and course data",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"student_id","course_id"},
     *              @OA\Property(property="student_id", type="integer", example=1),
     *              @OA\Property(property="course_id", type="integer", example=1),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="student_id", type="integer", example=1),
     *                  @OA\Property(property="course_id", type="integer", example=1),
     *              ),
     *              @OA\Property(property="message", type="string", example="Student enrolled in course successfully"),
     *          )
     *      )
     * )
     */
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

            $isStudentOfProfessor = Student::where('id', $request->student_id)
                ->where('user_id', $request->user_id)
                ->first();
            if (!$isStudentOfProfessor) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            $isProfessorCourse = Course::where('id', $request->course_id)
                ->where('user_id', $request->user_id)
                ->first();
            if (!$isProfessorCourse) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            $isAlreadyEnrolled = CourseStudent::where('student_id', $request->student_id)
                ->where('course_id', $request->course_id)
                ->first();
            if ($isAlreadyEnrolled) {
                return response()->json(['error' => 'Student is already enrolled in this course'], 400);
            }

            $course_student = new CourseStudent();
            $course_student->student_id = $request->student_id;
            $course_student->course_id = $request->course_id;
            $course_student->save();

            return response()->json(['data' => $course_student, 'message' => 'Student enrolled in course successfully']);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/student-courses/{student_id}/{course_id}",
     *      operationId="unbindStudentCourse",
     *      tags={"Students"},
     *      security={{"bearer_token":{}}},
     *      summary="Unbind student from a course",
     *      description="Returns a message",
     *      @OA\Parameter(
     *          name="student_id",
     *          description="Student id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="course_id",
     *          description="Course id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Student unenrolled from course successfully"),
     *          )
     *      )
     * )
     */
    public function unbindStudentCourse(string $student_id, string $course_id,Request $request)
    {
        try {
            $course_student = CourseStudent::where('student_id', $student_id)
                ->where('course_id', $course_id)
                ->first();

            if (!$course_student) {
                return response()->json(['error' => 'Student is not enrolled in this course'], 400);
            }
            $isStudentOfProfessor = Student::where('id', $student_id)
                ->where('user_id', $request->user_id)
                ->first();
            if (!$isStudentOfProfessor) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            $isProfessorCourse = Course::where('id', $course_id)
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


    /**
     * @OA\Get(
     *      path="/api/dashboard",
     *      operationId="getStudentStadistics",
     *      tags={"Dashboard"},
     *      security={{"bearer_token":{}}},
     *      summary="Get student stadistics",
     *      description="Returns stadistics data",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(
     *                      property="topSixMoths",
     *                      type="array", @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="name", type="string", example="Course 1"),
     *                          @OA\Property(property="user_id", type="integer", example=1),
     *                          @OA\Property(property="start_date", type="string", example="2021-09-01T00:00:00.000000Z"),
     *                          @OA\Property(property="end_date", type="string", example="2021-09-01T00:00:00.000000Z"),
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="topStudents",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="name", type="string", example="John"),
     *                          @OA\Property(property="lastname", type="string", example="Doe"),
     *                          @OA\Property(property="email", type="string", example="email@email.com"),
     *                          @OA\Property(property="age", type="integer", example=25),
     *                          @OA\Property(property="identification", type="string", example="12345678901"),
     *                          @OA\Property(property="user_id", type="integer", example=1),
     *                      )
     *                  ),
     *                  @OA\Property(property="totalStudents", type="integer"),
     *                  @OA\Property(property="totalCourses", type="integer"),
     *              ),
     *              @OA\Property(property="message", type="string", example="Stadistics retrieved successfully"),
     *          )
     *      )
     * )
     */
    public function stadistics(Request $request)
    {
        try {
            $topSixMoths = Course::where('user_id', $request->user_id)
                ->where('created_at', '>=', now()->subMonths(6))
                ->withCount('students')
                ->orderBy('students_count', 'desc')
                ->limit(3)
                ->get();
            // top 3 de estudiantes con más cursos
            $topStudents = Student::where('user_id', $request->user_id)
                ->withCount('courses')
                ->orderBy('courses_count', 'desc')
                ->limit(3)
                ->get();
            $totalStudents = Student::where('user_id', $request->user_id)->count();
            $totalCourses = Course::where('user_id', $request->user_id)->count();
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
