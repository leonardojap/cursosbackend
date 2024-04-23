<?php

namespace App\Http\Controllers;

use App\Models\schedules;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class schedulesController
{


    /**
     * Store a newly created resource in storage.
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

            $schedule = new schedules();
            $schedule->day = $request->day;
            $schedule->start_hour = $request->start_hour;
            $schedule->end_hour = $request->end_hour;
            $schedule->course_id = $request->course_id;
            $schedule->save();
            return response()->json(['schedule' => $schedule, 'message' => 'Schedule created successfully']);
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
                'day' => 'required|in:LUNES,MARTES,MIERCOLES,JUEVES,VIERNES,SABADO,DOMINGO',
                'start_hour' => 'required|integer|min:0|max:23',
                'course_id' => 'required',
                'end_hour' => 'required|integer|min:0|max:23'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $schedule = schedules::find($id);
            $schedule->day = $request->day;
            $schedule->start_hour = $request->start_hour;
            $schedule->end_hour = $request->end_hour;
            $schedule->course_id = $request->course_id;
            $schedule->save();
            return response()->json(['schedule' => $schedule, 'message' => 'Schedule updated successfully']);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $schedule = schedules::find($id);

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
