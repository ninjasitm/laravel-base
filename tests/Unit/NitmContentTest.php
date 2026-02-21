<?php

use Nitm\Content\NitmContent;
use Nitm\Testing\PackageTestCase as TestCase;
class NitmContentTest extends TestCase
{
    public function testVersion()
    {
        $expectedVersion = '1.0.0';
        $this->assertEquals($expectedVersion, NitmContent::$version);
    }
}