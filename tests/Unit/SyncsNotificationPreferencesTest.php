<?php

use Nitm\Content\NitmContent;
use Nitm\Content\Traits\SyncsNotificationPreferences;
use Nitm\Testing\PackageTestCase as TestCase;

class SyncsNotificationPreferencesTest extends TestCase {
    protected string $originalUserModel;

    protected string $originalTeamModel;

    protected function setUp(): void {
        parent::setUp();

        $this->originalUserModel = NitmContent::$userModel;
        $this->originalTeamModel = NitmContent::$teamModel;

        NitmContent::useUserModel(SyncPreferenceUserSubject::class);
        NitmContent::useTeamModel(SyncPreferenceTeamSubject::class);
    }

    protected function tearDown(): void {
        NitmContent::useUserModel($this->originalUserModel);
        NitmContent::useTeamModel($this->originalTeamModel);

        parent::tearDown();
    }

    public function testSyncNotificationPreferencesAssignsUserIdsForUserModels(): void {
        $subject     = new SyncPreferenceUserSubject(15);
        $preferences = [
            ['type_id' => 2],
        ];

        $subject->syncNotificationPreferences($preferences);

        $this->assertSame([
            ['type_id' => 2, 'user_id' => 15, 'team_id' => null],
        ], $subject->syncedData->values()->all());
    }

    public function testSyncNotificationPreferencesAssignsTeamIdsForTeamModels(): void {
        $subject     = new SyncPreferenceTeamSubject(30);
        $preferences = [
            ['type_id' => 7],
        ];

        $subject->syncNotificationPreferences($preferences);

        $this->assertSame([
            ['type_id' => 7, 'user_id' => null, 'team_id' => 30],
        ], $subject->syncedData->values()->all());
    }
}

class SyncPreferenceUserSubject {
    use SyncsNotificationPreferences;

    public $syncedData;

    public function __construct(public int $id) {
    }

    public function initNotificationPreferences() {
        return collect([]);
    }

    public function syncRelation($data, string $relation,  ? callable $callable = null, $linkedBy = ['id']) {
        $this->syncedData = $data;

        return $data;
    }
}

class SyncPreferenceTeamSubject {
    use SyncsNotificationPreferences;

    public $syncedData;

    public function __construct(public int $id) {
    }

    public function initNotificationPreferences() {
        return collect([]);
    }

    public function syncRelation($data, string $relation,  ? callable $callable = null, $linkedBy = ['id']) {
        $this->syncedData = $data;

        return $data;
    }
}