<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && method_exists($user, 'isBlocked') && $user->isBlocked()) {
            return response()->json([
                'message' => 'Account blocked',
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
