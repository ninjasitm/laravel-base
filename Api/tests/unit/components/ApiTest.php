<?php

namespace Nitm\Api\Tests\Components;

use Nitm\Api\Tests\PluginTestCase;
use Nitm\Api\Components\Api;

class ApiTest extends PluginTestCase
{
    protected $api;
    protected $controllerClass = '\Nitm\Api\Controllers\ApiController';

    public function setUp()
    {
        parent::setup();
        $this->api = new Api($this->spoofPageCode());
    }

    public function testApiObject()
    {
        $this->assertInstanceOf(\Nitm\Api\Components\Api::class, $this->api, "Didn't receive a proper Api component");
    }

    public function testPageSpoofing()
    {
        $this->setupDummyRoutes();
        $this->assertInstanceOf(\Cms\Classes\CodeBase::class, $this->api->spoofPageCode(), "Didn't receive a proper spoofed page");
    }
}
