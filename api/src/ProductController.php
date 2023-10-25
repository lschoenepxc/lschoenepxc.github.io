<?php

class ProductController
{
    public function processRequest(string $method, ?string $id): void
    {
        if ($id) {
            $this->processResourceRequest($method, $id);
        } else {
            $this->processCollectionRequest($method);
        }
    }

    private function processResourceRequest(string $method, string $id): void
    {
        // get http://localhost:3000/api/products/12
        echo json_encode(["id" => $id]);
    }
    private function processCollectionRequest(string $method): void
    {
        switch ($method) {
            // get http://localhost:3000/api/products
            case 'GET':
                echo json_encode(["method" => "GET"]);
                break;
            case 'POST':
                // post http://localhost:3000/api/products?name="A new Product"
                // Postman: body -> form and then add data
                // gives us associative array
                $data = $_POST;
                var_dump($data);
            default:
                break;
        }
    }
}
// [] {}
?>
