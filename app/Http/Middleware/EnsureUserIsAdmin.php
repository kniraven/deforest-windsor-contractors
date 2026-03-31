<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        $allowedEmails = config('directory.admin_emails', []);

        if (!in_array(strtolower((string) $user->email), $allowedEmails, true)) {
            abort(403);
        }

        return $next($request);
    }
}