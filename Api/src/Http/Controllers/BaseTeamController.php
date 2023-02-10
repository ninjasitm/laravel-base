<?php

namespace Nitm\Api\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Nitm\Content\Repositories\BaseRepository;
use Illuminate\Container\Container as Application;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as LaravelController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Nitm\Content\Http\Controllers\Traits\CustomControllerTrait;
use Nitm\Content\Http\Controllers\Traits\SupportsTeamRepositories;

/**
 * @SWG\Swagger(
 *   basePath="/api/v1",
 * @SWG\Info(
 *     title="WETHRIVE API DOCUMENTATION",
 *     version="1.0.0"
 *   )
 * )
 */

/**
 * @SWG\SecurityScheme(
 *   securityDefinition="Bearer",
 *   type="apiKey",
 *   in="header",
 *   name="Authorization"
 * )
 * This class should be parent class for other controllers
 * Class BaseTeamController
 */
class BaseTeamController extends LaravelController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;
    use CustomControllerTrait;
    use SupportsTeamRepositories;

    /**
     * Construct controller
     *
     * @param Application    $app
     * @param BaseRepository $repository
     */
    public function __construct($repository = null)
    {
        $this->createRepository($repository);
    }
}
