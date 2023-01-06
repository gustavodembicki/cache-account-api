<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class Auth
{
    /**
     * Handle an incoming request.
     * 
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws AuthException
     */
    public function handle(Request $request, Closure $next)
    {
        $auth = $request->header()['auth_key'] ?? null;

        if (is_null($auth)) {
            throw new AuthenticationException("Unauthorized request");
        }

        return $next[$request];
    }
}