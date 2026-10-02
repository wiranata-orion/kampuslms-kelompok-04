<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
	public function index(Request $request)
	{
		$notifications = $request->user()->notifications()->latest()->paginate(15);

		return ApiResponse::collection($notifications, NotificationResource::class, $request);
	}

	public function read(Request $request, string $id)
	{
		$notification = $request->user()->notifications()->whereKey($id)->firstOrFail();
		$notification->markAsRead();

		return response()->json([
			'data' => (new NotificationResource($notification))->resolve($request),
		]);
	}
}
