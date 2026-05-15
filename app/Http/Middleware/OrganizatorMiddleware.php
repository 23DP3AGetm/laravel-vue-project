<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrganizatorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isOrganizator()) {
            return response()->json([
                'message' => 'Forbidden.',
            ], JsonResponse::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
