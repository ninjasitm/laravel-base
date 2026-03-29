<?php

use Nitm\Content\Traits\CanJoinTeams;
use PHPUnit\Framework\TestCase;

class CanJoinTeamsTest extends TestCase {
    public function testRoleOnAllowsMissingTeam(): void {
        $subject = new class {
            use CanJoinTeams;
        };

        $method    = new ReflectionMethod($subject, 'roleOn');
        $parameter = $method->getParameters()[0];

        $this->assertTrue($parameter->allowsNull());
        $this->assertTrue($parameter->isOptional());
    }
}