<?php

namespace Nitm\Api\Tests\Components;

use Nitm\Api\Tests\PluginTestCase;
use Nitm\Content\Models\Follow;

class FollowTest extends PluginTestCase
{
    /**
     * @expectedException  \October\Rain\Database\ModelException
     * @expectedExceptionMessage No self follows!
     */
    public function testNoSelfFollow()
    {
        $model = new Follow();
        $model->fill([
         'follower' => 1,
         'followee' => 1,
      ]);
        $model->validate();
    }

     /**
      * @expectedException  \October\Rain\Database\ModelException
      * @expectedExceptionMessage Already following!
      */
     public function testFollowExists()
     {
         $model = factory(Follow::class)->create([
             'follower' => 10,
             'followee' => 1,
          ]);

         $testModel = new Follow();
         $testModel->fill([
                 'follower' => 10,
                 'followee' => 1,
              ]);
         $testModel->save();
     }
}
