<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MfaGate
{
    const NEEDS_MFA_KEY = 'auth.needs-mfa';

    /**
     * Routes to not redirect to the MFA gate on.
     *
     * @var array<string>
     */
    private array $exclude = [
        'front.login.mfa',
        'front.login.mfa-recover',
    ];

    private ResponseFactory $responseFactory;

    /**
     * MfaGate constructor.
     */
    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->accountDoesntNeedMfa($request)) {
            return $next($request);
        }

        return $this->responseFactory->redirectGuest(route('front.login.mfa'));
    }

    /**
     * Check if the given request does not require MFA first.
     */
    private function accountDoesntNeedMfa(Request $request): bool
    {
        return $request->routeIs($this->exclude) ||
            $request->user() === null ||
            ! $request->user()->is_totp_enabled ||
            ! Session::has(self::NEEDS_MFA_KEY);
    }
}
