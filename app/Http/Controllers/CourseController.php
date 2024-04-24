<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Models\CourseStudent;

class CourseController
{
    /**
     * @OA\Get(
     *      path="/api/courses",
     *      operationId="getCourses",
     *      tags={"Courses"},
     *      security={{"bearer_token":{}}},
     *      summary="Get list of courses",
     *      description="Returns list of courses",
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
     *          description="Limit of courses per page",
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
     *              @OA\Property(property="current_page", type="integer"),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="Course 1"),
     *                      @OA\Property(property="start_date", type="string", example="2022-01-01"),
     *                      @OA\Property(property="end_date", type="string", example="2022-12-31"),
     *                      @OA\Property(property="type", type="string", example="ONLINE"),
     *                      @OA\Property(property="user_id", type="integer", example=1),
     *                 )
     *              ),
     *              @OA\Property(property="first_page_url", type="string"),
     *              @OA\Property(property="from", type="integer"),
     *              @OA\Property(property="last_page", type="integer"),
     *              @OA\Property(property="last_page_url", type="string"),
     *              @OA\Property(property="next_page_url", type="string"),
     *              @OA\Property(property="path", type="string"),
     *              @OA\Property(property="per_page", type="integer"),
     *              @OA\Property(property="prev_page_url", type="string"),
     *              @OA\Property(property="to", type="integer"),
     *              @OA\Property(property="total", type="integer"),
     *              @OA\Property(property="message", type="string", example="Courses retrieved successfully"),
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
            $user_id = $request->user_id;
            $search = $request->search;
            $query = Course::where('user_id', $user_id);
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
     * @OA\Post(
     *      path="/api/courses",
     *      operationId="storeCourse",
     *      tags={"Courses"},
     *      security={{"bearer_token":{}}},
     *      summary="Store new course",
     *      description="Returns course data",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","start_date","end_date","type"},
     *              @OA\Property(property="name", type="string", format="text", example="Course 1"),
     *              @OA\Property(property="start_date", type="string", format="date", example="2022-01-01"),
     *              @OA\Property(property="end_date", type="string", format="date", example="2022-12-31"),
     *              @OA\Property(property="type", type="string", format="text", example="ONLINE")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="name", type="string", example="Course 1"),
     *              @OA\Property(property="start_date", type="string", example="2022-01-01"),
     *              @OA\Property(property="end_date", type="string", example="2022-12-31"),
     *              @OA\Property(property="type", type="string", example="ONLINE"),
     *              @OA\Property(property="user_id", type="integer", example=1),
     *             ),
     *             @OA\Property(property="message", type="string", example="Course created successfully"),
     *          )
     *      )
     * )
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

            $course = new Course();
            $course->name = $request->name;
            $course->start_date = $request->start_date;
            $course->end_date = $request->end_date;
            $course->type = $request->type;
            $course->user_id = $request->user_id;
            $course->save();
            return response()->json([
                "data" => $course,
                "message" => "Course created successfully"
            ]);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/courses/{id}",
     *      operationId="getCourse",
     *      tags={"Courses"},
     *      security={{"bearer_token":{}}},
     *      summary="Get course information",
     *      description="Returns course data",
     *      @OA\Parameter(
     *          name="id",
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
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Course 1"),
     *                  @OA\Property(property="start_date", type="string", example="2022-01-01"),
     *                  @OA\Property(property="end_date", type="string", example="2022-12-31"),
     *                  @OA\Property(property="type", type="string", example="ONLINE"),
     *                  @OA\Property(
     *                      property="schedules",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="day", type="string", example="LUNES"),
     *                          @OA\Property(property="start_hour", type="integer", example=8),
     *                          @OA\Property(property="end_hour", type="integer", example=10),
     *                          @OA\Property(property="course_id", type="integer", example=1),
     *                      ),
     *                  ),
     *              ),
     *              @OA\Property(property="message", type="string", example="Course retrieved successfully"),
     *           ),
     *      )
     * )
     */
    public function show(string $id, Request $request)
    {
        try {

            $course = Course::with('schedules')
            ->where('id', $id)
            ->where('user_id', $request->user_id)
            ->first();


            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            return response()->json([
                'data' => $course,
                'message' => 'Course retrieved successfully',
            ]);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    /**
     * @OA\Put(
     *      path="/api/courses/{id}",
     *      operationId="updateCourse",
     *      tags={"Courses"},
     *      security={{"bearer_token":{}}},
     *      summary="Update existing course",
     *      description="Returns updated course data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Course id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","start_date","end_date","type"},
     *              @OA\Property(property="name", type="string", format="text", example="Course 1"),
     *              @OA\Property(property="start_date", type="string", format="date", example="2022-01-01"),
     *              @OA\Property(property="end_date", type="string", format="date", example="2022-12-31"),
     *              @OA\Property(property="type", type="string", format="text", example="ONLINE")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Course 1"),
     *                  @OA\Property(property="start_date", type="string", example="2022-01-01"),
     *                  @OA\Property(property="end_date", type="string", example="2022-12-31"),
     *                  @OA\Property(property="type", type="string", example="ONLINE"),
     *              ),
     *              @OA\Property(property="message", type="string", example="Course updated successfully"),
     *          )
     *      )
     * )
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

            $course = Course::where('id', $id)
                ->where('user_id', $request->user_id)
                ->first();
            $course->name = $request->name;
            $course->start_date = $request->start_date;
            $course->end_date = $request->end_date;
            $course->type = $request->type;
            $course->save();
            return response()->json(['data' => $course, 'message' => 'Course updated successfully']);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/courses/{id}",
     *      operationId="deleteCourse",
     *      tags={"Courses"},
     *      security={{"bearer_token":{}}},
     *      summary="Delete existing course",
     *      description="Returns deleted course data",
     *      @OA\Parameter(
     *          name="id",
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
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Course 1"),
     *                  @OA\Property(property="start_date", type="string", example="2022-01-01"),
     *                  @OA\Property(property="end_date", type="string", example="2022-12-31"),
     *                  @OA\Property(property="type", type="string", example="ONLINE"),
     *              ),
     *              @OA\Property(property="message", type="string", example="Course deleted successfully"),
     *          )
     *      )
     * )
     */
    public function destroy(string $id, Request $request)
    {
        try {
            $course = Course::where('id', $id)
                ->where('user_id', $request->user_id)
                ->first();

            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            $studentsInCourse = CourseStudent::where('course_id', $id)->count();

            if ($studentsInCourse > 0) {
                return response()->json(['error' => 'Course has students, cannot be deleted'], 400);
            }

            Schedule::where('course_id', $id)->delete();

            $course->delete();
            return response()->json(['data' => $course, 'message' => 'Course deleted successfully']);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }
}
