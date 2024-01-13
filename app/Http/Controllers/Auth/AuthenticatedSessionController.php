<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Login a user",
     *     description="Login a user",
     *     operationId="loginUser",
     *     tags={"auth"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="username",
     *                     type="string"
     *                 ),
     *
     *            @OA\Property(
     *           property="password",
     *      type="string"
     * ),
     *        example={
     *                "username": "user234",
     *                "password": "password1"
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(type="boolean")
     *             },
     *             @OA\Examples(example="result", value={
     *                                   "success": true,
     *                                   "message": "User logged in successfully.",
     *                                   "data": {
     *                                   "name": "First Last",
     *                                   "email": "user@example.com",
     *                                   "username": "user234",
     *                                   "address": "1234 Main St",
     *                                   "phone": "1234567890"
     *                                   },
     *                                   "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXBpL3YxL2xvZ2luIiwiaWF0IjoxNzA1MTA0Njc3LCJleHAiOjE3MDUxMDgyNzcsIm5iZiI6MTcwNTEwNDY3NywianRpIjoiOGtTR1JLckRLYXFJaHlYbyIsInN1YiI6IjgiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.R_TzWgGlxPHY-tjIN1255GLWB7qdvRY9CZe39p2rixA",
     *                                   "token_type": "bearer",
     *                                   "expires_in": 3600
     *                                   }, summary="An result object."),
     *         )
     *     )
     * )
     */
    public function store(LoginRequest $request): JsonResponse
    {
      $request->authenticate();
      $token = auth()->refresh();

        return $this->respondWithToken($token);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }

    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'User logged in successfully.',
            'data' => auth()->user()->only(['name', 'email', 'username', 'address', 'phone']),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
