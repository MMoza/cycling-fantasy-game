<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Persistence\Models\NotificationModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = NotificationModel::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'description' => $notification->description,
                'data' => $notification->data,
                'created_at' => $notification->created_at->toIso8601String(),
            ]);

        return response()->json(['notifications' => $notifications]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = NotificationModel::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $notification) {
            return response()->json(['error' => 'Notificación no encontrada'], 404);
        }

        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
