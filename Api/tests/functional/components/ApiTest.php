<?php

namespace Nitm\Api\Tests\Components;

use Nitm\Api\Tests\PluginTestCase;

class ApiTest extends PluginTestCase
{
    public function testReadOne()
    {
        foreach ($this->getEndpoints() as $endpoint) {
            $this->json('GET', $endpoint->relatedtable.'/1')
            ->seeJsonEquals([
                'errors' => [
                   [
                    'method' => 'POST',
                    'code' => 400,
                    'reason' => 'Bad Request',
                    'detail' => 'A user was not found with the given credentials.',
                ],
             ],
         ]);
        }
    }

    public function getEndpoints()
    {
        return \Nitm\Api\Models\Mapping::all();
    }
}
