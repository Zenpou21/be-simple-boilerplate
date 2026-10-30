<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddTokenFromCookie
{
    /**
     * The browser sends the sign-in token in the HttpOnly `auth_token` cookie (set by
     * AuthController::onLogin). Passport looks for it in the Authorization header, so we copy it there.
     *
     * A request that already has an Authorization header (e.g. from Postman) keeps it.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('auth_token');

        if ($token && !$request->headers->has('Authorization')) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}
