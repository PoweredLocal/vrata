<?php

namespace App\Http\Middleware;

use App\Http\Request;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class AddCORSHeader
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /**
         * @var Response $response
         */
        $response = $next($request);
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }
}
