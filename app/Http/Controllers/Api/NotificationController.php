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

    /**
     * Ambil jumlah pesan belum dibaca via auth()->user()->unreadNotifications->count().
     */
    public function unreadCount(Request $request)
    {
        $count = $request->user()->unreadNotifications()->count();

        return response()->json([
            'data' => [
                'count' => $count,
                'unread_count' => $count,
            ],
        ]);
    }

		return response()->json([
			'data' => (new NotificationResource($notification))->resolve($request),
		]);
	}

        // Validasi kepemilikan: ID penerima harus cocok dengan ID user yang sedang login
        abort_if(
            (string) $notification->notifiable_id !== (string) $request->user()->getKey()
                || $notification->notifiable_type !== $request->user()->getMorphClass(),
            403,
            'Anda tidak memiliki akses ke notifikasi ini.'
        );

        return response()->json([
            'data' => (new NotificationResource($notification))->resolve($request),
        ]);
    }

    /**
     * Tandai notifikasi sebagai sudah dibaca menggunakan method markAsRead().
     */
    public function read(Request $request, string $id)
    {
        $notification = DatabaseNotification::findOrFail($id);

        // Validasi kepemilikan: ID penerima harus cocok dengan ID user yang sedang login
        abort_if(
            (string) $notification->notifiable_id !== (string) $request->user()->getKey()
                || $notification->notifiable_type !== $request->user()->getMorphClass(),
            403,
            'Anda tidak memiliki akses ke notifikasi ini.'
        );

        $notification->markAsRead();

        return response()->json([
            'data' => (new NotificationResource($notification))->resolve($request),
        ]);
    }

    /**
     * Tandai seluruh notifikasi belum dibaca milik user sebagai sudah dibaca.
     */
    public function readAll(Request $request)
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json([
            'data' => [
                'message' => 'Semua notifikasi telah ditandai dibaca.',
            ],
        ]);
    }
}
