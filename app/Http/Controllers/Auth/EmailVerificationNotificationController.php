<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth()->user() ?: $request->user();
        $cacheKey = 'email-verification-resend:'.$user->getKey();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
            ]);
        }

        if (Cache::has($cacheKey)) {
            return response()->json([
                'message' => 'Lūdzu, uzgaidiet pirms atkārtotas nosūtīšanas.',
            ], 429);
        }

        $user->sendEmailVerificationNotification();
        Cache::put($cacheKey, true, now()->addSeconds(60));

        return response()->json([
            'message' => 'Verification email sent.',
            'status' => 'verification-link-sent',
        ]);
    }
}
