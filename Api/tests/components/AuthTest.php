<?php

namespace Nitm\Api\Tests\Components;

use Nitm\Api\Tests\PluginTestCase;

class AuthTest extends PluginTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->component = Cms\Classes\ComponentManager::makeComponent('apiAuthAPI', $this->spoofPageCode());
    }

    public function testBadLogin()
    {
        $this->call('POST', 'auth', [
            'username' => 'test',
            'password' => 'test',
         ]);
        $this->assertResponseOk(1, $post->id);
    }
}
