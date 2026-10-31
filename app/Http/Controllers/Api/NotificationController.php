<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Tampilkan seluruh riwayat notifikasi user melalui auth()->user()->notifications.
     */
    public function index(Request $request)
    {
        $notifications = auth()->user()->notifications()->latest()->paginate($request->integer('per_page', 15));

        return ApiResponse::collection($notifications, NotificationResource::class, $request);
    }

	public function read(Request $request, string $id)
	{
		$notification = $request->user()->notifications()->whereKey($id)->firstOrFail();
		$notification->markAsRead();

    /**
     * Ambil detail satu notifikasi dengan validasi kepemilikan.
     */
    public function show(Request $request, string $id)
    {
        $notification = DatabaseNotification::findOrFail($id);

	public function readAll(Request $request)
	{
		$request->user()->unreadNotifications()->update(['read_at' => now()]);

		return response()->json(['data' => ['message' => 'Semua notifikasi telah ditandai dibaca.']]);
	}
}
