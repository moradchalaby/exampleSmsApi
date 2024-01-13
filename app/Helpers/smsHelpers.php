<?php
function send_sms($phone, $message, $origin = 'SMS', $sms_id = null)
{

    $mesaj = trim($message);

    $query = [

        'apikey' => env('SMS_API_KEY'),
        'type' => 'single',
        'origin' => $origin,
        'message' => $mesaj,
        'numbers' => $phone,
        "lang" => "en",
        "flashsms" => "0",
    ];

    $json_data = json_encode($query);

    if (env('APP_DEBUG') == true) {
        $output = true;
    } else {
        $url = env('SMS_SEND_URL');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $output = curl_exec($ch);
        curl_close($ch);
    }


    $sms = App\Models\Sms::find($sms_id);
    $sms->request = json_encode($query);
    $sms->send_time = date('Y-m-d H:i:s');
    $sms->status = $output ? 'delivered' : 'failed';
    $sms->response = json_encode($output);
    $sms->save();


    return [

        'status' => $output ? 'delivered' : 'failed',

    ];

}
