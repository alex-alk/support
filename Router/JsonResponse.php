<?php

namespace Support\Router;

use Fig\Http\Message\StatusCodeInterface;

class JsonResponse implements StatusCodeInterface
{
    private string $json;
    public function __construct(array $data)
    {
        $this->json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
    
    public function __toString()
    {
        header('Content-Type: application/json');
        return $this->json;
    }
    
    public function getJson(): string
    {
        return $this->json;
    }
}
