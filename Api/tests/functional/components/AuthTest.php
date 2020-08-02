<?php

namespace Nitm\Api\Tests\Components;

use Nitm\Api\Tests\PluginTestCase;
use Nitm\Api\Components\Auth;

class AuthTest extends PluginTestCase
{
    protected $controllerClass = '\Nitm\Api\Controllers\AuthController';

    public function setUp()
    {
        parent::setUp();
        $pluginManager = \System\Classes\PluginManager::instance();
        $pluginManager->registerPlugin($pluginManager->loadPlugin('Rainlab\User', plugins_path().'/rainlab/user'));
    }

    public function testNoLogin()
    {
        $this->route('POST', 'v1/auth/login');
        $this->shouldReturnJson();
        $this->seeJsonContains(['code' => 400]);
        $this->seeJsonContains(['message' => 'The login field is required.']);
    }

    public function testNoPasswordLogin()
    {
        $this->route('POST', 'v1/auth/login', [
            'login' => 'test@test.com',
        ]);
        $this->shouldReturnJson();
        $this->seeJsonContains(['code' => 400]);
        $this->seeJsonContains(['message' => 'The password field is required.']);
    }

    public function testNoLoginLogin()
    {
        $this->route('POST', 'v1/auth/login', [
              'password' => 'test',
        ]);
        $this->shouldReturnJson();
        $this->seeJsonContains(['code' => 400]);
        $this->seeJsonContains(['message' => 'The login field is required.']);
    }

    public function testBadLogin()
    {
        $response = $this->route('POST', 'v1/auth/login', [
             'login' => 'test@test.com',
             'password' => 'test',
         ]);
        $this->shouldReturnJson();
        $this->seeJsonContains(['code' => 400]);
        $this->seeJsonContains([
           'message' => 'The details you entered did not match our records. Please double-check and try again.',
        ]);
    }

    public function testGoodLogin()
    {
        $user = \Nitm\Content\Models\User::create([
            'email' => 'test@test.com',
            'password' => 'test',
            'password_confirmation' => 'test',
         ]);
        $this->assertEquals(1, $user->id);
        $user->attemptActivation($user->activation_code);
        $response = $this->route('POST', 'v1/auth/login', [
             'login' => 'test@test.com',
             'password' => 'test',
         ]);
        $this->shouldReturnJson();
        $this->assertNotEmpty($user->activated_at);
        $this->seeJsonContains(['id' => '1']);
    }
}
