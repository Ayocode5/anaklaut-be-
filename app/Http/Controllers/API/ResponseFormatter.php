<?php

namespace App\Http\Controllers\API;

class ResponseFormatter {

    protected static $response = [
        "meta" => [
            "status" => "success",
            "message" => null,
            "code" => 200
        ],
        "data" => null
    ];

    public static function success($data, $code = 200, $message = "success") {
        self::$response["data"] = $data;
        self::$response["meta"]["message"] = $message;
        self::$response["meta"]["code"] = $code;

        return response()->json(self::$response, $code);
    }
    
    public static function error($data = null, $message = null, $code = 400) {
        self::$response["meta"] = [
            "status" => "error",
            "message" => $message,
            "code" => $code
        ];
        self::$response["data"] = $data;
    
        return response()->json(self::$response, $code);

    }
}