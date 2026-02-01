<?php


namespace App\Api\Responder;

use Psr\Log\LoggerInterface;

class ApiResponseBuilder{

    public static ?LoggerInterface $logger;

    public static function init(LoggerInterface $logger){
        self::$logger = $logger;
    }

    public static function logError(\Throwable $exception)
    {
        if(self::$logger){
            self::$logger->error(
                "Caught Exception: ". $exception->getMessage(), 
                [   
                    'exception'=>$exception,
                ]
            );
        }
    }

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

    public static function error(string $message = '', ?\Throwable $exception = null): array
    {
        if($exception){
            self::logError($exception);
        }
        return [
            'status'  => 'error',
            'message' => $message,
            'data'    => []
        ];
    }
}