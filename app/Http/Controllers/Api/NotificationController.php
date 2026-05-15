<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest('id')
            ->limit(12)
            ->get();

        return response()->json([
            'notifications' => $notifications->map(fn (UserNotification $notification) => $this->serializeNotification($notification))->values(),
            'unread_count' => $request->user()->notifications()->whereNull('read_at')->count(),
        ]);
    }

    public function markRead(Request $request, UserNotification $notification): JsonResponse
    {
        abort_unless($notification->user_id === $request->user()->id, JsonResponse::HTTP_FORBIDDEN, 'Forbidden.');

        if (! $notification->read_at) {
            $notification->forceFill([
                'read_at' => now(),
            ])->save();
        }

        return response()->json([
            'message' => 'Paziņojums atzīmēts kā izlasīts.',
            'notification' => $this->serializeNotification($notification->fresh()),
        ]);
    }

    protected function serializeNotification(UserNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'message' => $notification->message,
            'link' => $notification->link,
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at,
        ];
    }
}
