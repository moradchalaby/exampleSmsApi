<?php
function send_sms($number, $message, $origin = 'SMS',$sms_id = null)
{

    if($sms_id == null){
        $sms = new App\Models\Sms();
        $sms->user_id = auth()->user()->id;
        $sms->numbers = $number;
        $sms->message = $message;
        $sms->origin = $origin;
        $sms->request_time = date('Y-m-d H:i:s');
        $sms->save();
        $sms_id = $sms->id;
    }
    $mesaj = trim($message);

    $query = [

        'apikey' => env('SMS_API_KEY'),
        'type' => 'single',
        'origin' => $origin,
        'message' => $mesaj,
        'numbers' => $number,
        "lang" => "en",
        "flashsms" => "0",
    ];

    $json_data = json_encode($query);


    $url = env('SMS_SEND_URL');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    $output = curl_exec($ch);
    curl_close($ch);

    $sms = App\Models\Sms::find($sms_id);
    $sms->request = json_encode($query);
    $sms->send_time = date('Y-m-d H:i:s');
    $sms->status = json_decode($output ?? '[]', true)['status'] ?? 'failed';
    $sms->response = json_encode($output);
    $sms->save();


    return [

        'status' => json_decode($output ?? '[]', true)['status'] ? 'delivered' : 'failed',

    ];

}
