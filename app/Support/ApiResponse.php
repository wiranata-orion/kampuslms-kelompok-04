<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponse
{
    /** @param class-string<JsonResource> $resourceClass */
    public static function collection(LengthAwarePaginator $paginator, string $resourceClass, Request $request): JsonResponse
    {
        return response()->json([
            'data' => $paginator->getCollection()
                ->map(fn ($item) => (new $resourceClass($item))->resolve($request))
                ->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}