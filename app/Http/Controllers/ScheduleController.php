<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class ScheduleController
{


    /**
     * @OA\Post(
     *      path="/api/schedules",
     *      summary="Create a schedule",
     *      description="Create a schedule",
     *      operationId="store",
     *      tags={"Schedule"},
     *      security={{"bearer_token":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Schedule creation",
     *          @OA\JsonContent(
     *              required={"day","start_hour","end_hour","course_id"},
     *              @OA\Property(property="day", type="string", format="day", example="LUNES"),
     *              @OA\Property(property="start_hour", type="integer", format="start_hour", example=8),
     *              @OA\Property(property="end_hour", type="integer", format="end_hour", example=10),
     *              @OA\Property(property="course_id", type="integer", format="course_id", example=1),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Schedule created successfully",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="day", type="string", example="LUNES"),
     *                  @OA\Property(property="start_hour", type="integer", example=8),
     *                  @OA\Property(property="end_hour", type="integer", example=10),
     *                  @OA\Property(property="course_id", type="integer", example=1),
     *                  @OA\Property(property="created_at", type="string", example="2021-09-01T00:00:00.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", example="2021-09-01T00:00:00.000000Z"),
     *                  @OA\Property(property="id", type="integer", example=1),
     *              ),
     *              @OA\Property(property="message", type="string", example="Schedule created successfully"),
     *          ),
     *      ),
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'day' => 'required|in:LUNES,MARTES,MIERCOLES,JUEVES,VIERNES,SABADO,DOMINGO',
                'start_hour' => 'required|integer|min:0|max:23',
                'course_id' => 'required',
                'end_hour' => 'required|integer|min:0|max:23'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $schedule = new Schedule();
            $schedule->day = $request->day;
            $schedule->start_hour = $request->start_hour;
            $schedule->end_hour = $request->end_hour;
            $schedule->course_id = $request->course_id;
            $schedule->save();
            return response()->json(['data' => $schedule, 'message' => 'Schedule created successfully']);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    /**
     * @OA\Put(
     *      path="/api/schedules/{id}",
     *      summary="Update a schedule",
     *      description="Update a schedule",
     *      operationId="update",
     *      tags={"Schedule"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Schedule ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Schedule update",
     *          @OA\JsonContent(
     *              required={"day","start_hour","end_hour","course_id"},
     *              @OA\Property(property="day", type="string", format="day", example="LUNES"),
     *              @OA\Property(property="start_hour", type="integer", format="start_hour", example=8),
     *              @OA\Property(property="end_hour", type="integer", format="end_hour", example=10),
     *              @OA\Property(property="course_id", type="integer", format="course_id", example=1),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Schedule updated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="day", type="string", example="LUNES"),
     *                  @OA\Property(property="start_hour", type="integer", example=8),
     *                  @OA\Property(property="end_hour", type="integer", example=10),
     *                  @OA\Property(property="course_id", type="integer", example=1),
     *                  @OA\Property(property="created_at", type="string", example="2021-09-01T00:00:00.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", example="2021-09-01T00:00:00.000000Z"),
     *                  @OA\Property(property="id", type="integer", example=1),
     *              ),
     *              @OA\Property(property="message", type="string", example="Schedule updated successfully"),
     *         ),
     *     ),
     * )
     */
    public function update(string $id, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'day' => 'required|in:LUNES,MARTES,MIERCOLES,JUEVES,VIERNES,SABADO,DOMINGO',
                'start_hour' => 'required|integer|min:0|max:23',
                'course_id' => 'required',
                'end_hour' => 'required|integer|min:0|max:23'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $schedule = Schedule::find($id);
            $schedule->day = $request->day;
            $schedule->start_hour = $request->start_hour;
            $schedule->end_hour = $request->end_hour;
            $schedule->course_id = $request->course_id;
            $schedule->save();
            return response()->json(['data' => $schedule, 'message' => 'Schedule updated successfully']);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/schedules/{id}",
     *      summary="Delete a schedule",
     *      description="Delete a schedule",
     *      operationId="destroy",
     *      tags={"Schedule"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Schedule ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Schedule deleted successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Schedule deleted successfully"),
     *          ),
     *      ),
     * )
     */
    public function destroy(string $id)
    {
        try {
            $schedule = Schedule::find($id);

            if (!$schedule) {
                return response()->json(['error' => 'Schedule not found'], 404);
            }

            $schedule->delete();
            return response()->json(['message' => 'Schedule deleted successfully']);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }
}
