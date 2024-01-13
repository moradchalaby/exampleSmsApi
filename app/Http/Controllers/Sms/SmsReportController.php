<?php

namespace App\Http\Controllers\Sms;

use App\Http\Controllers\Controller;
use App\Models\Sms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmsReportController extends Controller
{

    /**
     * @OA\Get(
     *     path="/sms-report",
     *     summary="SMS Report",
     *     description="SMS report endpoint.",
     *     operationId="reportSms",
     *     tags={"sms"},
     *      security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *     name="start_date",
     *     in="query",
     *     description="Start date",
     *     required=false,
     *     @OA\Schema(
     *     type="string",
     *     format="date",
     *     example="2021-01-01",
     *     ),
     *     ),
     *     @OA\Parameter(
     *     name="end_date",
     *     in="query",
     *     description="End date",
     *     required=false,
     *     @OA\Schema(
     *     type="string",
     *     format="date",
     *     example="2021-01-01",
     *     ),
     *     ),
     *     @OA\Parameter(
     *     name="status",
     *     in="query",
     *     description="Status",
     *     required=false,
     *     @OA\Schema(
     *     type="string",
     *     format="string",
     *     enum={"pending","delivered","failed"},
     *     ),
     *
     *     ),
     *     @OA\Parameter(
     *     name="sort_by",
     *     in="query",
     *     description="Sort by",
     *     required=false,
     *     @OA\Schema(
     *     type="string",
     *     format="string",
     *     enum={"status","phone","send_time"},
     *     ),
     *     ),
     *     @OA\Parameter(
     *     name="sort_direction",
     *     in="query",
     *     description="Sort direction",
     *     required=false,
     *     @OA\Schema(
     *     type="string",
     *     format="string",
     *     enum={"asc","desc"},
     *     ),
     *     ),
     *
     *     @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page number",
     *     required=false,
     *     @OA\Schema(
     *     type="integer",
     *     format="integer",
     *     example="1",
     *     ),
     *     ),
     *     @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     description="Number of items per page",
     *     required=false,
     *     @OA\Schema(
     *     type="integer",
     *     format="integer",
     *     example="10",
     *     ),
     *     ),
     *
     *     @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *
     *     @OA\Property(
     *     property="success",
     *     type="boolean",
     *     example="true",
     *
     *     ),
     *     @OA\Property(
     *     property="message",
     *     type="string",
     *     example="The SMS list has been fetched successfully.",
     *     ),
     *     @OA\Property(
     *     property="data",
     *     type="object",
     *     example="{
     *     'id': 1,
     *     'phone': '905555555555',
     *     'message': 'Hello World',
     *     'origin': 'SMS',
     *     'status': 'delivered',
     *     'request': {
     *     rquestData
     *      },
     *     'response': {
     *     responseData
     *     },
     *     'send_time': '2021-01-01 00:00:00',
     *     'request_time': '2021-01-01 00:00:00',
     *     }",
     *     ),
     *     @OA\Property(
     *     property="meta",
     *     type="object",
     *     example="{
     *     'total': 1,
     *     'per_page': 10,
     *     'current_page': 1,
     *     'last_page': 1,
     *     'from': 1,
     *     'to': 1,
     *     }",
     *     ),
     *     ),
     *     ),
     *     @OA\Response(
     *     response=404,
     *     description="Not Found",
     *     @OA\JsonContent(
     *
     *     @OA\Property(
     *     property="success",
     *     type="boolean",
     *     example="false",
     *     ),
     *     @OA\Property(
     *     property="message",
     *     type="string",
     *     example="SMS List not found.",
     *     ),
     *     ),
     *     ),
     *
     * )
     */

    public function reportSms(Request $request)
    {
        $request->validate([
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
            'status' => 'string|in:pending,delivered,failed',
            'sort_by' => 'string|in:user_id,phone,send_time',
            'sort_direction' => 'string|in:asc,desc',
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
        ], smsReport_validation());

        $start_date = parse_date($request->input('start_date'));
        $end_date = parse_date($request->input('end_date'));

        $query = Sms::query();
        $query->where('user_id', Auth::id());
        $query->when($request->filled('start_date'), fn ($q) => $q->whereDate('send_time', '>=', $start_date));
        $query->when($request->filled('end_date'), fn ($q) => $q->whereDate('send_time', '<=', $end_date));
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')));

        $selectedFields = ['id', 'phone', 'status', 'send_time'];
        $query->select($selectedFields);

        $sortField = $request->input('sort_by', 'send_time','request_time');
        $sortDirection = $request->input('sort_direction', 'asc');

        $query->orderBy($sortField, $sortDirection);

        // Sayfalama
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        $smsList = $query->paginate($perPage, ['*'], 'page', $page);
if($smsList->count() > 0){
    return response()->json([
        'success' => true,
        'message' => 'The SMS list has been fetched successfully.',
        'data' => $smsList,
        'meta' => [
            'total' => $smsList->total(),
            'per_page' => $smsList->perPage(),
            'current_page' => $smsList->currentPage(),
            'last_page' => $smsList->lastPage(),
            'from' => $smsList->firstItem(),
            'to' => $smsList->lastItem(),
        ],
        200,
    ]);
}else{
    return response()->json([
        'success' => false,
        'message' => 'SMS List not found.',
        404,
    ]);
}

    }




    /**
     * @OA\Get(
     *    path="/sms-report-detail",
     *   summary="SMS Report Detail",
     *  description="SMS report detail endpoint.",
     * operationId="reportSmsDetail",
     * tags={"sms"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="query",
     * description="SMS ID",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * format="integer",
     * example="1",
     * ),
     * ),
     * @OA\Response(
     * response=200,
     * description="OK",
     * @OA\JsonContent(
     * @OA\Property(
     * property="success",
     * type="boolean",
     * example="true",
     * ),
     * @OA\Property(
     * property="message",
     * type="string",
     * example="SMS found.",
     * ),
     * @OA\Property(
     * property="data",
     * type="object",
     * example="{
     * 'id': 1,
     * 'phone': '905555555555',
     * 'message': 'Hello World',
     * 'origin': 'SMS',
     * 'status': 'delivered',
     * 'request': {
     * rquestData
     * },
     * 'response': {
     * responseData
     * },
     * 'send_time': '2021-01-01 00:00:00',
     * 'request_time': '2021-01-01 00:00:00',
     * }",
     * ),
     * ),
     * ),
     * @OA\Response(
     * response=404,
     * description="Not Found",
     * @OA\JsonContent(
     * @OA\Property(
     * property="success",
     * type="boolean",
     * example="false",
     * ),
     * @OA\Property(
     * property="message",
     * type="string",
     *  example="SMS not found.",
     * ),
     * ),
     * ),

     * security={
     * {"bearerAuth": {}}
     * }
     * )
     * )
     *
     */
    public function reportSmsDetail(Request $request)
    {

       $request->validate([
            'id' => 'required|integer',
        ], [
            'id.required' => 'SMS ID is required.',
            'id.integer' => 'SMS ID must be an integer.',
        ]);

        $sms = Sms::where('user_id',Auth::id())->find($request->id);
        $sms->select(
            'phone',
            'message',
            'origin',
            'status',
            'request',
            'response',
            'send_time',
            'request_time');
        if (!$sms) {
            return response()->json([
                'success' => false,
                'message' => 'SMS not found.',
                404,
            ]);
        }else{
            return response()->json([
                'success' => true,
                'message' => 'SMS found.',
                'data' => $sms,
                200,
            ]);
        }


    }

}
