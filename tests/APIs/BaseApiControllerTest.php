<?php

use Nitm\Api\Http\Controllers\BaseApiController;
use Nitm\Api\Http\Middleware\Timezone;
use Nitm\Api\Http\Middleware\UpdatesUserActivity;
use Tests\TestCase;

class BaseApiControllerTest extends TestCase {
    public function testConstructor() {
        $controller = new class extends BaseApiController {};

        $middlewares = collect($controller->getMiddleware())->pluck('middleware');

        $this->assertContains('auth:api', $middlewares);
        $this->assertContains(Timezone::class, $middlewares);
        $this->assertContains(UpdatesUserActivity::class, $middlewares);
    }
}