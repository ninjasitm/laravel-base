<?php

use Nitm\Api\Http\Controllers\Auth\SocialAuthController;
use Nitm\Content\Traits\HasSocialAccounts;
use PHPUnit\Framework\TestCase;

class SocialAuthSourceTest extends TestCase {
    public function testTeamSocialAuthControllerUsesExpectedSymbols(): void {
        $contents = $this->fileContents('Api/src/Http/Controllers/Auth/TeamSocialAuthController.php');

        $this->assertStringNotContainsString('SocialProvdier', $contents);
        $this->assertStringNotContainsString('Nitm\\Api\\Http\\Controllers\\Traits\\CustomControllerTrait', $contents);
        $this->assertStringNotContainsString('use Nitm\\Content\\Models\\SocialProvider;', $contents);
        $this->assertStringContainsString('MadWeb\\SocialAuth\\Models\\SocialProvider', $contents);
        $this->assertStringContainsString('Nitm\\Content\\Http\\Controllers\\Traits\\CustomControllerTrait', $contents);
    }

    public function testSocialAuthSourcesAvoidKnownBadReferences(): void {
        $controllerContents = $this->fileContents('Api/src/Http/Controllers/Auth/SocialAuthController.php');
        $traitContents      = $this->fileContents('Content/src/Traits/HasSocialAccounts.php');

        $this->assertStringNotContainsString('SocialProvdier', $controllerContents);
        $this->assertStringNotContainsString('Nitm\\Content\\Models\\SocialProvider::class', $traitContents);
        $this->assertStringContainsString('MadWeb\\SocialAuth\\Models\\SocialProvider', $traitContents);
    }

    public function testSocialAuthHelperDoesNotEnforceNativeReturnType(): void {
        $method = new ReflectionMethod(SocialAuthController::class, 'socialAuthUser');

        $this->assertNull($method->getReturnType());
    }

    public function testNamespacedSocialAccountsTraitAutoloads(): void {
        $this->assertTrue(trait_exists(HasSocialAccounts::class));
    }

    private function fileContents(string $relativePath): string {
        $path = dirname(__DIR__, 2) . '/' . $relativePath;

        $contents = file_get_contents($path);

        $this->assertNotFalse($contents, 'Failed to read ' . $relativePath);

        return $contents;
    }
}
