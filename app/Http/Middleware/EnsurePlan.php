<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlan
{
    public function handle(Request $request, Closure $next, string $required)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $tier = app('plan.resolver')->getTier($user);

        $map = [
            'pro' => ['pro'],
            'casual_or_above' => ['pro','casual'],
            'free' => ['pro','casual','free'],
        ];

        $allowed = $map[$required] ?? [];
        if (!in_array($tier, $allowed, true)) {
            return response()->json(['message' => 'Fonctionnalité non disponible pour votre abonnement'], 403);
        }

        return $next($request);
    }
} 