<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Support\DashboardCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                //blok user selalu dibaca langsung (data pribadi, tidak di-cache)
                'user' => (new UserResource($user))->resolve($request),

                'stats' => DashboardCache::stats($user),
            ],
        ]);
    }
}
