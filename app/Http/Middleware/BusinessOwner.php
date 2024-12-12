<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BusinessOwner
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->ownedBusinesses()->exists()) {
            return redirect()->route('businesses.register')
                ->with('error', 'You need to register a business first.');
        }

        return $next($request);
    }
} 