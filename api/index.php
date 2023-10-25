<?php
// [] {}

// use class types
declare(strict_types=1);

// load classes from src-folder
spl_autoload_register(function ($class) {
    require __DIR__ . "/src/$class.php";       
});

// set exception handler to our won calls
set_exception_handler("ErrorHandler::handleException");

// return as json
header("Content-type: application/json; charset=UTF-8");

// Get the request uri and split it into parts (http://localhost:3000/api/products -> Array ( [0] => [1] => api [2] => products ))
$parts = explode("/", $_SERVER["REQUEST_URI"]);

// print_r($parts);

// Only accept "products" as request uri, else set response code 404
if($parts[2] != "products") {
    http_response_code(404);
    exit;
}

// Set id from request uri, if given, else null
// http://localhost:3000/api/products/12 -> Array ( [0] => [1] => api [2] => products [3] => 12 )
$id = $parts[3] ?? null;

// Create an instance of the ProductController class
$controller = new ProductController;

// call the processRequest with function "GET" (from URI request) and id
$controller->processRequest($_SERVER["REQUEST_METHOD"], $id);
?>
