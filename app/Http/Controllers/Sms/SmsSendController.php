<?php

namespace App\Http\Controllers\Sms;

use App\Http\Controllers\Controller;
use App\Jobs\SendSms;
use App\Models\Sms;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class SmsSendController extends Controller
{
    //
    /**
     * @OA\POST(
     *     path="/send-sms",
     *     summary="Send SMS",
     *     description="Send SMS endpoint.",
     *     operationId="sendSms",
     *     tags={"sms"},
     *      security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="phone",
     *     in="query",
     *     description="Phone number",
     *     required=true,
     *     @OA\Schema(
     *     type="string",
     *     format="string",
     *     example="905*********",
     *     ),
     *     ),
     *   @OA\Parameter(
     *     name="origin",
     *     in="query",
     *     description="SMS Sender",
     *     required=true,
     *     @OA\Schema(
     *     type="string",
     *     format="string",
     *     example="SMS Sender",
     *     ),
     *     ),
     *     @OA\Parameter(
     *     name="message",
     *     in="query",
     *     description="SMS Message",
     *     required=true,
     *     @OA\Schema(
     *     type="string",
     *     format="string",
     *     example="This is message.",
     *     ),
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
    public function sendSms(Request $request): JsonResponse
    {
        $validated =  Validator::make($request->all(), [
            'phone' => 'required',
            'message' => 'required',
            'origin' => 'required',
        ], [
            'phone.required' => 'Phone number is required.',
            'message.required' => 'Message is required.',
            'origin.required' => 'Origin is required.',
        ]);
        $validated = $validated->validated();

        $sms = new Sms();
        $sms->user_id = Auth::id();
        $sms->phone = $request->phone;
        $sms->message = $request->message;
        $sms->origin = $request->origin;
        $sms->request_time = date('Y-m-d H:i:s');

        $data = RateLimiter::hit('sendSms');// this will increase the counter by 1 and return the number of attempts

        if (RateLimiter::attempts('sendSms') >= env('SMS_RATE_LIMIT', 500)) {
            if (Cache::has('RateTime') || Cache::get('RateTime') != null) {
                $time = Cache::get('RateTime');
               if((new Carbon)->diffInSeconds($time) > 60) RateLimiter::clear('sendSms'); // if 60 seconds passed, clear the rate limiter
            }

            Cache::add('RateTime', Carbon::now(), 60);// this will be removed after 60 seconds
            $sms->status = 'pending';
            $sms->save();

            SendSms::dispatch($request->phone, $request->message, $request->origin, $sms->id);

            return response()->json([
                'success' => true,
                'message' => 'SMS record successfully.',
                'sms_status' => 'pending',

                'data' => [
                    'phone' => $data,
                    'message' => $request->message,
                ],
                200
            ]);
        } else {
            $sms->save();
            SendSms::dispatchSync($request->phone, $request->message, $request->title, $sms->id);
            return response()->json([
                'success' => true,
                'message' => 'SMS sent successfully.',

                'sms_status' => Sms::find($sms->id)->status,
                'data' => [
                    'phone' => $data,
                    'message' => $request->message,
                ],
                200]);


        }

    }


}
