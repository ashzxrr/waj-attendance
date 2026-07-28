<?php

namespace App\Http\Middleware;

use App\Models\EmployeeFaceProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFaceRegistered
{
    /**
     * Redirect to face registration if the authenticated employee
     * hasn't registered their face yet.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\EmployeeAuth $user */
        $user = $request->user();

        if ($user) {
            $hasFaceProfile = EmployeeFaceProfile::where('pin', $user->pin)->exists();

            if (! $hasFaceProfile) {
                return redirect('/face-registration');
            }
        }

        return $next($request);
    }
}