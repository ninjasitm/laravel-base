<?php

/**
 * Custom traits for APII controllers
 */

namespace Nitm\Api\Http\Controllers\Traits;

use Illuminate\Support\Response;
use InfyOm\Generator\Utils\ResponseUtil;

trait ApiControllerTrait
{
    public function sendResponse($result, $message)
    {
        return Response::json(ResponseUtil::makeResponse($message, $result));
    }

    public function sendError($error, $code = 404)
    {
        return Response::json(ResponseUtil::makeError($error), $code);
    }
}