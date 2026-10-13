<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

final readonly class ApiResponse
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function success(mixed $data = null, array $meta = [], int $status = 200): JsonResponse
    {
        $body = ['success' => true, 'data' => $data];

        if ($meta !== []) {
            $body['meta'] = $meta;
        }

        return new JsonResponse($body, $status);
    }

    /**
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     */
    public function paginated(LengthAwarePaginator $paginator): JsonResponse
    {
        return $this->success($paginator->items(), [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $errors
     */
    public function error(string $message, int $status, array $errors = []): JsonResponse
    {
        $body = ['success' => false, 'message' => $message];

        if ($errors !== []) {
            $body['errors'] = $errors;
        }

        return new JsonResponse($body, $status);
    }
}
