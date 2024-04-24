<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Teacher API",
 *      description="Teacher API",
 * )
 *
 */

class UserController
{

    /**
     * @OA\Post(
     *      path="/api/register",
     *      summary="Register a user",
     *      description="Register a user",
     *      operationId="register",
     *      tags={"Teacher"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="User registration",
     *          @OA\JsonContent(
     *              required={"name","lastname","email","password"},
     *              @OA\Property(property="name", type="string", format="name", example="John"),
     *              @OA\Property(property="lastname", type="string", format="lastname", example="Doe"),
     *              @OA\Property(property="email", type="string", format="email", example="email@emial.com"),
     *              @OA\Property(property="password", type="string", format="password", example="xxxxxxxx@xxx"),
     *          )
     *      ),
     *      @OA\Response(
     *        response=200,
     *        description="User created successfully",
     *        @OA\JsonContent(
     *          @OA\Property(
     *              property="data",
     *              type="object",
     *              @OA\Property(property="name", type="string", example="John"),
     *              @OA\Property(property="lastname", type="string", example="Doe"),
     *              @OA\Property(property="email", type="string", example="email@email.com"),
     *              @OA\Property(property="created_at", type="string", example="2021-09-01T00:00:00.000000Z"),
     *              @OA\Property(property="updated_at", type="string", example="2021-09-01T00:00:00.000000Z"),
     *              @OA\Property(property="id", type="integer", example=1),
     *          ),
     *          @OA\Property(property="message", type="string", example="User created successfully"),
     *        )
     *      ),
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:100',
                'lastname' => 'required|max:100',
                'email' => 'required|email|unique:users',
                'password' => 'required|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $user = new User();
            $user->name = $request->name;
            $user->lastname = $request->lastname;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();
            unset($user['password']);
            return response()->json(['data' => $user, 'message' => 'User created successfully']);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/login",
     *      summary="Login a user",
     *      description="Login a user",
     *      operationId="login",
     *      tags={"Teacher"},
     *      @OA\RequestBody(
     *      required=true,
     *      description="User login",
     *          @OA\JsonContent(
     *              required={"email","password"},
     *              @OA\Property(property="email", type="string", format="email", example="email@email.com"),
     *              @OA\Property(property="password", type="string", format="password", example="xxxxxxxx@xxx"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User logged in successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="string", example="1234567890"),
     *              @OA\Property(property="message", type="string", example="User logged in successfully"),
     *          )
     *      ),
     * )
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $token = $user->createToken('auth_token',[],
                now()->addDays(1)
            )->plainTextToken;
            return response()->json(['data' => $token, 'message' => 'User logged in successfully']);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/logout",
     *      summary="Logout a user",
     *      description="Logout a user",
     *      operationId="logout",
     *      tags={"Teacher"},
     *      security={{"bearer_token":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="User logged out successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="User logged out successfully"),
     *          )
     *      ),
     * )
     */
    public function logout(Request $request)
    {
        $user_id = $request->user_id;
        $user = User::find($user_id);
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $user->tokens()->where('token', hash('sha256', $token))->delete();
        return response()->json(['message' => 'User logged out successfully']);
    }
}
