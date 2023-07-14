<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     description="This is a voting-api development server.  You can find out more about Swagger at [http://swagger.io](http://swagger.io) or on [irc.freenode.net, #swagger](http://swagger.io/irc/).",
 *     version="1.0.0",
 *     title="voting-api",
 *     termsOfService="http://swagger.io/terms/",
 *     @OA\Contact(
 *         email="peter.hrobar@gmail.com"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 */

class Controller extends BaseController
{
    protected static function eWrap($message): array
    {
        return self::wrap($message, true);
    }

    protected static function sWrap($message): array
    {
        return self::wrap($message, false);
    }

    protected static function wrap($message, bool $error = false): array
    {
        return match($error) {
            true => ['status' => 'error', 'message' => $message],
            false => ['status' => 'success', 'message' => $message],
            default => ['status' => 'success', 'message' => $message],
        };
    }
}
