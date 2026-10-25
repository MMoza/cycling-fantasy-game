<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Persistence\Models\PushSubscriptionModel;
use App\Presentation\Http\Requests\Push\DeletePushSubscriptionRequest;
use App\Presentation\Http\Requests\Push\StorePushSubscriptionRequest;
use Illuminate\Http\JsonResponse;
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

    public function store(StorePushSubscriptionRequest $request): JsonResponse
    {
        Log::info('[Push] Store request received', [
            'user_id' => $request->user()?->id,
            'has_endpoint' => $request->has('endpoint'),
            'has_keys' => $request->has('keys'),
        ]);

        PushSubscriptionModel::updateOrCreate(
            ['endpoint' => $request->endpoint()],
            [
                'id' => Str::uuid()->toString(),
                'user_id' => $request->user()->id,
                'p256dh' => $request->p256dh(),
                'auth' => $request->auth(),
                'user_agent' => $request->userAgent(),
                'last_used_at' => now(),
            ]
        );

        Log::info('[Push] Subscription saved', ['user_id' => $request->user()->id]);

        return response()->json(['message' => 'Suscripción guardada correctamente']);
    }

    public function destroy(DeletePushSubscriptionRequest $request): JsonResponse
    {
        PushSubscriptionModel::where('user_id', $request->user()->id)
            ->where('endpoint', $request->endpoint())
            ->delete();

        return response()->json(['message' => 'Suscripción eliminada correctamente']);
    }
}
