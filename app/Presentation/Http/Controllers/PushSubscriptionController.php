<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Persistence\Models\PushSubscriptionModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PushSubscriptionController extends Controller
{
    public function vapidKey(): JsonResponse
    {
        return response()->json([
            'public_key' => config('services.vapid.public_key'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        Log::info('[Push] Store request received', [
            'user_id' => $request->user()?->id,
            'has_endpoint' => $request->has('endpoint'),
            'has_keys' => $request->has('keys'),
        ]);

        $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $user = $request->user();

        PushSubscriptionModel::updateOrCreate(
            ['endpoint' => $request->input('endpoint')],
            [
                'id' => Str::uuid()->toString(),
                'user_id' => $user->id,
                'p256dh' => $request->input('keys.p256dh'),
                'auth' => $request->input('keys.auth'),
                'user_agent' => $request->userAgent(),
                'last_used_at' => now(),
            ]
        );

        Log::info('[Push] Subscription saved', ['user_id' => $user->id]);

        return response()->json(['message' => 'Suscripción guardada correctamente']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        PushSubscriptionModel::where('user_id', $request->user()->id)
            ->where('endpoint', $request->input('endpoint'))
            ->delete();

        return response()->json(['message' => 'Suscripción eliminada correctamente']);
    }
}
