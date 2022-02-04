<?php

namespace PhpJwtHelper;

use Illuminate\Support\Facades\Validator as Validate;
use Illuminate\Support\Str;

class Validator
{
    public static function doValidate($rules, $needle = null) {
        if ($needle == null) $needle = request()->all();

        $validator = Validate::make($needle, $rules);

        if ($validator->fails()) {
            $failed_message = [];
            foreach($validator->errors()->getMessages() as $key => $messages) {
                foreach ($messages as $message_key => $message) {
                    if (Str::contains($key, '.')) {
                        $keys = explode('.', $key);
                        $new_key = $keys[0] . ' ' . $keys[2] . ' in index ' . $keys[1];
                        $message = str_replace($key, $new_key, $message);
                    }
                    $failed_message[] = str_replace(str_replace('_', ' ', $key), "'" . $key . "'", $message);
                }
            }

            return self::validationFailedResponse(implode(', ', $failed_message));
        }
    }

    public static function validationFailedResponse($message)
    {
        return response()->json(self::responseObject(422, 'Unprocessable Entity: ' . $message), 422);
    }

    public static function successResponse($message, $data = null, $nullable = true)
    {
        return response()->json(self::responseObject(200, $message, $data, $nullable), 200);
    }

    public static function failedResponse($message, $code = 400, $log = false, $data = null)
    {
        $response = response()->json(self::responseObject($code, $message, $data), $code);
        if ($log) \Log::info($response->getContent());

        return $response;
    }

    private static function responseObject($code, $message, $data = null, $nullable = true)
    {
        if($data instanceof Illuminate\Database\Eloquent\Model) $data = $data->toArray();
        $data = json_decode(json_encode($data));
        if($data == "") $data = (object)null;

        $response_object = self::generalCast([
            'response' => [
                'code' => $code,
                'message' => $message,
                'ip' => self::getClientOriginalIp(),
                'host_timestamp' => date('Y-m-d H:i:s')
            ],
            'data' => $data
        ]);
        $response_object['response']['latency'] = microtime(true) - LARAVEL_START;

        return $response_object;
    }

    private static function getClientOriginalIp(){
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }

        return request()->ip();
    }

    public static function generalCast($array)
    {
        $array = self::nullToEmpty($array);
        $array = self::numericToInteger($array);

        return $array;
    }

    public static function nullToEmpty($array)
    {
        foreach($array as $key => $value) {
            if(is_object($value) || is_array($value)) $value = self::nullToEmpty($value);

            if(!is_null($value)) continue;

            if(is_array($array)) $array[$key] = '';

            if(is_object($array)) $array->$key = '';
        }
        return $array;
    }

    public static function numericToInteger($array)
    {
        foreach ($array as $key => $value) {
            if (!is_object($value)) {
                if (is_array($value) && $key != 'request') {
                    $array[$key] = self::numericToInteger($value);
                } else {
                    $int = (int)$value;
                    if (is_numeric($value) && strlen($value) === strlen($int)) {
                        $array[$key] = $int;
                    }
                }
            }
        }

        return $array;
    }
}
