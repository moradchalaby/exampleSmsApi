<?php

namespace App\Http\Controllers\Sms;

use App\Http\Controllers\Controller;
use App\Jobs\SendSms;
use App\Models\Sms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class SmsSendController extends Controller
{
    //
    /**
     * @OA\Get(
     *     path="/send-sms",
     *     summary="Send SMS",
     *     description="SMS sending endpoint.",
     *     operationId="sendSms",
     *     tags={"sms"},
     *      security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(type="boolean")
     *             },
     *             @OA\Examples(example="result", value={
     *                                   "success": true,
     *                              "message": "SMS sent successfully.",
     *                                "sms_status": "delivered",
     *                         "data": {
     *                        "phone": "905555555555",
     *                         "message": "Hello World",
     *                              }
     *                              }, summary="An result object."),
     *                                   }, summary="An result object."),
     *         )
     *     )
     * )
     */
    public function sendSms(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required',
            'message' => 'required',
            'origin' => 'required',
        ], [
            'phone.required' => 'Phone number is required.',
            'message.required' => 'Message is required.',
            'origin.required' => 'Origin is required.',
        ]);




        $sms = new Sms();
        $sms->user_id = auth()->user()->id;
        $sms->numbers = $request->phone;
        $sms->message = $request->message;
        $sms->origin = $request->origin;
        $sms->request_time = date('Y-m-d H:i:s');

        $data = RateLimiter::hit('sendSms');
        if (RateLimiter::attempts('sendSms') >= 5) {
            $sms->status = 'pending';
            $sms->save();
            $result = SendSms::dispatch($request->phone, $request->message, $request->origin, $sms->id);
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
            $result = SendSms::dispatchSync($request->phone, $request->message, $request->title, $sms->id);
            return response()->json([
                'success' => true,
                'message' => 'SMS sent successfully.',
                'sms_status' => json_encode($result['output'])['status'] ? 'delivered' : 'failed',
                'data' => [
                    'phone' => $data,
                    'message' => $request->message,
                ],
                200]);


        }

    }


}
