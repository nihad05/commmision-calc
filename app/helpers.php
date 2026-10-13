<?php

declare(strict_types=1);

use App\Http\Responses\ApiResponse;

if (! function_exists('api')) {
    function api(): ApiResponse
    {
        return new ApiResponse;
    }
}
