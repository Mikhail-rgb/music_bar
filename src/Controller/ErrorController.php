<?php
declare(strict_types=1);


namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class ErrorController
{
    public function error(Throwable $exception): JsonResponse
    {
        return new JsonResponse([
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ]
        );
    }
}