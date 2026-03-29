<?php

use Nitm\Content\Listeners\BaseNotifyOfActivity;
use Nitm\Testing\PackageTestCase as TestCase;

class BaseNotifyOfActivityTest extends TestCase {
    public function testPrepareMessageJoinsSubMessages(): void {
        $message = BaseNotifyOfActivity::prepareMessage(
            'Summary: :subMessages',
            [
                'subMessages' => [
                    'collection' => collect([
                        ['name' => 'Alpha'],
                        ['name' => 'Beta'],
                    ]),
                    'message'    => ':name',
                    'params'     => ['name' => 'name'],
                ],
            ]
        );

        $this->assertSame("Summary: Alpha\nBeta", $message);
    }
}
