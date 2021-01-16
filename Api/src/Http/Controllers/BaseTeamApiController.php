<?php

namespace Nitm\Api\Http\Controllers;

use Nitm\Api\Http\Controllers\Traits\ApiControllerTrait;

/**
 * Base Api controller
 *
 * @author Malcolm Paul <malcolm@ninjasitm.com>
 */
abstract class BaseTeamApiController extends BaseController
{
    use ApiControllerTrait;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware(\Nitm\Api\Http\Middleware\Timezone::class);
        $this->middleware(\Nitm\Api\Http\Middleware\UpdatesUserActivity::class);
    }
}