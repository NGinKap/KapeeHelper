<?php

namespace PhpJwtHelper\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use PhpJwtHelper\{JwtHelper,Validator};
use Closure;

class JwtAuthenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        if (!is_null($failed = Validator::doValidate(['access_token' => 'string|required'], ['access_token' => $access_token = $request->bearerToken()]))) return $failed;

        try {
            $jwt = JwtHelper::decode($access_token);
        } catch(\Exception $exception) {
            if ($exception instanceof Firebase\JWT) {
                return Validator::failedResponse($exception->getMessage(), 401);
            } else {
                return Validator::failedResponse('Failed to process this access. Don\'t use it, please make a new one instead', 401);
            }
        }

        $request->request->add(['jwt' => $jwt]);

        return $next($request);
    }
}
