<?php

use PHPUnit\Framework\TestCase;

class RemainingDiagnosticsSourceTest extends TestCase {
    public function testOptionalIntegrationSymbolsAreResolvableInDevelopment(): void {
        $this->assertTrue(class_exists(Laravel\Passport\Passport::class));
        $this->assertTrue(class_exists(Laravel\Cashier\Cashier::class));
        $this->assertTrue(trait_exists(DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts::class));
        $this->assertTrue(class_exists(Recurr\Rule::class));
        $this->assertTrue(class_exists(Recurr\Transformer\ArrayTransformer::class));
        $this->assertTrue(class_exists(NotificationChannels\Fcm\FcmMessage::class));
        $this->assertTrue(class_exists(NotificationChannels\Fcm\Resources\AndroidConfig::class));
        $this->assertTrue(class_exists(NotificationChannels\Fcm\Resources\AndroidFcmOptions::class));
        $this->assertTrue(class_exists(NotificationChannels\Fcm\Resources\AndroidMessagePriority::class));
        $this->assertTrue(class_exists(NotificationChannels\Fcm\Resources\ApnsConfig::class));
        $this->assertTrue(class_exists(NotificationChannels\Fcm\Resources\ApnsFcmOptions::class));
        $this->assertTrue(class_exists(NotificationChannels\Fcm\Exceptions\CouldNotSendNotification::class));
        $this->assertTrue(class_exists(Nitm\Content\Models\CalendarEntry::class));
        $this->assertTrue(class_exists(Nitm\Content\Models\ChatMessage::class));
        $this->assertTrue(class_exists(Nitm\Content\Models\NotificationPreference::class));
        $this->assertTrue(class_exists(Nitm\Content\Models\NotificationType::class));
        $this->assertTrue(class_exists(Nitm\Content\Models\Announcement::class));
    }

    public function testRemainingSourceFilesUseModernPatterns(): void {
        $stubContents          = $this->fileContents('Api/stubs/tests/TestCase.stub.php');
        $appStubContents       = $this->fileContents('Api/stubs/tests/CreatesApplication.stub.php');
        $optionsContents       = $this->fileContents('Content/src/Configuration/ManagesApiOptions.php');
        $repositoryContents    = $this->fileContents('Content/src/Repositories/FileRepository.php');
        $syncRelationsContents = $this->fileContents('Content/src/Traits/SyncsRelations.php');
        $newCommentContents    = $this->fileContents('Content/src/Events/NewComment.php');
        $newReactionContents   = $this->fileContents('Content/src/Events/NewReaction.php');
        $repositoryPublishStub = $this->fileContents('Api/publishes/resources/infyom/infyom-generator-templates/test/repository_test.stub');
        $apiPublishStub        = $this->fileContents('Api/publishes/resources/infyom/infyom-generator-templates/api/test/api_test.stub');
        $factoryPublishStub    = $this->fileContents('Api/publishes/resources/infyom/infyom-generator-templates/factories/model_factory.stub');
        $modelPublishStub      = $this->fileContents('Api/publishes/resources/infyom/infyom-generator-templates/model/model.stub');

        $this->assertStringNotContainsString('ArraySubsetAsserts', $stubContents);
        $this->assertDoesNotMatchRegularExpression('/(?<!::)factory\(/', $stubContents);
        $this->assertStringContainsString('::factory()', $stubContents);
        $this->assertStringContainsString('::factory()->count($count)', $stubContents);
        $this->assertStringNotContainsString('factory($teamClass)', $appStubContents);
        $this->assertStringContainsString('$teamClass::factory()->create()', $appStubContents);
        $this->assertStringNotContainsString('use Laravel\\Passport\\Passport;', $optionsContents);
        $this->assertStringContainsString("class_exists(", $optionsContents);
        $this->assertStringNotContainsString('use Storage;', $repositoryContents);
        $this->assertStringNotContainsString('\\App::', $repositoryContents);
        $this->assertStringNotContainsString('\\Log::', $repositoryContents);
        $this->assertStringContainsString('use Illuminate\\Support\\Facades\\Storage;', $repositoryContents);
        $this->assertStringContainsString('use Illuminate\\Support\\Facades\\App;', $repositoryContents);
        $this->assertStringContainsString('use Illuminate\\Support\\Facades\\Log;', $repositoryContents);
        $this->assertStringContainsString('FilesystemAdapter', $repositoryContents);
        $this->assertStringContainsString('instanceof \\Illuminate\\Support\\Collection', $repositoryContents);
        $this->assertStringNotContainsString('dump(', $syncRelationsContents);
        $this->assertStringNotContainsString('use Nitm\\Content\\Models\\Comment;', $newCommentContents);
        $this->assertStringNotContainsString('Cog\\Contracts\\Love\\Reaction\\Models\\Reaction', $newReactionContents);
        $this->assertStringContainsString('public function __construct(Model $comment)', $newCommentContents);
        $this->assertStringContainsString('enabledFor(static::class)', $newCommentContents);
        $this->assertStringContainsString('public function __construct(Model $reaction)', $newReactionContents);
        $this->assertStringContainsString('enabledFor(static::class)', $newReactionContents);
        $this->assertStringContainsString('public function getReaction(): Model', $newReactionContents);
        $this->assertDoesNotMatchRegularExpression('/(?<!::)factory\(/', $repositoryPublishStub);
        $this->assertDoesNotMatchRegularExpression('/(?<!::)factory\(/', $apiPublishStub);
        $this->assertStringContainsString('::factory()->make()', $repositoryPublishStub);
        $this->assertStringContainsString('::factory()->create()', $repositoryPublishStub);
        $this->assertStringContainsString('::factory()->make()', $apiPublishStub);
        $this->assertStringContainsString('::factory()->create()', $apiPublishStub);
        $this->assertStringNotContainsString('Eloquent\\Factory $factory', $factoryPublishStub);
        $this->assertStringContainsString('namespace Database\\Factories;', $factoryPublishStub);
        $this->assertStringContainsString('class $MODEL_NAME$Factory extends Factory', $factoryPublishStub);
        $this->assertStringContainsString('protected $model = $MODEL_NAME$::class;', $factoryPublishStub);
        $this->assertStringContainsString('public function definition(): array', $factoryPublishStub);
        $this->assertStringContainsString('use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;', $modelPublishStub);
        $this->assertStringContainsString('use HasFactory;', $modelPublishStub);
    }

    private function fileContents(string $relativePath): string {
        $path     = dirname(__DIR__, 2) . '/' . $relativePath;
        $contents = file_get_contents($path);

        $this->assertNotFalse($contents, 'Failed to read ' . $relativePath);

        return $contents;
    }
}
