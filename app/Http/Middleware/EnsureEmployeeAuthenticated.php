<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployeeAuthenticated
{
    /**
     * Redirect to login when the employee session cookie is missing.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionCookie = $request->cookie('employee_session');

        if (empty($sessionCookie)) {
            return redirect('/login');
        }

        return $next($request);
    }
}
