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
        'code' => 200,
        'success' => 'true',
        'message' => null
    ];

    /**
     * Give success response.
     */
    public static function success($data = null, $message = null)
    {
        self::$response['success'] = true;
        self::$response['message'] = $message;
        self::$response['data'] = $data;

        return response()->json(self::$response, self::$response['code']);
    }

    /**
     * Give error response.
     */
    public static function error($message = null, $code = 400)
    {
        self::$response['success'] = false;
        self::$response['code'] = $code;
        self::$response['message'] = $message;

        return response()->json(self::$response, self::$response['code']);
    }

    public static function errorArray($message = [], $code = 400)
    {
        self::$response['success'] = false;
        self::$response['code'] = $code;
        self::$response['message'] = $message;

        return response()->json(self::$response, self::$response['code']);
    }
}