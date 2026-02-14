<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, \Closure $next, ...$guards)
    {
        // First, run Laravel's normal authentication check
        $this->authenticate($request, $guards);

        // Now check user status
        if (Auth::check() && Auth::user()->status !== 'active') {

            // Logout blocked user immediately
            Auth::logout();

            return redirect()->route('login')
                ->with('error', 'Your account is blocked. Contact admin.');
        }

        return $next($request);
    }

    /**
     * Redirect if not authenticated
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
