<?php

use PHPUnit\Framework\TestCase;

class OptionalSocialAuthDependenciesTest extends TestCase {
    public function testOptionalSocialAuthSymbolsAreResolvableInDevelopment(): void {
        $this->assertTrue(class_exists(MadWeb\SocialAuth\Controllers\SocialAuthController::class));
        $this->assertTrue(class_exists(MadWeb\SocialAuth\Models\SocialProvider::class));
        $this->assertTrue(class_exists(MadWeb\SocialAuth\SocialProviderManager::class));
        $this->assertTrue(interface_exists(MadWeb\SocialAuth\Contracts\SocialAuthenticatable::class));
        $this->assertTrue(interface_exists(Laravel\Socialite\Contracts\Factory::class));
        $this->assertTrue(interface_exists(Laravel\Socialite\Contracts\User::class));
        $this->assertTrue(class_exists(Laravel\Socialite\Two\AbstractProvider::class));
        $this->assertTrue(class_exists(\Google_Client::class));
    }
}
