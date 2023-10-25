<?php

class ErrorHandler
{
    public static function handleException(Throwable $ex) : void
    {
        http_response_code(500);
        echo json_encode([
            "code" => $ex->getCode(),
            "file" => $ex->getFile(),
            "line" => $ex->getLine(),
            "message" => $ex->getMessage()
        ]);
    }
}
// [] {}
?>