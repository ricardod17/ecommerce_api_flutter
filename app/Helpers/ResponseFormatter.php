<?php

namespace App\Helpers;

/**
 * Format response.
 */
class ResponseFormatter
{
    /**
     * API Response
     *
     * @var array
     */
    protected static $response = [
        'result' => [
            'code' => 200,
            'success' => 'true',
            'message' => null,
        ],
    ];

    /**
     * Give success response.
     */
    public static function success($data = null, $message = null)
    {
        self::$response['result']['success'] = true;
        self::$response['result']['message'] = $message;
        self::$response['data'] = $data;

        return response()->json(self::$response, self::$response['result']['code']);
    }

    /**
     * Give error response.
     */
    public static function error($message = null, $code = 400)
    {
        self::$response['result']['success'] = false;
        self::$response['result']['code'] = $code;
        self::$response['result']['message'] = $message;

        return response()->json(self::$response, self::$response['result']['code']);
    }

    public static function errorArray($message = [], $code = 400)
    {
        self::$response['result']['success'] = false;
        self::$response['result']['code'] = $code;
        self::$response['result']['message'] = $message;

        return response()->json(self::$response, self::$response['result']['code']);
    }
}