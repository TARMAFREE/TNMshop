<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = env('ADMIN_KEY');
        $provided = $request->header('X-Admin-Key');

        if (!$expected || $provided !== $expected) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
