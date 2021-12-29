<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;
use App\Exceptions\UnauthorisedException;

class Authenticate
{
    /**
     * Create a new middleware instance.
     *
     * @param \Illuminate\Contracts\Auth\Factory $auth The auth fatory instance
     */
    public function __construct(protected Auth $auth)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request The incoming request
     * @param \Closure $next The next handler in the list
     * @param string|null $guard The name of the auth guard in use
     *
     * @return mixed
     */
    public function handle($request, \Closure $next, $guard = null)
    {
        if ($this->auth->guard($guard)->guest()) {
            throw new UnauthorisedException("You are not permitted to use this resource");
        }

        return $next($request);
    }
}
