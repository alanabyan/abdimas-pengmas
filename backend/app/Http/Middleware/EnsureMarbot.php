<?php

namespace App\Http\Middleware;

use App\Models\Marbot;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMarbot
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() instanceof Marbot) {
            return response()->json(['message' => 'Akses hanya untuk marbot.'], 403);
        }

        return $next($request);
    }
}