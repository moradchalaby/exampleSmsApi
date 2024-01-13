<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/register",
     *     summary="Adds a new user - with oneOf examples",
     *     description="Adds a new user",
     *     operationId="addUser",
     *     tags={"auth"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="username",
     *                     type="string"
     *                 ),
     *     @OA\Property(
     *                property="email",
     *           type="string"
     *      ),
     *            @OA\Property(
     *           property="password",
     *      type="string"
     * ),
     *     @OA\Property(
     *           property="password_confirmation",
     *      type="string"
     * ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *     @OA\Property(
     *                     property="address",
     *                     oneOf={
     *                     	   @OA\Schema(type="string"),
     *                     	   @OA\Schema(type="integer"),
     *                     }
     *                 ),
     *                 example={
     *                "username": "user234",
     *                "email": "user@example.com",
     *                "password": "password1",
     *                "password_confirmation": "password1",
     *                "name": "First Last",
     *                "phone": "1234567890",
     *                "address": "1234 Main St"
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(type="boolean")
     *             },
     *             @OA\Examples(example="result", value={
     *                "success": true,
     *                "message": "The user named First Last was successfully created",
     *                "status": "201",
     *                "statusText": "Created"
     *                }, summary="An result object."),
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {

        $request->validate(['name' => ['required', 'string', 'max:255'], 'username' => ['required', 'string', 'lowercase', 'max:255', 'unique:' . User::class], 'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class], 'address' => ['required', 'string', 'max:255'], 'phone' => ['required', 'string', 'max:255', 'unique:' . User::class], 'password' => ['required', 'confirmed', Rules\Password::defaults()],]);

        $user = User::create(['name' => $request->name, 'email' => $request->email, 'username' => $request->username, 'address' => $request->address, 'phone' => $request->phone, 'address' => $request->address, 'password' => Hash::make($request->password),]);

        event(new Registered($user));

        //Auth::login($user);

        return response()->json([

            'success' => true, 'message' => "The user named " . $request->name . " was successfully created",

            'status' => '201', 'statusText' => 'Created',], Response::HTTP_CREATED);
    }
}
