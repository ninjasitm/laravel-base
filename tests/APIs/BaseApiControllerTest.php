<?php

use Nitm\Api\Http\Controllers\BaseApiController;
use Nitm\Api\Http\Controllers\Traits\ApiControllerTrait;
use Nitm\Api\Http\Middleware\Timezone;
use Nitm\Api\Http\Middleware\UpdatesUserActivity;
use Tests\TestCase;

class BaseApiControllerTest extends TestCase
{
    public function testConstructor()
    {
        $controller = $this->getMockForAbstractClass(BaseApiController::class);

        $middlewares = collect($controller->getMiddleware())->pluck('middleware');

        $this->assertContains('auth:api', $middlewares);
        $this->assertContains(Timezone::class, $middlewares);
        $this->assertContains(UpdatesUserActivity::class, $middlewares);
    }
}