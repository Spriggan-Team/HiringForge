<?php


namespace App\Api\Responder;


class ApiResponseBuilder{
    public static function success(mixed $data, string $message = ""): array
    {
        return [
            "data"   => $data,
            "message"=> $message,
            "status" => 'success'
        ];
    }

    public static function notice(string $message): array
    {
        return [
            "message" => $message
        ];
    }

    public static function error(string $message = ''): array
    {
        return [
            'status'  => 'error',
            'message' => $message,
            'data'    => []
        ];
    }
}