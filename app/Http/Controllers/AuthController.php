<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Info(
 *     title="Laravel JWT Auth API",
 *     version="1.0.0",
 *     description="API documentation for authentication using JWT in Laravel.",
 *     @OA\Contact(
 *         email="your-email@example.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Localhost API Server"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for Authentication"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1...")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error")
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'), 201);
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Login user and get JWT token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=203,
     *         description="User authenticated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1...")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=500, description="Could not create token")
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = auth()->user();
            $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);

            return response()->json(compact('token'), 203);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/user",
     *     summary="Get authenticated user details",
     *     security={{ "bearerAuth":{} }},
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response=200,
     *         description="Authenticated user details",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid token"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function getUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }

        return response()->json(compact('user'));
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     summary="Logout user and invalidate token",
     *     security={{ "bearerAuth":{} }},
     *     tags={"Authentication"},
     *     @OA\Response(response=200, description="Successfully logged out"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out']);
    }
}
